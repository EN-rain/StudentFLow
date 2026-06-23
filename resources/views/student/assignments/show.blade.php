@extends('layouts.app')
@section('title', $assignment->title . ' - StudentFlow')
@section('content')
    <div class="mb-3">
        <a href="/student/assignments" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Back to My Assignments
        </a>
    </div>

    <div class="page-header mb-4">
        <h2 class="mb-1"><i class="bi bi-clipboard-check"></i> {{ $assignment->title }}</h2>
        <p class="text-muted mb-0">
            {{ $assignment->class?->class_name ?? '—' }}
            &middot;
            Assigned: {{ optional($assignment->date_assigned)->format('M j, Y') ?? '—' }}
            &middot;
            Deadline: <strong>{{ optional($assignment->deadline)->format('M j, Y') ?? '—' }}</strong>
        </p>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card stat-card mb-4">
        <div class="card-body">
            <h5 class="card-title">Description</h5>
            <p class="mb-2">{{ $assignment->description ?: '—' }}</p>
            <dl class="row mb-0">
                <dt class="col-sm-3">Maximum score</dt>
                <dd class="col-sm-9">{{ number_format((float) $assignment->maximum_score, 2) }}</dd>
                @if ($assignment->attachment_link)
                    <dt class="col-sm-3">Reference link</dt>
                    <dd class="col-sm-9">
                        <a href="{{ $assignment->attachment_link }}" target="_blank" rel="noopener">{{ $assignment->attachment_link }}</a>
                    </dd>
                @endif
            </dl>
        </div>
    </div>

    <div class="card stat-card mb-4">
        <div class="card-body">
            <h5 class="card-title">My Submission</h5>
            @if ($submission)
                <dl class="row mb-0">
                    <dt class="col-sm-3">Status</dt>
                    <dd class="col-sm-9">
                        @php
                            $badgeColor = match ($submission->status) {
                                'Submitted' => 'success',
                                'Late' => 'warning',
                                'Pending' => 'info',
                                'Missing' => 'danger',
                                'Excused' => 'secondary',
                                default => 'secondary',
                            };
                        @endphp
                        <span class="badge bg-{{ $badgeColor }}">{{ $submission->status }}</span>
                    </dd>
                    <dt class="col-sm-3">Submitted at</dt>
                    <dd class="col-sm-9">{{ optional($submission->submitted_at)->format('M j, Y g:i A') ?? '—' }}</dd>
                    @if ($submission->attachment_link)
                        <dt class="col-sm-3">My attachment</dt>
                        <dd class="col-sm-9">
                            <a href="{{ $submission->attachment_link }}" target="_blank" rel="noopener">{{ $submission->attachment_link }}</a>
                        </dd>
                    @endif
                    @if ($submission->remarks)
                        <dt class="col-sm-3">Remarks</dt>
                        <dd class="col-sm-9">{{ $submission->remarks }}</dd>
                    @endif
                    @if ($submission->score !== null)
                        <dt class="col-sm-3">Score</dt>
                        <dd class="col-sm-9">
                            <span class="badge bg-primary">{{ number_format((float) $submission->score, 2) }} / {{ number_format((float) $assignment->maximum_score, 2) }}</span>
                        </dd>
                    @endif
                </dl>
            @else
                <p class="text-muted mb-0"><i class="bi bi-info-circle"></i> You haven't submitted this assignment yet.</p>
            @endif
        </div>
    </div>

    @if (! $submission || in_array($submission->status, ['Pending', 'Missing'], true))
        <div class="card stat-card">
            <div class="card-body">
                <h5 class="card-title">
                    {{ $submission ? 'Resubmit' : 'Submit' }}
                    @if ($isPastDeadline)
                        <span class="badge bg-warning ms-2">Past deadline — will be marked Late</span>
                    @endif
                </h5>
                <form method="POST" action="{{ route('student.assignments.submit', $assignment->id) }}">
                    @csrf
                    <div class="mb-3">
                        <label for="attachment_link" class="form-label">Attachment Link (optional)</label>
                        <input type="url" name="attachment_link" id="attachment_link" class="form-control @error('attachment_link') is-invalid @enderror" placeholder="https://..." value="{{ old('attachment_link') }}">
                        @error('attachment_link')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks (optional)</label>
                        <textarea name="remarks" id="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror" placeholder="Any notes for your teacher...">{{ old('remarks') }}</textarea>
                        @error('remarks')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i>
                        {{ $isPastDeadline ? 'Submit (Late)' : 'Submit Assignment' }}
                    </button>
                </form>
            </div>
        </div>
    @endif
@endsection