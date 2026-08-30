@extends('layouts.auth')

@section('content')
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center"><span class="h1">{{ config('app.name', 'Laravel') }}</span></div>
        <div class="card-body">
            <p class="login-box-msg">{{ ui('sign_in_with') }}</p>

            @if ($errors->any())
                <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
            @endif

            @if (session('status'))
                <div class="alert alert-success py-2">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="input-group mb-3">
                    <input type="text" name="identifier" class="form-control @error('identifier') is-invalid @enderror" placeholder="{{ ui('email_or_username') }}" value="{{ old('identifier') }}" required autofocus>
                    @error('identifier')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="input-group-text"><i class="bi bi-person"></i></div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="{{ ui('password') }}" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <button type="button" class="input-group-text" id="toggle-password" aria-label="Show password" style="cursor:pointer">
                        <i class="bi bi-eye" id="password-icon"></i>
                    </button>
                </div>
                <div class="row">
                    <div class="col-8">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember">{{ ui('remember_me') }}</label>
                        </div>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary w-100">{{ ui('sign_in') }}</button>
                    </div>
                </div>
            </form>

            <p class="mb-0 mt-2">
                <a href="{{ route('password.request') }}">{{ ui('forgot_your_password') }}</a>
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Toggle password visibility. State is DOM-only (no persistence / no server state).
    (function () {
        const pwd = document.getElementById('password');
        const btn = document.getElementById('toggle-password');
        const icon = document.getElementById('password-icon');
        btn.addEventListener('click', function () {
            const show = pwd.type === 'password';
            pwd.type = show ? 'text' : 'password';
            icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    })();
</script>
@endpush
@endsection
