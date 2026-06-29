<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Models\Teacher;
use App\Models\User;
use App\Support\AccountAccess;
use App\Support\ActivityLogger;
use App\Support\ApiPagination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AdminTeacherController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(ApiPagination::paginate(
            Teacher::with('user')->withCount('classes')->orderBy('last_name'),
            $request
        ));
    }

    public function store(StoreTeacherRequest $request): JsonResponse
    {
        [$teacher, $setupUrl] = DB::transaction(function () use ($request) {
            $user = User::create([
                'username' => $this->pendingTeacherUsername(),
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make(Str::random(48)),
                'role' => 'teacher',
                'status' => $request->status,
            ]);

            $teacher = Teacher::create($request->safe()->except(['name', 'email', 'status']) + [
                'user_id' => $user->id,
            ]);

            return [$teacher, $this->teacherSetupUrl($user)];
        });

        ActivityLogger::log($request, 'teacher.created', $teacher, ['invite_issued' => true]);

        return response()->json(['data' => $teacher->load('user'), 'setup_url' => $setupUrl], 201);
    }

    public function show(Teacher $teacher): JsonResponse
    {
        return response()->json(['data' => $teacher->load('user', 'classes')]);
    }

    public function update(StoreTeacherRequest $request, Teacher $teacher): JsonResponse
    {
        DB::transaction(function () use ($request, $teacher) {
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'status' => $request->status,
            ];
            $status = $userData['status'];
            unset($userData['status']);
            $teacher->user->update($userData);
            AccountAccess::setStatus($teacher->user, $status);
            $teacher->update($request->safe()->except(['name', 'email', 'status']));
        });

        ActivityLogger::log($request, 'teacher.updated', $teacher);

        return response()->json(['data' => $teacher->load('user')]);
    }

    public function setStatus(Request $request, Teacher $teacher): JsonResponse
    {
        $payload = $request->validate(['status' => 'required|in:active,disabled']);
        AccountAccess::setStatus($teacher->user, $payload['status']);
        ActivityLogger::log($request, 'teacher.status_changed', $teacher, ['status' => $payload['status']]);

        return response()->json(['data' => $teacher->load('user')]);
    }

    public function invite(Request $request, Teacher $teacher): JsonResponse
    {
        $setupUrl = $this->teacherSetupUrl($teacher->user);
        ActivityLogger::log($request, 'teacher.invite_regenerated', $teacher, ['invite_issued' => true]);

        return response()->json(['data' => $teacher->load('user'), 'setup_url' => $setupUrl]);
    }

    private function pendingTeacherUsername(): string
    {
        do {
            $candidate = User::TEACHER_INVITE_PREFIX.Str::lower(Str::random(12));
        } while (User::where('username', $candidate)->exists());

        return $candidate;
    }

    private function teacherSetupUrl(User $user): string
    {
        $token = Password::broker()->createToken($user);
        $frontend = trim((string) env('FRONTEND_URL', ''));
        if ($frontend !== '') {
            return rtrim($frontend, '/').'/teacher/setup/'.$token.'?email='.urlencode($user->email);
        }

        return url('/teacher/setup/'.$token.'?email='.urlencode($user->email));
    }
}
