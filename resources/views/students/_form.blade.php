<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Student Number <span class="text-danger">*</span></label>
        <input type="text" name="student_number" class="form-control" value="{{ old('student_number', $student->student_number ?? '') }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">First Name <span class="text-danger">*</span></label>
        <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $student->first_name ?? '') }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Middle Name</label>
        <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name', $student->middle_name ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Last Name <span class="text-danger">*</span></label>
        <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $student->last_name ?? '') }}" required>
    </div>
    <div class="col-md-2">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-select">
            <option value="">-</option>
            <option value="Male" {{ old('gender', $student->gender ?? '') === 'Male' ? 'selected' : '' }}>Male</option>
            <option value="Female" {{ old('gender', $student->gender ?? '') === 'Female' ? 'selected' : '' }}>Female</option>
            <option value="Other" {{ old('gender', $student->gender ?? '') === 'Other' ? 'selected' : '' }}>Other</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Birth Date</label>
        <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', isset($student->birth_date) ? $student->birth_date->format('Y-m-d') : '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $student->email ?? '') }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Contact Number</label>
        <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $student->contact_number ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Address</label>
        <input type="text" name="address" class="form-control" value="{{ old('address', $student->address ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Guardian Name</label>
        <input type="text" name="guardian_name" class="form-control" value="{{ old('guardian_name', $student->guardian_name ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Guardian Contact</label>
        <input type="text" name="guardian_contact" class="form-control" value="{{ old('guardian_contact', $student->guardian_contact ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="active" {{ old('status', $student->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="disabled" {{ old('status', $student->status ?? '') === 'disabled' ? 'selected' : '' }}>Disabled</option>
        </select>
    </div>
</div>
<div class="mt-4">
    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save</button>
    <a href="/students" class="btn btn-outline-secondary">Cancel</a>
</div>
