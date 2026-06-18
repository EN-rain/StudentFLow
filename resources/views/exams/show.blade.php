@extends('layouts.app')

@section('title', $exam->title)

@section('content')
<div class="page-header mb-4">
    <div>
        <h1 class="h3 mb-1">{{ $exam->title }}</h1>
        <p class="text-muted mb-0">{{ $exam->schoolClass?->class_name }} — {{ $exam->schoolClass?->subject }}</p>
    </div>
    <div class="d-flex gap-2">
        @if ($exam->status === 'draft')
            <form method="POST" action="/exams/{{ $exam->id }}/publish">
                @csrf
                <button class="btn btn-success" type="submit">Publish</button>
            </form>
        @endif
        <a href="/exams" class="btn btn-outline-secondary">Back</a>
    </div>
</div>

@if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h2 class="h5">Exam details</h2>
            <dl class="row mb-0">
                <dt class="col-5">Status</dt><dd class="col-7">{{ ucfirst($exam->status) }}</dd>
                <dt class="col-5">Maximum score</dt><dd class="col-7">{{ $exam->maximum_score }}</dd>
                <dt class="col-5">Duration</dt><dd class="col-7">{{ $exam->duration_minutes ? $exam->duration_minutes.' minutes' : 'No limit' }}</dd>
                <dt class="col-5">Available</dt><dd class="col-7">{{ $exam->available_from?->format('M d, Y g:i A') ?? 'Immediately' }}</dd>
                <dt class="col-5">Due</dt><dd class="col-7">{{ $exam->due_at?->format('M d, Y g:i A') ?? 'No deadline' }}</dd>
            </dl>
            @if ($exam->instructions)
                <hr>
                <p class="mb-0">{{ $exam->instructions }}</p>
            @endif
        </div>

        <div class="card border-0 shadow-sm p-4">
            <h2 class="h5">Questions</h2>
            @foreach ($exam->questions as $question)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between gap-3">
                        <strong>{{ $loop->iteration }}. {{ $question->prompt }}</strong>
                        <span class="badge text-bg-light">{{ $question->points }} pts</span>
                    </div>
                    <div class="small text-muted mt-2">{{ str_replace('_', ' ', ucfirst($question->type)) }}</div>
                    @if (is_array($question->choices))
                        <ol class="mt-2 mb-2">
                            @foreach ($question->choices as $choice)
                                <li>{{ $choice }}</li>
                            @endforeach
                        </ol>
                    @endif
                    <div class="small"><strong>Answer:</strong> {{ $question->correct_answer ?: 'Manual review' }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="p-4 border-bottom">
                <h2 class="h5 mb-1">Student attempts</h2>
                <p class="text-muted mb-0">{{ $exam->attempts->where('status', 'submitted')->count() }} submitted of {{ $exam->attempts->count() }} assigned.</p>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Student</th><th>Status</th><th>Started</th><th>Submitted</th><th>Score</th><th>Link</th></tr></thead>
                    <tbody>
                        @forelse ($exam->attempts as $attempt)
                            <tr>
                                <td>{{ $attempt->student?->full_name }}<div class="small text-muted">{{ $attempt->student?->student_number }}</div></td>
                                <td>{{ ucfirst(str_replace('_', ' ', $attempt->status)) }}</td>
                                <td>{{ $attempt->started_at?->format('M d, g:i A') ?? '—' }}</td>
                                <td>{{ $attempt->submitted_at?->format('M d, g:i A') ?? '—' }}</td>
                                <td>{{ $attempt->score !== null ? $attempt->score.' / '.$exam->maximum_score : '—' }}</td>
                                <td><a href="/exam/magic/{{ $attempt->magic_token }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">Open</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5">No attempts assigned yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
