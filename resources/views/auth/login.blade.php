@extends('layouts.app')
@section('title', 'Login - StudentFlow')
@section('body_class', 'page-login')
@section('content')
<div class="login-card">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <div class="text-center mb-2">
                <img src="/images/studentflow-logo-96.png" alt="" width="48" height="48" class="auth-logo mb-2" fetchpriority="high" decoding="async">
                <h3 class="mb-0">StudentFlow</h3>
            </div>
            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="/login" data-login-form novalidate>
                @csrf
                <div class="mb-3">
                    <label class="form-label">Username or Email</label>
                    <input type="text" name="username" class="form-control" value="{{ old('username') }}" autocomplete="username" autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-primary w-100" data-login-submit>
                    <span data-login-label>Sign in</span>
                </button>
            </form>

            <div class="text-center mt-3">
                <a href="/forgot-password" class="text-decoration-none">Forgot password?</a>
            </div>
            <div class="text-center mt-2">
                <a href="/register" class="text-decoration-none">Create student account</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelector('[data-login-form]')?.addEventListener('submit', function () {
        const button = this.querySelector('[data-login-submit]');
        const label = this.querySelector('[data-login-label]');
        if (!button || !label) {
            return;
        }

        button.disabled = true;
        label.textContent = 'Signing in...';
        window.setTimeout(function () {
            label.textContent = 'Logging in...';
        }, 700);
    });
</script>
@endpush
