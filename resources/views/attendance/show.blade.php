@extends('layouts.app')
@section('title', 'Mark Attendance - ' . $schoolClass->class_name)
@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-pencil-square"></i> Mark Attendance</h2>
            <p class="text-muted mb-0">{{ $schoolClass->class_name }} - {{ $schoolClass->subject }}</p>
        </div>
        <div>
            <a href="/attendance/{{ $schoolClass->id }}/history" class="btn btn-outline-secondary"><i class="bi bi-clock-history"></i> History</a>
            <a href="/attendance" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="/attendance/{{ $schoolClass->id }}">
        @csrf
        <input type="hidden" name="attendance_date" value="{{ $date }}">

        <div class="card stat-card mb-3">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <label class="form-label mb-1">Date</label>
                    <input type="date" name="attendance_date_display" class="form-control form-control-sm" value="{{ $date }}" onchange="this.form.attendance_date.value=this.value; this.form.submit();">
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="markAll('Present')"><i class="bi bi-check-all"></i> Mark all Present</button>
                </div>
            </div>
        </div>

        @if ($students->isEmpty())
            <div class="alert alert-warning">No students enrolled in this class.</div>
        @else
            <div class="card stat-card">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $i => $s)
                                @php $rec = $existing[$s->id] ?? null; @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <a href="/students/{{ $s->id }}">{{ $s->full_name }}</a><br>
                                        <small class="text-muted">{{ $s->student_number }}</small>
                                    </td>
                                    <td>
                                        <select name="records[{{ $s->id }}][status]" class="form-select form-select-sm status-select" data-student-id="{{ $s->id }}">
                                            <option value="">-</option>
                                            <option value="Present" {{ ($rec->status ?? '') === 'Present' ? 'selected' : '' }}>Present</option>
                                            <option value="Absent" {{ ($rec->status ?? '') === 'Absent' ? 'selected' : '' }}>Absent</option>
                                            <option value="Late" {{ ($rec->status ?? '') === 'Late' ? 'selected' : '' }}>Late</option>
                                            <option value="Excused" {{ ($rec->status ?? '') === 'Excused' ? 'selected' : '' }}>Excused</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="records[{{ $s->id }}][remarks]" class="form-control form-control-sm" value="{{ $rec->remarks ?? '' }}" placeholder="Optional remarks">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Attendance</button>
            </div>
        @endif
    </form>

    <script>
        function markAll(status) {
            document.querySelectorAll('.status-select').forEach(s => s.value = status);
        }
    </script>
@endsection
