@extends('layouts.app')
@section('title', $assignment->title . ' — StudentFlow')
@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-journal-text"></i> {{ $assignment->title }}</h2>
            <p class="text-muted mb-0">
                <a href="/classes/{{ $assignment->class_id }}">{{ $assignment->class->class_name ?? '—' }}</a>
                · {{ $assignment->class->subject ?? '' }}
            </p>
        </div>
        <div>
            <a href="/assignments/{{ $assignment->id }}/edit" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i> Edit</a>
            <form method="POST" action="/assignments/{{ $assignment->id }}" class="d-inline">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger" onclick="return confirm('Delete this assignment?')"><i class="bi bi-trash"></i> Delete</button>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card stat-card"><div class="card-body">
                <div class="stat-label">Date Assigned</div>
                <div>{{ $assignment->date_assigned->format('M d, Y') }}</div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card"><div class="card-body">
                <div class="stat-label">Deadline</div>
                <div>{{ $assignment->deadline->format('M d, Y') }}</div>
            </div></div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card"><div class="card-body">
                <div class="stat-label">Max Score</div>
                <div class="stat-value text-primary">{{ $assignment->maximum_score }}</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card"><div class="card-body">
                <div class="stat-label">Status</div>
                @php
                    $color = match($assignment->status) {
                        'Upcoming' => 'info', 'Active' => 'success',
                        'Overdue' => 'warning', 'Completed' => 'primary',
                        'Cancelled' => 'secondary', default => 'secondary',
                    };
                @endphp
                <span class="badge bg-{{ $color }} fs-6">{{ $assignment->status }}</span>
                @if ($assignment->attachment_link)
                    <a href="{{ $assignment->attachment_link }}" target="_blank" class="ms-3"><i class="bi bi-link-45deg"></i> Attachment</a>
                @endif
            </div></div>
        </div>
    </div>

    <div class="card stat-card">
        <div class="card-header bg-white"><h5 class="mb-0">Description / Instructions</h5></div>
        <div class="card-body">
            {!! nl2br(e($assignment->description ?? 'No description provided.')) !!}
        </div>
    </div>

    <div class="card stat-card mt-3">
        <div class="card-header bg-white"><h5 class="mb-0">Student Completion and Scores</h5></div>
        <div class="card-body p-0">
            <form method="POST" action="/assignments/{{ $assignment->id }}/submissions">
                @csrf
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr><th>Student</th><th>Status</th><th>Score</th><th>Submitted At</th><th>Attachment</th><th>Remarks</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($assignment->class->students as $student)
                                @php $submission = $assignment->submissions->firstWhere('student_id', $student->id); @endphp
                                <tr>
                                    <td><strong>{{ $student->full_name }}</strong><br><small>{{ $student->student_number }}</small></td>
                                    <td>
                                        <select name="submissions[{{ $student->id }}][status]" class="form-select form-select-sm">
                                            @foreach (['Pending', 'Submitted', 'Late', 'Missing', 'Excused'] as $status)
                                                <option value="{{ $status }}" @selected(($submission->status ?? 'Pending') === $status)>{{ $status }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" step="0.01" min="0" name="submissions[{{ $student->id }}][score]" class="form-control form-control-sm" value="{{ $submission->score ?? '' }}"></td>
                                    <td><input type="datetime-local" name="submissions[{{ $student->id }}][submitted_at]" class="form-control form-control-sm" value="{{ $submission?->submitted_at?->format('Y-m-d\\TH:i') }}"></td>
                                    <td><input type="url" name="submissions[{{ $student->id }}][attachment_link]" class="form-control form-control-sm" value="{{ $submission->attachment_link ?? '' }}"></td>
                                    <td><input name="submissions[{{ $student->id }}][remarks]" class="form-control form-control-sm" value="{{ $submission->remarks ?? '' }}"></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No students enrolled in this class.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">
                    <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Submissions</button>
                </div>
            </form>
        </div>
    </div>
@endsection
