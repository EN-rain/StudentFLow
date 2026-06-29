@extends('layouts.app')
@section('title', 'Register - StudentFlow')
@section('body_class', 'page-login')
@section('content')
<div class="login-card">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <div class="text-center mb-3">
                <img src="/images/studentflow-logo-96.png" alt="" width="48" height="48" class="auth-logo mb-2" decoding="async">
                <h3 class="mb-0">Create Student Account</h3>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="/register" novalidate>
                @csrf
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" autocomplete="name" autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" autocomplete="email">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" autocomplete="new-password">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary w-100">Create account</button>
            </form>

            <div class="text-center mt-3">
                <a href="/login" class="text-decoration-none">Back to login</a>
            </div>
        </div>
    </div>
</div>
@endsection
