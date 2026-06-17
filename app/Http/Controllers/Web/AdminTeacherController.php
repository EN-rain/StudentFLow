<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Models\Teacher;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminTeacherController extends Controller
{
    public function index(Request $request)
    {
        $q = Teacher::with('user')->withCount('classes');
        if ($search = $request->query('q')) {
            $q->where(function ($w) use ($search) {
                $like = "%{$search}%";
                $w->where('employee_number', 'like', $like)
                    ->orWhere('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhereHas('user', fn ($u) => $u->where('email', 'like', $like)->orWhere('username', 'like', $like));
            });
        }
        $teachers = $q->orderBy('last_name')->get();
        return view('admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        return view('admin.teachers.create');
    }

    public function store(StoreTeacherRequest $request)
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
        return redirect('/admin/teachers')->with('status', 'Teacher created.');
    }

    public function edit(Teacher $teacher)
    {
        $teacher->load('user');
        return view('admin.teachers.edit', compact('teacher'));
    }

    public function update(StoreTeacherRequest $request, Teacher $teacher)
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
        return redirect('/admin/teachers')->with('status', 'Teacher updated.');
    }

    public function setStatus(Request $request, Teacher $teacher)
    {
        $payload = $request->validate(['status' => 'required|in:active,disabled']);
        $teacher->user->update(['status' => $payload['status']]);
        ActivityLogger::log($request, 'teacher.status_changed', $teacher, ['status' => $payload['status']]);
        return back()->with('status', 'Teacher account status updated.');
    }
}
