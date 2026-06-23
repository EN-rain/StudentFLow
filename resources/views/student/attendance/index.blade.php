@extends('layouts.app')
@section('title', 'My Attendance - StudentFlow')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-calendar-check"></i> My Attendance</h2>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card stat-card mb-4">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="text-muted small text-uppercase">Total Records</div>
                    <div class="h3 mb-0">{{ $overallTotal }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small text-uppercase">Present / Late</div>
                    <div class="h3 mb-0">{{ $overallPresent }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small text-uppercase">Attendance Rate</div>
                    <div class="h3 mb-0">
                        @if ($overallRate !== null)
                            <span class="badge bg-{{ $overallRate >= 85 ? 'success' : ($overallRate >= 75 ? 'warning' : 'danger') }}">{{ $overallRate }}%</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @forelse ($summary as $group)
        <div class="card stat-card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0"><i class="bi bi-journal-text"></i> {{ $group['class_name'] }}</h5>
                    <span class="badge bg-{{ ($group['rate'] ?? 0) >= 85 ? 'success' : (($group['rate'] ?? 0) >= 75 ? 'warning' : 'danger') }}">
                        {{ $group['rate'] !== null ? $group['rate'].'%' : 'N/A' }}
                    </span>
                </div>
                <div class="row text-center small text-muted mb-3">
                    <div class="col">Present: <strong>{{ $group['present'] }}</strong></div>
                    <div class="col">Late: <strong>{{ $group['late'] }}</strong></div>
                    <div class="col">Absent: <strong>{{ $group['absent'] }}</strong></div>
                    <div class="col">Excused: <strong>{{ $group['excused'] }}</strong></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($group['rows'] as $row)
                                <tr>
                                    <td>{{ $row->attendance_date->format('M j, Y') }}</td>
                                    <td>
                                        @php
                                            $badgeColor = match ($row->status) {
                                                'Present' => 'success',
                                                'Late' => 'warning',
                                                'Absent' => 'danger',
                                                'Excused' => 'info',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $badgeColor }}">{{ $row->status }}</span>
                                    </td>
                                    <td><small class="text-muted">{{ $row->remarks ?? '—' }}</small></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="card stat-card">
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>
                No attendance records yet.
            </div>
        </div>
    @endforelse
@endsection