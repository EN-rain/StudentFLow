@extends('layouts.app')
@section('title', $class->class_name . ' - StudentFlow')
@section('content')
    <div class="mb-3">
        <a href="/student/classes" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Back to My Classes
        </a>
    </div>

    <div class="page-header mb-4">
        <div>
            <h2 class="mb-1">{{ $class->class_name }}</h2>
            <p class="text-muted mb-0">{{ $class->subject }}</p>
        </div>
    </div>

    <div class="row g-3">
        {{-- Class Information --}}
        <div class="col-md-6">
            <div class="card stat-card">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-info-circle"></i> Class Information</h5>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Subject</dt>
                        <dd class="col-sm-8">{{ $class->subject }}</dd>
                        <dt class="col-sm-4">Schedule</dt>
                        <dd class="col-sm-8">{{ $class->schedule ?? '—' }}</dd>
                        <dt class="col-sm-4">Teacher</dt>
                        <dd class="col-sm-8">{{ $class->teacher?->user?->full_name ?? '—' }}</dd>
                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $class->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($class->status) }}
                            </span>
                        </dd>
                        @if ($class->description)
                            <dt class="col-sm-4">Description</dt>
                            <dd class="col-sm-8">{{ $class->description }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        {{-- Announcements and Assignments stacked on right --}}
        <div class="col-md-6">
            <div class="card stat-card">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-megaphone"></i> Recent Announcements</h5>
                    @forelse ($class->announcements as $a)
                        <div class="mb-2">
                            <strong>{{ $a->title }}</strong>
                            @if ($a->priority)
                                <span class="badge bg-{{ $a->priority === 'Urgent' ? 'danger' : ($a->priority === 'Important' ? 'warning text-dark' : 'secondary') }} ms-1">
                                    {{ $a->priority }}
                                </span>
                            @endif
                            <small class="text-muted d-block">
                                {{ optional($a->publish_date)->format('M j, Y') ?? '—' }}
                            </small>
                            @if ($a->message)
                                <small class="text-muted">{{ Str::limit($a->message, 80) }}</small>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">No announcements yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="card stat-card mt-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-journal-text"></i> Recent Assignments</h5>
                    @forelse ($class->assignments as $a)
                        <div class="mb-2">
                            <strong>{{ $a->title }}</strong>
                            <span class="badge bg-{{ $a->status === 'published' ? 'success' : 'secondary' }} ms-1">
                                {{ ucfirst($a->status ?? 'draft') }}
                            </span>
                            <small class="text-muted d-block">
                                Due {{ optional($a->deadline)->format('M j, Y') ?? '—' }}
                            </small>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No assignments yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection