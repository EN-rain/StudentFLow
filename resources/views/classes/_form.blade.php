@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Class Name <span class="text-danger">*</span></label>
        <input type="text" name="class_name" class="form-control" value="{{ old('class_name', $class->class_name ?? '') }}" required>
    </div>
    <div class="col-md-2">
        <label class="form-label">Section</label>
        <input type="text" name="section" class="form-control" value="{{ old('section', $class->section ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Subject <span class="text-danger">*</span></label>
        <input type="text" name="subject" class="form-control" value="{{ old('subject', $class->subject ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Grade Level</label>
        <input type="text" name="grade_level" class="form-control" value="{{ old('grade_level', $class->grade_level ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">School Year</label>
        <input type="text" name="school_year" class="form-control" value="{{ old('school_year', $class->school_year ?? '2026-2027') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Semester</label>
        <input type="text" name="semester" class="form-control" value="{{ old('semester', $class->semester ?? 'Second Semester') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Schedule</label>
        <input type="text" name="schedule" class="form-control" value="{{ old('schedule', $class->schedule ?? '') }}" placeholder="e.g. MWF 9-10 AM">
    </div>
    <div class="col-md-3">
        <label class="form-label">Room</label>
        <input type="text" name="room" class="form-control" value="{{ old('room', $class->room ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="active" {{ old('status', $class->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="archived" {{ old('status', $class->status ?? '') === 'archived' ? 'selected' : '' }}>Archived</option>
        </select>
    </div>
    @if (auth()->user()->isAdmin())
        <div class="col-md-6">
            <label class="form-label">Teacher <span class="text-danger">*</span></label>
            <select name="teacher_id" class="form-select" required>
                <option value="">Select teacher</option>
                @foreach ($teachers as $t)
                    <option value="{{ $t->id }}" {{ (string) old('teacher_id', $class->teacher_id ?? '') === (string) $t->id ? 'selected' : '' }}>
                        {{ $t->full_name }} ({{ $t->employee_number }})
                    </option>
                @endforeach
            </select>
        </div>
    @else
        <input type="hidden" name="teacher_id" value="{{ auth()->user()->teacher->id ?? '' }}">
    @endif
</div>
<div class="mt-4">
    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save</button>
    <a href="/classes" class="btn btn-outline-secondary">Cancel</a>
</div>
