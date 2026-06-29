<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Support\StudentSocialUserResolver;
use App\Support\StudentUsername;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SessionAuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email|unique:students,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'prohibited',
        ]);

        [$firstName, $lastName] = $this->splitName($payload['name']);

        $student = null;
        $user = null;
        DB::transaction(function () use (&$student, &$user, $payload, $firstName, $lastName) {
            $student = Student::create([
                'student_number' => 'pending-'.Str::uuid(),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => strtolower($payload['email']),
                'status' => 'active',
            ]);
            $student->forceFill([
                'student_number' => sprintf('%s-%04d', now()->format('Y'), $student->id),
            ])->save();

            $user = User::create([
                'username' => StudentUsername::fromStudent($student),
                'name' => $student->full_name,
                'email' => $student->email,
                'password' => Hash::make($payload['password']),
                'role' => 'student',
                'status' => 'active',
                'student_id' => $student->id,
            ]);
        }, 3);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Student registered.',
            'user' => $this->userPayload($user),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $payload['username'])
            ->orWhere('email', $payload['username'])
            ->first();

        if (! $user && $this->starterLoginRepairEnabled()) {
            $user = $this->createMissingStarterStudent($payload['username'], $payload['password']);
        }

        if ($user && ! Hash::check($payload['password'], $user->password) && $this->matchesStarterPassword($user, $payload['password'])) {
            $user->forceFill(['password' => Hash::make($payload['password'])])->save();
        }

        if (! $user || ! Hash::check($payload['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Invalid credentials.'],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'username' => ['This account has been disabled. Contact an administrator.'],
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login successful.',
            'user' => $this->userPayload($user),
        ]);
    }

    public function google(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'id_token' => 'required|string',
        ]);

        $profile = $this->verifyGoogleToken($payload['id_token']);
        $user = StudentSocialUserResolver::resolve('google', $profile);
        $this->ensureActive($user);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Student social login successful.',
            'user' => $this->userPayload($user->fresh(['teacher', 'student'])),
        ]);
    }

    public function githubRedirect(Request $request): RedirectResponse
    {
        $clientId = config('services.github.client_id');
        $clientSecret = config('services.github.client_secret');
        abort_unless($clientId && $clientSecret, 503, 'GitHub OAuth is not configured on the server.');

        $state = Str::random(40);
        $request->session()->put('github_oauth_state', $state);

        $authorizationUrl = 'https://github.com/login/oauth/authorize?'.http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => url('/api/session/github/callback'),
            'scope' => 'read:user user:email',
            'state' => $state,
        ]);

        return redirect()->away($authorizationUrl);
    }

    public function githubCallback(Request $request): RedirectResponse
    {
        $loginUrl = $this->frontendUrl('/login');
        if ($request->filled('error')) {
            $message = $request->string('error_description')->toString() ?: 'GitHub sign-in failed.';

            return redirect()->away($loginUrl.'?error='.urlencode($message));
        }

        $payload = $request->validate([
            'code' => 'required|string',
            'state' => 'required|string',
        ]);

        $expectedState = $request->session()->pull('github_oauth_state');
        if (! is_string($expectedState) || ! hash_equals($expectedState, $payload['state'])) {
            return redirect()->away($loginUrl.'?error='.urlencode('GitHub sign-in state is invalid or expired.'));
        }

        try {
            $token = $this->exchangeGithubCode($payload['code'], url('/api/session/github/callback'));
            $profile = $this->githubProfile($token);
            $user = StudentSocialUserResolver::resolve('github', $profile);
            $this->ensureActive($user);

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->away($this->frontendUrl('/dashboard'));
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first() ?: 'GitHub sign-in failed.';

            return redirect()->away($loginUrl.'?error='.urlencode((string) $message));
        }
    }

    public function completeTeacherSetup(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'username' => ['required', 'string', 'max:64', Rule::unique('users', 'username')],
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset([
            'token' => $payload['token'],
            'email' => $payload['email'],
            'password' => $payload['password'],
            'password_confirmation' => $request->string('password_confirmation')->toString(),
        ], function (User $user, string $password) use ($payload) {
            if (! $user->isTeacher() || ! $user->hasPendingTeacherSetup()) {
                throw ValidationException::withMessages([
                    'email' => 'This teacher setup link is not valid for an established account.',
                ]);
            }

            $user->forceFill([
                'username' => $payload['username'],
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();
            $user->tokens()->delete();
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return response()->json(['message' => 'Teacher account setup complete. You can now sign in.']);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('teacher', 'student');

        return response()->json(['user' => $this->userPayload($user)]);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'classroom_verified' => $user->isClassroomVerified(),
            'google_linked' => filled($user->google_id),
            'github_linked' => filled($user->github_id),
            'github_username' => $user->github_username,
            'avatar_url' => $user->avatar_url,
            'teacher' => $user->teacher ? [
                'id' => $user->teacher->id,
                'employee_number' => $user->teacher->employee_number,
                'full_name' => $user->teacher->full_name,
                'department' => $user->teacher->department,
            ] : null,
            'student' => $user->student ? [
                'id' => $user->student->id,
                'student_number' => $user->student->student_number,
                'full_name' => $user->student->full_name,
            ] : null,
        ];
    }

    private function ensureActive(User $user): void
    {
        if ($user->status !== 'active' || $user->student?->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['This account has been disabled. Contact an administrator.'],
            ]);
        }
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2);

        return [$parts[0] ?: 'Student', $parts[1] ?? 'User'];
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

    private function exchangeGithubCode(string $code, ?string $redirectUri = null): string
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

    private function frontendUrl(string $path): string
    {
        $base = rtrim((string) env('FRONTEND_URL', 'http://localhost:3000'), '/');

        return $base.'/'.ltrim($path, '/');
    }

    private function matchesStarterPassword(User $user, string $password): bool
    {
        if (! $this->starterLoginRepairEnabled() || ! str_ends_with($user->email, '@studentflow.local')) {
            return false;
        }

        $expected = config('studentflow.starter_passwords.'.$user->role);

        return is_string($expected) && $expected !== '' && hash_equals($expected, $password);
    }

    private function createMissingStarterStudent(string $login, string $password): ?User
    {
        if (! $this->starterLoginRepairEnabled()) {
            return null;
        }

        $expectedPassword = config('studentflow.starter_passwords.student');
        if (! is_string($expectedPassword) || $expectedPassword === '' || ! hash_equals($expectedPassword, $password)) {
            return null;
        }

        $students = [
            'aaronvillanueva001' => ['2026-0001', 'Aaron', 'Miguel', 'Villanueva', 'aaron.villanueva@studentflow.local'],
            'biancaramos002' => ['2026-0002', 'Bianca', 'Marie', 'Ramos', 'bianca.ramos@studentflow.local'],
            'carlomendoza003' => ['2026-0003', 'Carlo', 'James', 'Mendoza', 'carlo.mendoza@studentflow.local'],
            'denisegarcia004' => ['2026-0004', 'Denise', 'Anne', 'Garcia', 'denise.garcia@studentflow.local'],
            'ethanflores005' => ['2026-0005', 'Ethan', 'Luis', 'Flores', 'ethan.flores@studentflow.local'],
            'faithnavarro006' => ['2026-0006', 'Faith', 'Rose', 'Navarro', 'faith.navarro@studentflow.local'],
            'gabrieltorres007' => ['2026-0007', 'Gabriel', 'John', 'Torres', 'gabriel.torres@studentflow.local'],
            'hannahlim008' => ['2026-0008', 'Hannah', 'Grace', 'Lim', 'hannah.lim@studentflow.local'],
            'ivancastillo009' => ['2026-0009', 'Ivan', 'James', 'Castillo', 'ivan.castillo@studentflow.local'],
            'jasmineaquino010' => ['2026-0010', 'Jasmine', 'Marie', 'Aquino', 'jasmine.aquino@studentflow.local'],
        ];

        $normalizedLogin = strtolower(trim($login));
        $seed = $students[$normalizedLogin] ?? collect($students)->first(fn ($student) => $student[4] === $normalizedLogin);
        if (! $seed) {
            return null;
        }

        [$studentNumber, $firstName, $middleName, $lastName, $email] = $seed;

        return DB::transaction(function () use ($normalizedLogin, $studentNumber, $firstName, $middleName, $lastName, $email, $password) {
            $student = Student::firstOrCreate(
                ['email' => $email],
                [
                    'student_number' => $studentNumber,
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'status' => 'active',
                ]
            );

            return User::firstOrCreate(
                ['username' => $normalizedLogin],
                [
                    'name' => $student->full_name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'role' => 'student',
                    'status' => 'active',
                    'student_id' => $student->id,
                ]
            );
        }, 3);
    }

    private function starterLoginRepairEnabled(): bool
    {
        return app()->environment('local', 'testing')
            && (bool) config('studentflow.seed_starter_data');
    }
}
