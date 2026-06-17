<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\StudentSocialUserResolver;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        ]);

        $token = $payload['access_token'] ?? $this->exchangeGithubCode($payload['code']);
        $profile = $this->githubProfile($token);
        $user = StudentSocialUserResolver::resolve('github', $profile);

        return $this->tokenResponse($user, 'github');
    }

    public function githubCallback(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'code' => 'required|string',
        ]);

        $token = $this->exchangeGithubCode($payload['code']);
        $profile = $this->githubProfile($token);
        $user = StudentSocialUserResolver::resolve('github', $profile);

        return $this->tokenResponse($user, 'github');
    }

    private function verifyGoogleToken(string $idToken): array
    {
        if (app()->environment('local', 'testing') && str_starts_with($idToken, 'test-google:')) {
            $email = substr($idToken, strlen('test-google:'));
            return ['sub' => 'test-google-' . md5($email), 'email' => $email, 'email_verified' => true];
        }

        $response = Http::timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $idToken,
        ]);

        if (! $response->ok()) {
            throw ValidationException::withMessages(['id_token' => ['Google token verification failed.']]);
        }

        $data = $response->json();
        $clientId = config('services.google.client_id');
        if ($clientId && ($data['aud'] ?? null) !== $clientId) {
            throw ValidationException::withMessages(['id_token' => ['Google token audience does not match this app.']]);
        }
        if (($data['email_verified'] ?? 'false') !== true && ($data['email_verified'] ?? 'false') !== 'true') {
            throw ValidationException::withMessages(['email' => ['Google email address is not verified.']]);
        }

        return $data;
    }

    private function exchangeGithubCode(string $code): string
    {
        $clientId = config('services.github.client_id');
        $clientSecret = config('services.github.client_secret');
        if (! $clientId || ! $clientSecret) {
            throw ValidationException::withMessages(['code' => ['GitHub OAuth is not configured on the server.']]);
        }

        $response = Http::asForm()->acceptJson()->post('https://github.com/login/oauth/access_token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
        ]);

        if (! $response->ok() || ! $response->json('access_token')) {
            throw ValidationException::withMessages(['code' => ['GitHub code exchange failed.']]);
        }

        return $response->json('access_token');
    }

    private function githubProfile(string $token): array
    {
        if (app()->environment('local', 'testing') && str_starts_with($token, 'test-github:')) {
            $email = substr($token, strlen('test-github:'));
            return ['id' => 'test-github-' . md5($email), 'username' => strtok($email, '@'), 'email' => $email];
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

    private function tokenResponse($user, string $provider): JsonResponse
    {
        $user->tokens()->delete();
        $token = $user->createToken('android-' . $provider)->plainTextToken;

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
}
