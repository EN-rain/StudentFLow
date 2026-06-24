<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $assignment->title ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Class <span class="text-danger">*</span></label>
        <select name="class_id" class="form-select" required>
            <option value="">Select class</option>
            @foreach ($classes as $c)
                <option value="{{ $c->id }}" {{ (string) old('class_id', $assignment->class_id ?? '') === (string) $c->id ? 'selected' : '' }}>
                    {{ $c->class_name }} - {{ $c->subject }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="4">{{ old('description', $assignment->description ?? '') }}</textarea>
    </div>
    <div class="col-md-3">
        <label class="form-label">Date Assigned <span class="text-danger">*</span></label>
        <input type="date" name="date_assigned" class="form-control" value="{{ old('date_assigned', isset($assignment->date_assigned) ? $assignment->date_assigned->format('Y-m-d') : date('Y-m-d')) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Deadline <span class="text-danger">*</span></label>
        <input type="date" name="deadline" class="form-control" value="{{ old('deadline', isset($assignment->deadline) ? $assignment->deadline->format('Y-m-d') : date('Y-m-d', strtotime('+7 days'))) }}" required>
    </div>
    <div class="col-md-2">
        <label class="form-label">Maximum Score <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" name="maximum_score" class="form-control" value="{{ old('maximum_score', $assignment->maximum_score ?? 100) }}" required>
    </div>
    <div class="col-md-2">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach (['Upcoming', 'Active', 'Overdue', 'Completed', 'Cancelled'] as $s)
                <option value="{{ $s }}" {{ old('status', $assignment->status ?? 'Active') === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Attachment URL</label>
        <input type="url" name="attachment_link" class="form-control" value="{{ old('attachment_link', $assignment->attachment_link ?? '') }}">
    </div>
</div>
<div class="mt-4">
    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save</button>
    <a href="/assignments" class="btn btn-outline-secondary">Cancel</a>
</div>
