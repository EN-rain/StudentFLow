@extends('layouts.app')
@section('title', $announcement->title . ' — StudentFlow')
@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            @php
                $color = match($announcement->priority) {
                    'Urgent' => 'danger', 'Important' => 'warning', default => 'secondary',
                };
            @endphp
            <span class="badge bg-{{ $color }} mb-2">{{ $announcement->priority }}</span>
            <h2 class="mb-1">{{ $announcement->title }}</h2>
            <p class="text-muted mb-0">
                Posted by {{ $announcement->teacher->full_name ?? '—' }}
                on {{ $announcement->publish_date->format('M d, Y') }}
                @if ($announcement->schoolClass) · for <strong>{{ $announcement->schoolClass->class_name }}</strong> @else · for <strong>All my classes</strong> @endif
            </p>
        </div>
        <div>
            <a href="/announcements/{{ $announcement->id }}/edit" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i> Edit</a>
            <form method="POST" action="/announcements/{{ $announcement->id }}" class="d-inline">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger" onclick="return confirm('Delete this announcement?')"><i class="bi bi-trash"></i> Delete</button>
            </form>
        </div>
    </div>

    <div class="card stat-card">
        <div class="card-body">
            {!! nl2br(e($announcement->message)) !!}
        </div>
    </div>

    @if ($announcement->expiration_date)
        <p class="text-muted mt-2"><small><i class="bi bi-clock"></i> Expires on {{ $announcement->expiration_date->format('M d, Y') }}</small></p>
    @endif
@endsection
