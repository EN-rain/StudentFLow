@extends('layouts.app')
@section('title', 'My Announcements - StudentFlow')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-megaphone"></i> My Announcements</h2>
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
                    <div class="text-muted small text-uppercase">Urgent</div>
                    <div class="h3 mb-0 text-danger">{{ $stats['urgent'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <div class="text-muted small text-uppercase">Important</div>
                    <div class="h3 mb-0 text-warning">{{ $stats['important'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card stat-card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Class</th>
                        <th>Posted By</th>
                        <th>Posted</th>
                        <th>Expires</th>
                        <th>Priority</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($announcements as $a)
                        @php
                            $badgeColor = match ($a->priority) {
                                'Urgent' => 'danger',
                                'Important' => 'warning',
                                'Normal' => 'secondary',
                                default => 'secondary',
                            };
                        @endphp
                        <tr>
                            <td><strong>{{ $a->title }}</strong></td>
                            <td>
                                @if ($a->schoolClass)
                                    {{ $a->schoolClass->class_name }}
                                @else
                                    <span class="badge bg-info">All Classes</span>
                                @endif
                            </td>
                            <td>{{ $a->teacher?->user?->full_name ?? '—' }}</td>
                            <td><small>{{ optional($a->publish_date)->format('M j, Y') ?? '—' }}</small></td>
                            <td><small>{{ optional($a->expiration_date)->format('M j, Y') ?? '—' }}</small></td>
                            <td><span class="badge bg-{{ $badgeColor }}">{{ $a->priority }}</span></td>
                            <td>
                                <a href="{{ route('student.announcements.show', $a->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Read
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>
                                No announcements right now.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection