@extends('layouts.app')
@section('title', 'My Assignments - StudentFlow')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-clipboard-check"></i> My Assignments</h2>
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
                    <div class="text-muted small text-uppercase">Pending</div>
                    <div class="h3 mb-0 text-warning">{{ $stats['pending'] }}</div>
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
                        <th>Assignment</th>
                        <th>Class</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        @php
                            $sub = $row['submission'];
                            $badgeColor = match ($sub?->status) {
                                'Submitted' => 'success',
                                'Late' => 'warning',
                                'Pending' => 'info',
                                'Missing' => 'danger',
                                'Excused' => 'secondary',
                                default => 'secondary',
                            };
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $row['assignment']->title }}</strong>
                                @if ($row['is_past_deadline'])
                                    <br><small class="text-danger"><i class="bi bi-clock-history"></i> Past deadline</small>
                                @endif
                            </td>
                            <td>{{ $row['assignment']->class?->class_name ?? '—' }}</td>
                            <td>
                                {{ optional($row['assignment']->deadline)->format('M j, Y') ?? '—' }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $badgeColor }}">{{ $sub?->status ?? 'Not submitted' }}</span>
                            </td>
                            <td>
                                <a href="{{ route('student.assignments.show', $row['assignment']->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>
                                No assignments yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection