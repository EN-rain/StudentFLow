<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Models\Teacher;
use App\Models\User;
use App\Support\ActivityLogger;
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
        $teachers = Teacher::with('user')->withCount('classes')->orderBy('last_name')->get();

        return response()->json(['data' => $teachers]);
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
            $teacher->user->update($userData);
            $teacher->update($request->safe()->except(['name', 'email', 'status']));
        });

        ActivityLogger::log($request, 'teacher.updated', $teacher);

        return response()->json(['data' => $teacher->load('user')]);
    }

    public function setStatus(Request $request, Teacher $teacher): JsonResponse
    {
        $payload = $request->validate(['status' => 'required|in:active,disabled']);
        $teacher->user->update(['status' => $payload['status']]);
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

        return url('/teacher/setup/'.$token.'?email='.urlencode($user->email));
    }
}
