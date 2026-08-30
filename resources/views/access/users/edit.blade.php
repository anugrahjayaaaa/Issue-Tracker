@extends('layouts.app')
@section('content')
<h3>{{ isset($user) ? ui('edit') . ' ' . ui('user') : ui('new_user') }}</h3>

{{-- Account status / admin actions: placed OUTSIDE the main update form to avoid nested-form submit --}}
@if (isset($user) && !$user->trashed() && $user->id !== auth()->id() && (auth()->user()->can('user.lock') || auth()->user()->can('user.edit')))
    <div class="alert alert-light d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <span>Status:
                @if ($user->isPermanentlyLocked())
                    <span class="badge text-bg-danger">{{ ui('perm_locked') }}</span>
                @elseif ($user->isLocked())
                    <span class="badge text-bg-warning">{{ ui('locked') }}</span>
                @else
                    <span class="badge text-bg-success">{{ ui('active') }}</span>
                @endif
            </span>
            @if ($user->last_login_at)
                <span class="text-muted small">{{ ui('last_login', ['time' => $user->last_login_at->format('Y-m-d H:i'), 'ip' => $user->last_login_ip]) }}</span>
            @endif
            @if (auth()->user()->can('user.lock'))
                @if ($user->isLocked())
                    <span>Unlock:
                        <form method="POST" action="{{ route('users.unlock', $user) }}" class="d-inline">@csrf
                            <button class="btn btn-sm btn-warning">{{ ui('unlock') }}</button>
                        </form>
                    </span>
                @else
                    <span>Lock:
                        <form method="POST" action="{{ route('users.lock', $user) }}" class="d-inline">@csrf
                            <button class="btn btn-sm btn-danger">{{ ui('lock') }}</button>
                        </form>
                    </span>
                @endif
            @endif
        </div>
        @if (auth()->user()->can('user.edit'))
        <span>{{ ui('send_reset_email_label') }}
            <form method="POST" action="{{ route('users.reset-password', $user) }}" class="d-inline">@csrf
                <button class="btn btn-sm btn-secondary">{{ ui('send_reset_email') }}</button>
            </form>
        </span>
        @endif
    </div>
@endif

<form method="POST" action="{{ isset($user) ? route('users.update', $user) : route('users.store') }}">
    @csrf
    @if (isset($user)) @method('PUT') @endif
    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ ui('name') }}</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name ?? '') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ ui('username') }}</label>
                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $user->username ?? '') }}" required>
                    @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ ui('email') }}</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email ?? '') }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ ui('phone') }}</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone ?? '') }}">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ ui('password') }}{{ isset($user) ? ' ' . ui('leave_blank_to_keep') : '' }}</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" {{ isset($user) ? '' : 'required' }}>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ ui('confirm_password') }}</label>
                    <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror">
                    @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label">{{ ui('roles') }}</label>
                    <div class="row">
                        @foreach ($roles as $role)
                        <div class="col-6 col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}"
                                    {{ (isset($user) && $user->roles->contains($role->id)) ? 'checked' : '' }}>
                                <label class="form-check-label" for="role_{{ $role->id }}">{{ $role->name }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('users.index') }}" class="btn btn-link">{{ ui('cancel') }}</a>
            <button class="btn btn-primary">{{ ui('save') }}</button>
        </div>
    </div>
</form>
@endsection
