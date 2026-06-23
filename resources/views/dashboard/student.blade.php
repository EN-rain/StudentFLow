@extends('layouts.app')
@section('title', 'Student Dashboard - StudentFlow')
@section('content')
    <div class="page-header">
        <div>
            <h2><i class="bi bi-speedometer2 me-2"></i>Student Dashboard</h2>
            <p>Your classes, assignments, grades, and upcoming exams at a glance.</p>
        </div>
        <div class="text-muted">Welcome, {{ $student->full_name ?? auth()->user()->name }}</div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-label">Enrolled Classes</div>
                    <div class="stat-value text-primary">{{ $classes_count ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-label">Recent Grades</div>
                    <div class="stat-value text-info">&mdash;</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-label">Pending Assignments</div>
                    <div class="stat-value text-primary">{{ $assignments_count ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-label">Upcoming Exams</div>
                    <div class="stat-value text-warning">{{ $pending_exams_count ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection