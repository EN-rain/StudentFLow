@extends('layouts.app')
@section('title', 'Teacher Dashboard — StudentFlow')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-speedometer2"></i> Teacher Dashboard</h2>
        <span class="text-muted">Welcome, {{ $teacher->full_name }}</span>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-label">My Classes</div>
                    <div class="stat-value text-primary">{{ $totalClasses }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-label">My Students</div>
                    <div class="stat-value text-primary">{{ $totalStudents }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-label">Absent Today</div>
                    <div class="stat-value text-warning">{{ $absentToday }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-label">Pending Assignments</div>
                    <div class="stat-value text-info">{{ $pendingAssignments }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card stat-card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-megaphone"></i> My Recent Announcements</h5>
                </div>
                <div class="card-body">
                    @if ($recentAnnouncements->isEmpty())
                        <p class="text-muted mb-0">No announcements yet.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($recentAnnouncements as $a)
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <div class="fw-bold">{{ $a->title }}</div>
                                        <span class="badge bg-{{ $a->priority === 'Urgent' ? 'danger' : ($a->priority === 'Important' ? 'warning' : 'secondary') }}">{{ $a->priority }}</span>
                                    </div>
                                    <small class="text-muted">{{ Str::limit($a->message, 100) }}</small>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card stat-card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-clipboard-data"></i> Recent Grade Updates</h5>
                </div>
                <div class="card-body">
                    @if ($recentGrades->isEmpty())
                        <p class="text-muted mb-0">No grades yet.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($recentGrades as $g)
                                <li class="list-group-item d-flex justify-content-between">
                                    <div>
                                        <div class="fw-bold">{{ $g->student->full_name }}</div>
                                        <small class="text-muted">{{ $g->gradeItem->title }}</small>
                                    </div>
                                    <span class="badge bg-primary align-self-center">{{ $g->score }} / {{ $g->gradeItem->maximum_score }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
