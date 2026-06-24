@extends('layouts.app')
@section('title', 'Reset Password - StudentFlow')
@section('content')
    <div class="card login-card stat-card">
        <div class="card-body p-4">
            <h3 class="mb-3 text-center"><i class="bi bi-key"></i> Reset Password</h3>
            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="/reset-password" novalidate>
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $email) }}" autocomplete="email">
                </div>
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" autocomplete="new-password">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                </div>
                <button class="btn btn-primary w-100">Reset Password</button>
            </form>
        </div>
    </div>
@endsection
