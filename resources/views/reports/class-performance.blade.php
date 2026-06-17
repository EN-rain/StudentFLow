@extends('reports._pdf')
@section('title', 'Class Performance Report')

@section('content')
    <h1>Class Performance Report</h1>
    <div class="meta">
        <strong>{{ $class->class_name }}</strong> — {{ $class->subject }}<br>
        Teacher: {{ $class->teacher->full_name ?? '—' }}<br>
        Generated: {{ now()->format('M d, Y H:i') }}
    </div>

    <h2>Combined Performance Summary</h2>
    <p>
        <strong>Class Average:</strong> {{ $classAverage !== null ? $classAverage : 'N/A' }}<br>
        <strong>Total Students:</strong> {{ count($rows) }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>Student Number</th>
                <th>Name</th>
                <th class="num">Attendance %</th>
                <th class="num">Final Grade</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $r)
                <tr>
                    <td>{{ $r['rank'] }}</td>
                    <td>{{ $r['student_number'] }}</td>
                    <td>{{ $r['name'] }}</td>
                    <td class="num">{{ $r['percentage'] !== null ? $r['percentage'] . '%' : '—' }}</td>
                    <td class="num"><strong>{{ $r['final_grade'] }}</strong></td>
                    <td>
                        <span class="{{ $r['status'] === 'Pass' ? 'badge-pass' : 'badge-fail' }}">{{ $r['status'] }}</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;">No data.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
