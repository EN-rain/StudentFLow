@extends('layouts.app')
@section('title', $student->full_name . ' - StudentFlow')
@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-person-circle"></i> {{ $student->full_name }}</h2>
            <p class="text-muted mb-0">{{ $student->student_number }} · {{ $student->email }}</p>
        </div>
        <div>
            @if (auth()->user()->isAdmin())
                <a href="/students/{{ $student->id }}/edit" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i> Edit</a>
                <form method="POST" action="/students/{{ $student->id }}" class="d-inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger" onclick="return confirm('Delete this student?')"><i class="bi bi-trash"></i> Delete</button>
                </form>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card stat-card"><div class="card-body">
                <div class="stat-label">Gender</div>
                <div>{{ $student->gender ?? '-' }}</div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card"><div class="card-body">
                <div class="stat-label">Birth Date</div>
                <div>{{ $student->birth_date ? $student->birth_date->format('M d, Y') : '-' }}</div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card"><div class="card-body">
                <div class="stat-label">Contact</div>
                <div>{{ $student->contact_number ?? '-' }}</div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card"><div class="card-body">
                <div class="stat-label">Status</div>
                <span class="badge bg-{{ $student->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($student->status) }}</span>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card stat-card"><div class="card-body">
                <div class="stat-label">Address</div>
                <div>{{ $student->address ?? '-' }}</div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card"><div class="card-body">
                <div class="stat-label">Guardian</div>
                <div>{{ $student->guardian_name ?? '-' }}</div>
                <small class="text-muted">{{ $student->guardian_contact ?? '' }}</small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card"><div class="card-body">
                <div class="stat-label">Attendance</div>
                <div class="stat-value text-{{ $attendancePct !== null && $attendancePct >= 75 ? 'success' : 'warning' }}">
                    {{ $attendancePct !== null ? $attendancePct . '%' : '-' }}
                </div>
                <small class="text-muted">{{ $attendancePresent }} of {{ $attendanceTotal }} records</small>
            </div></div>
        </div>
    </div>

    <div class="card stat-card">
        <div class="card-header bg-white"><h5 class="mb-0">Enrolled Classes</h5></div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Class</th><th>Subject</th><th>Teacher</th><th>Schedule</th><th>Date Enrolled</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse ($student->classes as $c)
                        <tr>
                            <td><a href="/classes/{{ $c->id }}">{{ $c->class_name }}</a></td>
                            <td>{{ $c->subject }}</td>
                            <td>{{ $c->teacher->full_name ?? '-' }}</td>
                            <td><small>{{ $c->schedule ?? '-' }}</small></td>
                            <td>{{ $c->pivot->date_enrolled }}</td>
                            <td><span class="badge bg-{{ $c->pivot->status === 'enrolled' ? 'success' : 'secondary' }}">{{ ucfirst($c->pivot->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Not enrolled in any class.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
