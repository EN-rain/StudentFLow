@extends('layouts.app')
@section('title', $class->class_name . ' — StudentFlow')
@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-collection"></i> {{ $class->class_name }}</h2>
            <p class="text-muted mb-0">
                {{ $class->subject }} · {{ $class->grade_level }} · {{ $class->semester }} · {{ $class->school_year }}
            </p>
        </div>
        <div>
            <a href="/classes/{{ $class->id }}/edit" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i> Edit</a>
            <form method="POST" action="/classes/{{ $class->id }}" class="d-inline">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger" onclick="return confirm('Delete this class?')"><i class="bi bi-trash"></i> Delete</button>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card stat-card"><div class="card-body">
                <div class="stat-label">Teacher</div>
                <div>{{ $class->teacher->full_name ?? '—' }}</div>
                <small class="text-muted">{{ $class->teacher->department ?? '' }}</small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card"><div class="card-body">
                <div class="stat-label">Schedule</div>
                <div>{{ $class->schedule ?? '—' }}</div>
            </div></div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card"><div class="card-body">
                <div class="stat-label">Room</div>
                <div>{{ $class->room ?? '—' }}</div>
            </div></div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card"><div class="card-body">
                <div class="stat-label">Status</div>
                <span class="badge bg-{{ $class->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($class->status) }}</span>
            </div></div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card"><div class="card-body">
                <div class="stat-label">Enrolled</div>
                <div class="stat-value text-primary">{{ $class->students->count() }}</div>
            </div></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card stat-card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Enrolled Students</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Student Number</th><th>Name</th><th>Status</th><th></th></tr>
                        </thead>
                        <tbody>
                            @forelse ($class->students as $i => $s)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $s->student_number }}</td>
                                    <td><a href="/students/{{ $s->id }}">{{ $s->full_name }}</a></td>
                                    <td>
                                        <form method="POST" action="/classes/{{ $class->id }}/enrollments/{{ $s->id }}" class="d-flex gap-2">
                                            @csrf @method('PUT')
                                            <input type="date" name="date_enrolled" class="form-control form-control-sm" value="{{ $s->pivot->date_enrolled }}">
                                            <select name="status" class="form-select form-select-sm">
                                                @foreach (['enrolled', 'dropped', 'completed'] as $status)
                                                    <option value="{{ $status }}" @selected($s->pivot->status === $status)>{{ ucfirst($status) }}</option>
                                                @endforeach
                                            </select>
                                            <button class="btn btn-sm btn-outline-primary"><i class="bi bi-check-lg"></i></button>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" action="/classes/{{ $class->id }}/enrollments/{{ $s->id }}">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this student from the class?')"><i class="bi bi-x-lg"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No students enrolled.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card stat-card mb-3">
                <div class="card-header bg-white"><h5 class="mb-0">Add Enrollment</h5></div>
                <div class="card-body">
                    <form method="POST" action="/classes/{{ $class->id }}/enrollments">
                        @csrf
                        <div class="mb-2">
                            <select name="student_id" class="form-select" required>
                                <option value="">Select student</option>
                                @foreach ($availableStudents as $student)
                                    <option value="{{ $student->id }}">{{ $student->student_number }} - {{ $student->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <input type="date" name="date_enrolled" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <button class="btn btn-primary w-100"><i class="bi bi-person-plus"></i> Enroll</button>
                    </form>
                </div>
            </div>
            <div class="card stat-card mb-3">
                <div class="card-header bg-white"><h5 class="mb-0">Assignments</h5></div>
                <ul class="list-group list-group-flush">
                    @forelse ($class->assignments as $a)
                        <li class="list-group-item">
                            <strong>{{ $a->title }}</strong><br>
                            <small class="text-muted">Due {{ $a->deadline->format('M d, Y') }} · {{ $a->status }}</small>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No assignments.</li>
                    @endforelse
                </ul>
            </div>
            <div class="card stat-card">
                <div class="card-header bg-white"><h5 class="mb-0">Recent Announcements</h5></div>
                <ul class="list-group list-group-flush">
                    @forelse ($class->announcements as $a)
                        <li class="list-group-item">
                            <strong>{{ $a->title }}</strong>
                            <span class="badge bg-{{ $a->priority === 'Urgent' ? 'danger' : ($a->priority === 'Important' ? 'warning' : 'secondary') }} float-end">{{ $a->priority }}</span>
                            <br><small class="text-muted">{{ Str::limit($a->message, 80) }}</small>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No announcements.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
