@extends('layouts.app')
@section('title', 'Attendance History — ' . $class->class_name)
@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-clock-history"></i> Attendance History</h2>
            <p class="text-muted mb-0">{{ $class->class_name }} — {{ $class->subject }}</p>
        </div>
        <div>
            <a href="/attendance/{{ $class->id }}" class="btn btn-primary"><i class="bi bi-pencil-square"></i> Mark Attendance</a>
            <a href="/attendance" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    <form method="GET" action="/attendance/{{ $class->id }}/history" class="row g-2 mb-3">
        <div class="col-md-3">
            <label class="form-label">From</label>
            <input type="date" name="from" class="form-control" value="{{ $from }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">To</label>
            <input type="date" name="to" class="form-control" value="{{ $to }}">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-outline-primary"><i class="bi bi-filter"></i> Filter</button>
        </div>
    </form>

    <div class="row g-3 mb-4">
        @foreach ($summary as $row)
            <div class="col-md-4 col-lg-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="fw-bold">{{ $row['student']->full_name ?? '—' }}</div>
                        <small class="text-muted">{{ $row['student']->student_number ?? '' }}</small>
                        <div class="stat-value text-{{ $row['percentage'] !== null && $row['percentage'] >= 75 ? 'success' : 'warning' }}">
                            {{ $row['percentage'] !== null ? $row['percentage'] . '%' : '—' }}
                        </div>
                        <small class="text-muted">{{ $row['present'] }} of {{ $row['total'] }} records</small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card stat-card">
        <div class="card-header bg-white"><h5 class="mb-0">Records ({{ $records->count() }})</h5></div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Date</th><th>Student</th><th>Status</th><th>Remarks</th></tr>
                </thead>
                <tbody>
                    @forelse ($records as $r)
                        <tr>
                            <td>{{ $r->attendance_date->format('M d, Y') }}</td>
                            <td>{{ $r->student->full_name ?? '—' }}</td>
                            <td>
                                <span class="badge bg-{{ $r->status === 'Present' ? 'success' : ($r->status === 'Late' ? 'warning' : ($r->status === 'Excused' ? 'info' : 'danger')) }}">
                                    {{ $r->status }}
                                </span>
                            </td>
                            <td><small>{{ $r->remarks ?? '' }}</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No attendance records in this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
