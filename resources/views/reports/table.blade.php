@extends('layouts.app')
@section('title', ($title ?? ucwords(str_replace('-', ' ', $type))) . ' - StudentFlow')
@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h2 class="mb-1">{{ $title ?? ucwords(str_replace('-', ' ', $type)) }}</h2>
            @if ($class)
                <p class="text-muted mb-0">{{ $class->class_name }} · {{ $class->subject }}</p>
            @elseif (isset($student))
                <p class="text-muted mb-0">{{ $student->full_name }}</p>
            @endif
        </div>
        <a href="/reports" class="btn btn-outline-secondary">Back</a>
    </div>
    <div class="card stat-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        @foreach (array_keys($rows[0] ?? ['No data' => '']) as $heading)
                            <th>{{ ucwords(str_replace('_', ' ', $heading)) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            @foreach ($row as $value)
                                <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td class="text-center text-muted py-4">No records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
