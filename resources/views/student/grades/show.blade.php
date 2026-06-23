@extends('layouts.app')
@section('title', $class->class_name . ' Grades - StudentFlow')
@section('content')
    <div class="mb-3">
        <a href="/student/grades" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Back to My Grades
        </a>
    </div>

    <div class="page-header mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-mortarboard"></i> {{ $class->class_name }}</h2>
            <p class="text-muted mb-0">{{ $class->subject }} &middot; {{ $class->teacher?->user?->full_name ?? '—' }}</p>
        </div>
        <div class="text-end">
            <div class="text-muted small text-uppercase">Final Grade</div>
            @if ($final > 0)
                <span class="badge bg-{{ $final >= 85 ? 'success' : ($final >= 75 ? 'warning' : 'danger') }} fs-5">
                    {{ number_format($final, 2) }} ({{ $letter }})
                </span>
            @else
                <span class="text-muted fs-5">—</span>
            @endif
        </div>
    </div>

    @forelse ($categories as $cat)
        <div class="card stat-card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0"><i class="bi bi-folder"></i> {{ $cat['name'] }}</h5>
                    <div>
                        <span class="badge bg-secondary">Weight: {{ number_format((float) $cat['weight'], 1) }}%</span>
                        @if ($cat['category_average'] !== null)
                            <span class="badge bg-info ms-1">Avg: {{ $cat['category_average'] }}%</span>
                            <span class="badge bg-primary ms-1">Contrib: {{ $cat['weighted_contribution'] }}</span>
                        @endif
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Date Given</th>
                                <th>Score</th>
                                <th>Maximum</th>
                                <th>Percentage</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cat['rows'] as $row)
                                <tr>
                                    <td>{{ $row['title'] }}</td>
                                    <td><small>{{ optional($row['date_given'])->format('M j, Y') ?? '—' }}</small></td>
                                    <td>
                                        @if ($row['score'] !== null)
                                            {{ number_format((float) $row['score'], 2) }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format((float) $row['maximum_score'], 2) }}</td>
                                    <td>
                                        @if ($row['ratio'] !== null)
                                            <span class="badge bg-{{ $row['ratio'] >= 85 ? 'success' : ($row['ratio'] >= 75 ? 'warning' : 'danger') }}">
                                                {{ $row['ratio'] }}%
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td><small class="text-muted">{{ $row['remarks'] ?? '—' }}</small></td>
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
                No grade categories published yet.
            </div>
        </div>
    @endforelse
@endsection