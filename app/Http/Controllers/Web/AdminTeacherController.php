<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Models\Teacher;
use App\Models\User;
use App\Support\AccountAccess;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

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

        return redirect('/admin/teachers')->with('status', 'Teacher created. Share the setup link below.')->with('teacher_setup_url', $setupUrl);
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

        return redirect('/admin/teachers')->with('status', 'Teacher updated.');
    }

    public function invite(Request $request, Teacher $teacher)
    {
        $setupUrl = $this->teacherSetupUrl($teacher->user);
        ActivityLogger::log($request, 'teacher.invite_regenerated', $teacher, ['invite_issued' => true]);

        return back()->with('status', 'Teacher setup link regenerated.')->with('teacher_setup_url', $setupUrl);
    }

    public function setStatus(Request $request, Teacher $teacher)
    {
        $payload = $request->validate(['status' => 'required|in:active,disabled']);
        AccountAccess::setStatus($teacher->user, $payload['status']);
        ActivityLogger::log($request, 'teacher.status_changed', $teacher, ['status' => $payload['status']]);

        return back()->with('status', 'Teacher account status updated.');
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
