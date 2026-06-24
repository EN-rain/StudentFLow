@extends('layouts.app')
@section('title', 'Change Password - StudentFlow')
@section('content')
<div class="row">
    <div class="col-md-6 offset-md-3">
        <h2 class="mb-4"><i class="bi bi-key"></i> Change Password</h2>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="card stat-card">
            <div class="card-body">
                <form method="POST" action="/change-password" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Current password</label>
                        <input type="password" name="current_password" class="form-control" autocomplete="current-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New password</label>
                        <input type="password" name="new_password" class="form-control" autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm new password</label>
                        <input type="password" name="new_password_confirmation" class="form-control" autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-primary">Change password</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
