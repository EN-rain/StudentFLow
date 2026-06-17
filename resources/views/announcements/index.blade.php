@extends('layouts.app')
@section('title', 'Announcements — StudentFlow')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-megaphone"></i> Announcements</h2>
        <a href="/announcements/create" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Announcement</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @forelse ($announcements as $a)
        @php
            $color = match($a->priority) {
                'Urgent' => 'danger',
                'Important' => 'warning',
                default => 'secondary',
            };
        @endphp
        <div class="card stat-card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge bg-{{ $color }} me-2">{{ $a->priority }}</span>
                        <h5 class="d-inline mb-0"><a href="/announcements/{{ $a->id }}">{{ $a->title }}</a></h5>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">{{ $a->publish_date->format('M d, Y') }}</small>
                        <small class="text-muted">{{ $a->teacher->full_name ?? '—' }}</small>
                    </div>
                </div>
                <p class="mb-1">{{ Str::limit($a->message, 200) }}</p>
                @if ($a->schoolClass)
                    <small class="text-muted"><i class="bi bi-collection"></i> {{ $a->schoolClass->class_name }}</small>
                @else
                    <small class="text-muted"><i class="bi bi-globe"></i> All classes</small>
                @endif
                <div class="mt-2">
                    <a href="/announcements/{{ $a->id }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> View</a>
                    <a href="/announcements/{{ $a->id }}/edit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i> Edit</a>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-info">No announcements yet.</div>
    @endforelse
@endsection
