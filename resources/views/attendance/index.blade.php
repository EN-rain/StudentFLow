@extends('layouts.app')
@section('title', 'Attendance — StudentFlow')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-check2-square"></i> Attendance</h2>
    </div>

    <p class="text-muted">Pick a class to mark attendance, or view attendance history.</p>

    <div class="row g-3">
        @forelse ($classes as $c)
            <div class="col-md-6 col-lg-4">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <h5 class="mb-1">{{ $c->class_name }}</h5>
                        <p class="text-muted small mb-3">{{ $c->subject }} · {{ $c->schedule ?? '—' }}</p>
                        <a href="/attendance/{{ $c->id }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i> Mark Attendance</a>
                        <a href="/attendance/{{ $c->id }}/history" class="btn btn-outline-secondary btn-sm"><i class="bi bi-clock-history"></i> History</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">No classes available. Teachers must be assigned to a class first.</div>
            </div>
        @endforelse
    </div>
@endsection
