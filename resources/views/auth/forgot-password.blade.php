@extends('layouts.app')
@section('title', 'Forgot Password — StudentFlow')
@section('content')
<div class="login-card">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h3 class="mb-3 text-center">Reset Password</h3>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="/forgot-password">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required autofocus>
                </div>
                <button type="submit" class="btn btn-primary w-100">Send reset link</button>
            </form>

            <div class="text-center mt-3">
                <a href="/login" class="text-decoration-none">Back to login</a>
            </div>
        </div>
    </div>
</div>
@endsection
