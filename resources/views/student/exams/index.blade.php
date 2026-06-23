@extends('layouts.app')
@section('title', 'My Exams - StudentFlow')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-pencil-square"></i> My Exams</h2>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <div class="text-muted small text-uppercase">Total</div>
                    <div class="h3 mb-0">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <div class="text-muted small text-uppercase">Available Now</div>
                    <div class="h3 mb-0 text-primary">{{ $stats['available'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <div class="text-muted small text-uppercase">Submitted</div>
                    <div class="h3 mb-0 text-success">{{ $stats['submitted'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card stat-card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Exam</th>
                        <th>Class</th>
                        <th>Available</th>
                        <th>Due</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        @php
                            $badgeColor = match ($row['state']) {
                                'submitted' => 'success',
                                'in_progress' => 'warning',
                                'expired' => 'danger',
                                'no_attempt' => 'info',
                                default => 'secondary',
                            };
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $row['exam']->title }}</strong>
                                <br><small class="text-muted">Max score: {{ number_format((float) $row['exam']->maximum_score, 2) }}</small>
                            </td>
                            <td>{{ $row['exam']->schoolClass?->class_name ?? '—' }}</td>
                            <td><small>{{ optional($row['exam']->available_from)->format('M j, Y g:i A') ?? '—' }}</small></td>
                            <td>
                                @if ($row['exam']->due_at)
                                    <small>{{ $row['exam']->due_at->format('M j, Y g:i A') }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $row['exam']->duration_minutes ? $row['exam']->duration_minutes . ' min' : '—' }}</td>
                            <td>
                                <span class="badge bg-{{ $badgeColor }}">{{ ucfirst(str_replace('_', ' ', $row['state'])) }}</span>
                                @if ($row['attempt'] && $row['attempt']->score !== null)
                                    <br><small class="text-muted">Score: {{ number_format((float) $row['attempt']->score, 2) }}</small>
                                @endif
                            </td>
                            <td>
                                @if ($row['is_open'] && in_array($row['state'], ['no_attempt', 'assigned', 'in_progress'], true))
                                    <a href="{{ route('student.exams.start', $row['exam']->id) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-play-fill"></i>
                                        {{ $row['state'] === 'in_progress' ? 'Continue' : 'Start' }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>
                                No exams available right now.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection