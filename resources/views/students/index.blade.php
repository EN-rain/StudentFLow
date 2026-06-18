@extends('layouts.app')
@section('title', 'Students - StudentFlow')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-people"></i> Students</h2>
        @if (auth()->user()->isAdmin())
            <a href="/students/create" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Student</a>
        @endif
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card stat-card mb-3">
        <div class="card-body">
            <form method="GET" action="/students" class="row g-2">
                <div class="col-md-5">
                    <input type="text" name="q" class="form-control" placeholder="Search name, number, or email" value="{{ request('q') }}">
                </div>
                <div class="col-md-4">
                    <select name="class_id" class="form-select">
                        <option value="">All classes</option>
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}" {{ (string) request('class_id') === (string) $c->id ? 'selected' : '' }}>{{ $c->class_name }} - {{ $c->subject }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-primary"><i class="bi bi-search"></i> Filter</button>
                    <a href="/students" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card stat-card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Student Number</th>
                        <th>Name</th>
                        <th>Gender</th>
                        <th>Email</th>
                        <th>Classes</th>
                        <th>Verification</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $i => $s)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $s->student_number }}</td>
                            <td><a href="/students/{{ $s->id }}">{{ $s->full_name }}</a></td>
                            <td>{{ $s->gender ?? '-' }}</td>
                            <td><small>{{ $s->email }}</small></td>
                            <td>{{ $s->classes->count() }}</td>
                            <td>
                                @if ($s->user?->isClassroomVerified())
                                    <span class="badge bg-success">Verified</span>
                                    <small class="d-block text-muted">Google + GitHub</small>
                                @else
                                    <span class="badge bg-secondary">Not verified</span>
                                @endif
                            </td>
                            <td><span class="badge bg-{{ $s->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($s->status) }}</span></td>
                            <td>
                                <a href="/students/{{ $s->id }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                @if (auth()->user()->isAdmin())
                                    <a href="/students/{{ $s->id }}/edit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No students match the filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <p class="text-muted mt-2"><small>Showing {{ $students->count() }} students.</small></p>
@endsection
