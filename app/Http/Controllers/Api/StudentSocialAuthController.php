<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\StudentSocialUserResolver;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class StudentSocialAuthController extends Controller
{
    public function google(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'id_token' => 'required|string',
        ]);

        $profile = $this->verifyGoogleToken($payload['id_token']);
        $user = StudentSocialUserResolver::resolve('google', $profile);

        return $this->tokenResponse($user, 'google');
    }

    public function github(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'code' => 'required_without:access_token|string',
            'access_token' => 'required_without:code|string',
            'redirect_uri' => 'nullable|url|max:255',
            'code_verifier' => 'nullable|string|min:43|max:128',
        ]);

        $token = $payload['access_token'] ?? $this->exchangeGithubCode(
            $payload['code'],
            $payload['redirect_uri'] ?? null,
            $payload['code_verifier'] ?? null,
        );
        $profile = $this->githubProfile($token);
        $user = StudentSocialUserResolver::resolve('github', $profile);

        return $this->tokenResponse($user, 'github');
    }

    public function mobileGithubStart(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'state' => 'required|string|min:32|max:255',
            'code_challenge' => ['required', 'string', 'regex:/^[A-Za-z0-9_-]{43}$/'],
        ]);

        $clientId = config('services.github.client_id');
        $clientSecret = config('services.github.client_secret');
        if (! $clientId || ! $clientSecret) {
            throw ValidationException::withMessages([
                'github' => ['GitHub OAuth is not configured on the server.'],
            ]);
        }

        $redirectUri = url('/api/auth/github/callback');
        Cache::put($this->mobileStateKey($payload['state']), [
            'code_challenge' => $payload['code_challenge'],
            'redirect_uri' => $redirectUri,
        ], now()->addMinutes(10));

        $authorizationUrl = 'https://github.com/login/oauth/authorize?'.http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => 'read:user user:email',
            'state' => $payload['state'],
            'code_challenge' => $payload['code_challenge'],
            'code_challenge_method' => 'S256',
        ]);

        return response()->json(['authorization_url' => $authorizationUrl]);
    }

    public function githubCallback(Request $request): JsonResponse|RedirectResponse
    {
        $state = $request->string('state')->toString();
        $mobileFlow = $state !== '' ? Cache::get($this->mobileStateKey($state)) : null;

        if (is_array($mobileFlow)) {
            $query = ['state' => $state];
            if ($request->filled('error')) {
                $query['error'] = $request->string('error')->toString();
                $query['error_description'] = $request->string('error_description')->toString();
            } else {
                $query['code'] = $request->validate(['code' => 'required|string'])['code'];
            }

            return redirect()->away(url('/mobile/oauth/github').'?'.http_build_query($query));
        }

        $payload = $request->validate([
            'code' => 'required|string',
        ]);

        $token = $this->exchangeGithubCode($payload['code']);
        $profile = $this->githubProfile($token);
        $user = StudentSocialUserResolver::resolve('github', $profile);

        return $this->tokenResponse($user, 'github');
    }

    public function mobileGithubComplete(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'code' => 'required|string',
            'state' => 'required|string|min:32|max:255',
            'code_verifier' => 'required|string|min:43|max:128',
        ]);

        $flow = Cache::pull($this->mobileStateKey($payload['state']));
        if (! is_array($flow)) {
            throw ValidationException::withMessages([
                'state' => ['The GitHub sign-in request is invalid or expired.'],
            ]);
        }

        $challenge = rtrim(strtr(base64_encode(hash('sha256', $payload['code_verifier'], true)), '+/', '-_'), '=');
        if (! hash_equals((string) ($flow['code_challenge'] ?? ''), $challenge)) {
            throw ValidationException::withMessages([
                'code_verifier' => ['The GitHub sign-in verifier is invalid.'],
            ]);
        }

        $token = $this->exchangeGithubCode(
            $payload['code'],
            (string) ($flow['redirect_uri'] ?? url('/api/auth/github/callback')),
            $payload['code_verifier'],
        );
        $profile = $this->githubProfile($token);
        $user = StudentSocialUserResolver::resolve('github', $profile);

        return $this->tokenResponse($user, 'github');
    }

    private function verifyGoogleToken(string $idToken): array
    {
        if (app()->environment('local', 'testing') && str_starts_with($idToken, 'test-google:')) {
            $email = substr($idToken, strlen('test-google:'));

            return ['sub' => 'test-google-'.md5($email), 'email' => $email, 'email_verified' => true, 'name' => strtok($email, '@')];
        }

        $clientId = config('services.google.client_id');
        if (! is_string($clientId) || $clientId === '') {
            throw ValidationException::withMessages([
                'id_token' => ['Google OAuth is not configured on the server.'],
            ]);
        }

        $response = Http::timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $idToken,
        ]);

        if (! $response->ok()) {
            throw ValidationException::withMessages(['id_token' => ['Google token verification failed.']]);
        }

        $data = $response->json();
        if (($data['aud'] ?? null) !== $clientId) {
            throw ValidationException::withMessages(['id_token' => ['Google token audience does not match this app.']]);
        }
        if (($data['email_verified'] ?? 'false') !== true && ($data['email_verified'] ?? 'false') !== 'true') {
            throw ValidationException::withMessages(['email' => ['Google email address is not verified.']]);
        }

        return [
            'sub' => $data['sub'] ?? null,
            'email' => $data['email'] ?? null,
            'email_verified' => $data['email_verified'] ?? false,
            'name' => $data['name'] ?? strtok((string) ($data['email'] ?? ''), '@'),
            'avatar_url' => $data['picture'] ?? null,
        ];
    }

    private function exchangeGithubCode(string $code, ?string $redirectUri = null, ?string $codeVerifier = null): string
    {
        $clientId = config('services.github.client_id');
        $clientSecret = config('services.github.client_secret');
        if (! $clientId || ! $clientSecret) {
            throw ValidationException::withMessages(['code' => ['GitHub OAuth is not configured on the server.']]);
        }

        $form = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
        ];

        if ($redirectUri) {
            $form['redirect_uri'] = $redirectUri;
        }
        if ($codeVerifier) {
            $form['code_verifier'] = $codeVerifier;
        }

        $response = Http::asForm()->acceptJson()->post('https://github.com/login/oauth/access_token', $form);

        if (! $response->ok() || ! $response->json('access_token')) {
            throw ValidationException::withMessages(['code' => ['GitHub code exchange failed.']]);
        }

        return $response->json('access_token');
    }

    private function githubProfile(string $token): array
    {
        if (app()->environment('local', 'testing') && str_starts_with($token, 'test-github:')) {
            $email = substr($token, strlen('test-github:'));

            return ['id' => 'test-github-'.md5($email), 'username' => strtok($email, '@'), 'email' => $email];
        }

        try {
            $userResponse = Http::withToken($token)->acceptJson()->get('https://api.github.com/user')->throw();
            $emailResponse = Http::withToken($token)->acceptJson()->get('https://api.github.com/user/emails')->throw();
        } catch (RequestException) {
            throw ValidationException::withMessages(['access_token' => ['GitHub profile lookup failed.']]);
        }

        $primary = collect($emailResponse->json())
            ->first(fn ($email) => ($email['primary'] ?? false) && ($email['verified'] ?? false));

        if (! $primary) {
            throw ValidationException::withMessages(['email' => ['GitHub account has no verified primary email.']]);
        }

        $user = $userResponse->json();

        return [
            'id' => (string) $user['id'],
            'username' => $user['login'] ?? null,
            'email' => $primary['email'],
            'avatar_url' => $user['avatar_url'] ?? null,
        ];
    }

    private function tokenResponse(User $user, string $provider): JsonResponse
    {
        if ($user->status !== 'active' || $user->student?->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['This account has been disabled. Contact an administrator.'],
            ]);
        }

        $user->tokens()->delete();
        $token = $user->createToken('android-'.$provider)->plainTextToken;

        return response()->json([
            'message' => 'Student social login successful.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'student' => [
                    'id' => $user->student->id,
                    'student_number' => $user->student->student_number,
                    'full_name' => $user->student->full_name,
                ],
                'github_username' => $user->github_username,
                'avatar_url' => $user->avatar_url,
            ],
        ]);
    }

    private function mobileStateKey(string $state): string
    {
        return 'mobile-github-state:'.hash('sha256', $state);
    }
}
