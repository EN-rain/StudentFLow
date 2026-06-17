@extends('layouts.app')
@section('title', 'Admin Dashboard — StudentFlow')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-speedometer2"></i> Administrator Dashboard</h2>
        <span class="text-muted">Welcome, {{ auth()->user()->name }}</span>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4 col-lg-2">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-label">Total Students</div>
                    <div class="stat-value text-primary">{{ $totalStudents }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-label">Total Classes</div>
                    <div class="stat-value text-primary">{{ $totalClasses }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-label">Total Teachers</div>
                    <div class="stat-value text-primary">{{ $totalTeachers }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-label">Absent Today</div>
                    <div class="stat-value text-warning">{{ $absentToday }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-label">Pending Assignments</div>
                    <div class="stat-value text-info">{{ $pendingAssignments }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-label">Recent Announcements</div>
                    <div class="stat-value text-success">{{ $recentAnnouncements->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card stat-card">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-megaphone"></i> Recent Announcements</h5>
        </div>
        <div class="card-body">
            @if ($recentAnnouncements->isEmpty())
                <p class="text-muted mb-0">No announcements yet.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach ($recentAnnouncements as $a)
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="me-3">
                                <div class="fw-bold">{{ $a->title }}</div>
                                <small class="text-muted">{{ Str::limit($a->message, 100) }}</small>
                            </div>
                            <span class="badge bg-{{ $a->priority === 'Urgent' ? 'danger' : ($a->priority === 'Important' ? 'warning' : 'secondary') }}">
                                {{ $a->priority }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
