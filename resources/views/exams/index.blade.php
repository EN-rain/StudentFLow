@extends('layouts.app')

@section('title', 'Exams')

@section('content')
<div class="page-header mb-4">
    <div>
        <h1 class="h3 mb-1">Exams</h1>
        <p class="text-muted mb-0">Create, publish, and review student exam attempts.</p>
    </div>
    <a href="/exams/create" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Create Exam</a>
</div>

@if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Class</th>
                    <th>Status</th>
                    <th>Schedule</th>
                    <th>Attempts</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($exams as $exam)
                    <tr>
                        <td>
                            <strong>{{ $exam->title }}</strong>
                            <div class="small text-muted">{{ $exam->maximum_score }} points</div>
                        </td>
                        <td>{{ $exam->schoolClass?->class_name }}<div class="small text-muted">{{ $exam->schoolClass?->subject }}</div></td>
                        <td><span class="badge text-bg-{{ $exam->status === 'published' ? 'success' : ($exam->status === 'closed' ? 'secondary' : 'warning') }}">{{ ucfirst($exam->status) }}</span></td>
                        <td>
                            <div class="small">From: {{ $exam->available_from?->format('M d, Y g:i A') ?? 'Immediately' }}</div>
                            <div class="small">Due: {{ $exam->due_at?->format('M d, Y g:i A') ?? 'No deadline' }}</div>
                        </td>
                        <td>{{ $exam->attempts->count() }}</td>
                        <td class="text-end"><a href="/exams/{{ $exam->id }}" class="btn btn-sm btn-outline-primary">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">No exams created.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
