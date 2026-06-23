@extends('layouts.app')
@section('title', 'My Classes - StudentFlow')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-collection"></i> My Classes</h2>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card stat-card mb-4">
        <div class="card-body">
            <p class="mb-0 text-muted">
                Showing <strong>{{ $classes->count() }}</strong> enrolled class{{ $classes->count() === 1 ? '' : 'es' }} for
                <strong>{{ $student->full_name }}</strong>.
            </p>
        </div>
    </div>

    <div class="card stat-card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Teacher</th>
                        <th>Schedule</th>
                        <th>Room</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($classes as $c)
                        <tr>
                            <td>
                                <strong>{{ $c->class_name }}</strong>
                                @if ($c->pivot->status === 'enrolled')
                                    <span class="badge bg-success ms-1">Enrolled</span>
                                @endif
                                <br>
                                <small class="text-muted">{{ $c->grade_level }} &middot; {{ $c->semester }} &middot; {{ $c->school_year }}</small>
                            </td>
                            <td>{{ $c->subject }}</td>
                            <td>{{ $c->teacher?->full_name ?? '-' }}</td>
                            <td><small>{{ $c->schedule ?? '-' }}</small></td>
                            <td>{{ $c->room ?? '-' }}</td>
                            <td>
                                <a href="{{ route('student.classes.show', $c->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>
                                No enrolled classes yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection