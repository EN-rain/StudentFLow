@extends('layouts.app')
@section('title', 'Assignments — StudentFlow')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-journal-text"></i> Assignments</h2>
        <a href="/assignments/create" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Assignment</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card stat-card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Class</th>
                        <th>Date Assigned</th>
                        <th>Deadline</th>
                        <th>Max Score</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignments as $a)
                        <tr>
                            <td>
                                <a href="/assignments/{{ $a->id }}"><strong>{{ $a->title }}</strong></a>
                                @if ($a->description)<br><small class="text-muted">{{ Str::limit($a->description, 80) }}</small>@endif
                            </td>
                            <td>{{ $a->class->class_name ?? '—' }}</td>
                            <td>{{ $a->date_assigned->format('M d, Y') }}</td>
                            <td>{{ $a->deadline->format('M d, Y') }}</td>
                            <td>{{ $a->maximum_score }}</td>
                            <td>
                                @php
                                    $color = match($a->status) {
                                        'Upcoming' => 'info',
                                        'Active' => 'success',
                                        'Overdue' => 'warning',
                                        'Completed' => 'primary',
                                        'Cancelled' => 'secondary',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ $a->status }}</span>
                            </td>
                            <td>
                                <a href="/assignments/{{ $a->id }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                <a href="/assignments/{{ $a->id }}/edit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No assignments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
