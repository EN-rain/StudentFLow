@extends('layouts.app')
@section('title', 'Classes - StudentFlow')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-collection"></i> Classes</h2>
        @if (auth()->user()->isAdmin() || auth()->user()->isTeacher())
            <a href="/classes/create" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> New Class
            </a>
        @endif
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card stat-card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Teacher</th>
                        <th>Schedule</th>
                        <th>Room</th>
                        <th>Students</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($classes as $c)
                        <tr>
                            <td><strong>{{ $c->class_name }}</strong><br><small class="text-muted">{{ $c->grade_level }} · {{ $c->semester }} · {{ $c->school_year }}</small></td>
                            <td>{{ $c->subject }}</td>
                            <td>{{ $c->teacher->full_name ?? '-' }}</td>
                            <td><small>{{ $c->schedule ?? '-' }}</small></td>
                            <td>{{ $c->room ?? '-' }}</td>
                            <td>{{ $c->students->count() }}</td>
                            <td><span class="badge bg-{{ $c->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($c->status) }}</span></td>
                            <td>
                                <a href="/classes/{{ $c->id }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                <a href="/classes/{{ $c->id }}/edit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No classes found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
