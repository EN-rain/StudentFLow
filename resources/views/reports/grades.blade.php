@extends('reports._pdf')
@section('title', 'Grade Report')

@section('content')
    <h1>Grade Report</h1>
    <div class="meta">
        <strong>{{ $class->class_name }}</strong> — {{ $class->subject }}<br>
        Teacher: {{ $class->teacher->full_name ?? '—' }}<br>
        Generated: {{ now()->format('M d, Y H:i') }}
    </div>

    <h2>Final Grades (Ranked)</h2>
    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>Student Number</th>
                <th>Name</th>
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
                    <td class="num"><strong>{{ $r['final_grade'] }}</strong></td>
                    <td>
                        <span class="{{ $r['status'] === 'Pass' ? 'badge-pass' : 'badge-fail' }}">{{ $r['status'] }}</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;">No grade records.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p>
        <strong>Class Average:</strong> {{ $classAverage !== null ? $classAverage : 'N/A' }}<br>
        <strong>Pass Threshold:</strong> 75
    </p>

    <p style="margin-top: 24px; font-size: 9pt; color: #888;">
        <em>Weighted formula per plan §4.6: Quizzes 20% + Activities 15% + Assignments 20% + Project 20% + Final Exam 25%</em>
    </p>
@endsection
