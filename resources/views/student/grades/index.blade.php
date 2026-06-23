@extends('layouts.app')
@section('title', 'My Grades - StudentFlow')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-mortarboard"></i> My Grades</h2>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card stat-card mb-4">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-6">
                    <div class="text-muted small text-uppercase">Classes with Grades</div>
                    <div class="h3 mb-0">{{ $countWithGrades }} / {{ $rows->count() }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small text-uppercase">Average Final</div>
                    <div class="h3 mb-0">
                        @if ($rows->count() > 0)
                            <span class="badge bg-{{ $averageFinal >= 85 ? 'success' : ($averageFinal >= 75 ? 'warning' : 'danger') }}">
                                {{ number_format($averageFinal, 2) }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>
            </div>
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
                        <th>Categories</th>
                        <th>Final Grade</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td><strong>{{ $row['class']->class_name }}</strong></td>
                            <td>{{ $row['class']->subject }}</td>
                            <td>{{ $row['class']->teacher?->user?->full_name ?? '—' }}</td>
                            <td>{{ $row['category_count'] }}</td>
                            <td>
                                @if ($row['final'] > 0)
                                    <span class="badge bg-{{ $row['final'] >= 85 ? 'success' : ($row['final'] >= 75 ? 'warning' : 'danger') }}">
                                        {{ number_format($row['final'], 2) }} ({{ $row['letter'] }})
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('student.grades.show', $row['class']->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Breakdown
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