<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Display Name</label>
        <input name="name" class="form-control" value="{{ old('name', $teacher->user->name ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $teacher->user->email ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Employee Number</label>
        <input name="employee_number" class="form-control" value="{{ old('employee_number', $teacher->employee_number ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">First Name</label>
        <input name="first_name" class="form-control" value="{{ old('first_name', $teacher->first_name ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Middle Name</label>
        <input name="middle_name" class="form-control" value="{{ old('middle_name', $teacher->middle_name ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Last Name</label>
        <input name="last_name" class="form-control" value="{{ old('last_name', $teacher->last_name ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Department</label>
        <input name="department" class="form-control" value="{{ old('department', $teacher->department ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Contact Number</label>
        <input name="contact_number" class="form-control" value="{{ old('contact_number', $teacher->contact_number ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach (['active' => 'Active', 'disabled' => 'Disabled'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $teacher->user->status ?? 'active') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
@if (! isset($teacher))
    <div class="alert alert-info mt-4 mb-0">
        The teacher will receive a setup link. They choose their own username and password from that link.
    </div>
@endif
<div class="mt-4">
    <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Save</button>
    <a href="/admin/teachers" class="btn btn-outline-secondary">Cancel</a>
</div>
