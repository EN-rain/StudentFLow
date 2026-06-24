@extends('layouts.app')
@section('title', 'Teacher Setup - StudentFlow')
@section('content')
<div class="login-card">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <div class="text-center mb-2">
                <img src="/images/studentflow-logo-96.png" alt="" width="48" height="48" class="auth-logo mb-2" decoding="async">
                <h3 class="mb-0">Teacher Account Setup</h3>
            </div>
            <p class="text-muted text-center mb-4">Choose your username and password to activate your teacher account.</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="/teacher/setup" novalidate>
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="{{ $email }}" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" value="{{ old('username') }}" autocomplete="username" autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" autocomplete="new-password">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary w-100">Complete setup</button>
            </form>
        </div>
    </div>
</div>
@endsection
