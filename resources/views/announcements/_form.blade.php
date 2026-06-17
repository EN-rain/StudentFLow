@csrf
<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $announcement->title ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Priority</label>
        <select name="priority" class="form-select">
            @foreach (['Normal', 'Important', 'Urgent'] as $p)
                <option value="{{ $p }}" {{ old('priority', $announcement->priority ?? 'Normal') === $p ? 'selected' : '' }}>{{ $p }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label">Message <span class="text-danger">*</span></label>
        <textarea name="message" class="form-control" rows="6" required>{{ old('message', $announcement->message ?? '') }}</textarea>
    </div>
    <div class="col-md-4">
        <label class="form-label">Target Class (optional)</label>
        <select name="class_id" class="form-select">
            <option value="">All my classes</option>
            @foreach ($classes as $c)
                <option value="{{ $c->id }}" {{ (string) old('class_id', $announcement->class_id ?? '') === (string) $c->id ? 'selected' : '' }}>
                    {{ $c->class_name }} — {{ $c->subject }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Publish Date <span class="text-danger">*</span></label>
        <input type="date" name="publish_date" class="form-control" value="{{ old('publish_date', isset($announcement->publish_date) ? $announcement->publish_date->format('Y-m-d') : date('Y-m-d')) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Expiration Date (optional)</label>
        <input type="date" name="expiration_date" class="form-control" value="{{ old('expiration_date', isset($announcement->expiration_date) ? $announcement->expiration_date->format('Y-m-d') : '') }}">
    </div>
</div>
<div class="mt-4">
    <button type="submit" class="btn btn-primary"><i class="bi bi-megaphone"></i> Post Announcement</button>
    <a href="/announcements" class="btn btn-outline-secondary">Cancel</a>
</div>
