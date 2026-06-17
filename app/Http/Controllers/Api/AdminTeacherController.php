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

class AdminTeacherController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $teachers = Teacher::with('user')->withCount('classes')->orderBy('last_name')->get();
        return response()->json(['data' => $teachers]);
    }

    public function store(StoreTeacherRequest $request): JsonResponse
    {
        $teacher = DB::transaction(function () use ($request) {
            $user = User::create([
                'username' => $request->username,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'teacher',
                'status' => $request->status,
            ]);

            return Teacher::create($request->safe()->except(['username', 'name', 'email', 'password', 'password_confirmation', 'status']) + [
                'user_id' => $user->id,
            ]);
        });

        ActivityLogger::log($request, 'teacher.created', $teacher);
        return response()->json(['data' => $teacher->load('user')], 201);
    }

    public function show(Teacher $teacher): JsonResponse
    {
        return response()->json(['data' => $teacher->load('user', 'classes')]);
    }

    public function update(StoreTeacherRequest $request, Teacher $teacher): JsonResponse
    {
        DB::transaction(function () use ($request, $teacher) {
            $userData = [
                'username' => $request->username,
                'name' => $request->name,
                'email' => $request->email,
                'status' => $request->status,
            ];
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            $teacher->user->update($userData);
            $teacher->update($request->safe()->except(['username', 'name', 'email', 'password', 'password_confirmation', 'status']));
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
}
