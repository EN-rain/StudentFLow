@extends('reports._pdf')
@section('title', 'Attendance Report')

@section('content')
    <h1>Attendance Report</h1>
    <div class="meta">
        <strong>{{ $class->class_name }}</strong> — {{ $class->subject }}<br>
        Teacher: {{ $class->teacher->full_name ?? '—' }}<br>
        Generated: {{ now()->format('M d, Y H:i') }}
    </div>

    <h2>Per-Student Attendance</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student Number</th>
                <th>Name</th>
                <th class="num">Total</th>
                <th class="num">Present/Late</th>
                <th class="num">Absent</th>
                <th class="num">Late</th>
                <th class="num">Excused</th>
                <th class="num">%</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['student_number'] }}</td>
                    <td>{{ $r['name'] }}</td>
                    <td class="num">{{ $r['total'] }}</td>
                    <td class="num">{{ $r['present'] }}</td>
                    <td class="num">{{ $r['absent'] }}</td>
                    <td class="num">{{ $r['late'] }}</td>
                    <td class="num">{{ $r['excused'] }}</td>
                    <td class="num">{{ $r['percentage'] !== null ? $r['percentage'] . '%' : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="9" style="text-align:center;">No attendance records.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p style="margin-top: 24px; font-size: 9pt; color: #888;">
        <em>Attendance % = (Present + Late) / Total records × 100</em>
    </p>
@endsection
