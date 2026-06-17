@extends('layouts.app')
@section('title', 'Reports — StudentFlow')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-file-earmark-bar-graph"></i> Reports</h2>
    </div>

    <p class="text-muted">Pick a class to generate reports. Each report is available as PDF, CSV, and printable HTML.</p>

    <div class="row g-3">
        @forelse ($classes as $c)
            <div class="col-md-6 col-lg-4">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <h5 class="mb-1">{{ $c->class_name }}</h5>
                        <p class="text-muted small mb-3">{{ $c->subject }} · {{ $c->students()->count() }} students</p>
                        <div class="btn-group-vertical w-100">
                            <div class="btn-group mb-1" role="group">
                                <a href="/reports/attendance?class_id={{ $c->id }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Attendance</a>
                                <a href="/reports/attendance/csv?class_id={{ $c->id }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-filetype-csv"></i> CSV</a>
                                <a href="/reports/attendance/pdf?class_id={{ $c->id }}" class="btn btn-sm btn-outline-danger"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
                            </div>
                            <div class="btn-group mb-1" role="group">
                                <a href="/reports/grades?class_id={{ $c->id }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Grades</a>
                                <a href="/reports/grades/csv?class_id={{ $c->id }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-filetype-csv"></i> CSV</a>
                                <a href="/reports/grades/pdf?class_id={{ $c->id }}" class="btn btn-sm btn-outline-danger"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
                            </div>
                            <div class="btn-group mb-1" role="group">
                                <a href="/reports/class-performance?class_id={{ $c->id }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Performance</a>
                                <a href="/reports/class-performance/csv?class_id={{ $c->id }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-filetype-csv"></i> CSV</a>
                                <a href="/reports/class-performance/pdf?class_id={{ $c->id }}" class="btn btn-sm btn-outline-danger"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
                            </div>
                            <div class="btn-group mb-1" role="group">
                                <a href="/reports/missing-assignments?class_id={{ $c->id }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-exclamation-circle"></i> Missing</a>
                                <a href="/reports/failing-grades?class_id={{ $c->id }}" class="btn btn-sm btn-outline-danger"><i class="bi bi-graph-down"></i> Failing</a>
                                <a href="/reports/frequent-absences?class_id={{ $c->id }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-calendar-x"></i> Absences</a>
                            </div>
                            @if ($c->students()->exists())
                                <div class="btn-group" role="group">
                                    <a href="/reports/student-profile?student_id={{ $c->students()->first()->id }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-person-vcard"></i> Sample Profile</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="alert alert-info">No classes available.</div></div>
        @endforelse
    </div>
@endsection
