@extends('layouts.app')
@section('title', $announcement->title . ' - StudentFlow')
@section('content')
    <div class="mb-3">
        <a href="/student/announcements" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Back to My Announcements
        </a>
    </div>

    <div class="card stat-card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h2 class="mb-0"><i class="bi bi-megaphone"></i> {{ $announcement->title }}</h2>
                @php
                    $badgeColor = match ($announcement->priority) {
                        'Urgent' => 'danger',
                        'Important' => 'warning',
                        'Normal' => 'secondary',
                        default => 'secondary',
                    };
                @endphp
                <span class="badge bg-{{ $badgeColor }} fs-6">{{ $announcement->priority }}</span>
            </div>
            <p class="text-muted mb-3">
                @if ($announcement->schoolClass)
                    <i class="bi bi-journal-text"></i> {{ $announcement->schoolClass->class_name }}
                @else
                    <span class="badge bg-info"><i class="bi bi-globe"></i> All Classes</span>
                @endif
                &middot;
                <i class="bi bi-person"></i> {{ $announcement->teacher?->user?->full_name ?? '—' }}
                &middot;
                <i class="bi bi-calendar"></i> Posted {{ optional($announcement->publish_date)->format('M j, Y') ?? '—' }}
                @if ($announcement->expiration_date)
                    &middot;
                    <i class="bi bi-calendar-x"></i> Expires {{ $announcement->expiration_date->format('M j, Y') }}
                @endif
            </p>
            <hr>
            <div class="announcement-message" style="white-space: pre-wrap;">{{ $announcement->message }}</div>
        </div>
    </div>
@endsection