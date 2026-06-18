@extends('layouts.app')
@section('title', 'Teachers - StudentFlow')
@section('content')
    @if (session('teacher_setup_url'))
        <div class="alert alert-info">
            <div class="fw-semibold mb-1">Teacher setup link</div>
            <code>{{ session('teacher_setup_url') }}</code>
        </div>
    @endif
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0"><i class="bi bi-person-workspace"></i> Teachers</h2>
        <a href="/admin/teachers/create" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Teacher</a>
    </div>
    <form class="row g-2 mb-3">
        <div class="col-md-5"><input name="q" class="form-control" value="{{ request('q') }}" placeholder="Search teachers"></div>
        <div class="col-auto"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button></div>
    </form>
    <div class="card stat-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>Teacher</th><th>Account</th><th>Department</th><th>Classes</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($teachers as $teacher)
                        <tr>
                            <td><strong>{{ $teacher->full_name }}</strong><br><small>{{ $teacher->employee_number }}</small></td>
                            <td>
                                @if ($teacher->user->hasPendingTeacherSetup())
                                    <span class="badge text-bg-warning">Pending Setup</span>
                                @else
                                    {{ $teacher->user->username }}
                                @endif
                                <br><small>{{ $teacher->user->email }}</small>
                            </td>
                            <td>{{ $teacher->department }}</td>
                            <td>{{ $teacher->classes_count }}</td>
                            <td><span class="badge bg-{{ $teacher->user->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($teacher->user->status) }}</span></td>
                            <td class="text-end">
                                <a href="/admin/teachers/{{ $teacher->id }}/edit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="/admin/teachers/{{ $teacher->id }}/invite" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-link-45deg"></i></button>
                                </form>
                                <form method="POST" action="/admin/teachers/{{ $teacher->id }}/status" class="d-inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $teacher->user->status === 'active' ? 'disabled' : 'active' }}">
                                    <button class="btn btn-sm btn-outline-warning">{{ $teacher->user->status === 'active' ? 'Disable' : 'Reactivate' }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No teachers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
