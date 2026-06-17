@extends('layouts.app')
@section('title', 'Login - StudentFlow')
@section('content')
<div class="login-card">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <div class="text-center mb-2">
                <img src="/images/studentflow-logo.png" alt="" width="48" height="48" class="mb-2">
                <h3 class="mb-0">StudentFlow</h3>
            </div>
            <p class="text-muted text-center mb-4">Sign in to continue</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="/login">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Username or Email</label>
                    <input type="text" name="username" class="form-control" value="{{ old('username') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">Sign in</button>
            </form>

            <div class="text-center mt-3">
                <a href="/forgot-password" class="text-decoration-none">Forgot password?</a>
            </div>

            <hr>
            <div class="small text-muted">
                <strong>Demo credentials:</strong><br>
                Admin: <code>admin</code> / <code>Admin123!</code><br>
                Teacher: <code>john.reyes</code> / <code>Teacher123!</code>
            </div>
        </div>
    </div>
</div>
@endsection
