@extends('layouts.app')
@section('content')
<div class="row g-3 mb-3">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="rounded-circle d-flex align-items-center justify-content-center text-bg-primary" style="width:48px;height:48px"><i class="bi bi-people fs-4"></i></span>
                <div><div class="text-muted small">{{ ui('users') }}</div><div class="fs-4 fw-semibold">{{ $userCount ?? 0 }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="rounded-circle d-flex align-items-center justify-content-center text-bg-success" style="width:48px;height:48px"><i class="bi bi-shield fs-4"></i></span>
                <div><div class="text-muted small">{{ ui('roles') }}</div><div class="fs-4 fw-semibold">{{ $roleCount ?? 0 }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="rounded-circle d-flex align-items-center justify-content-center text-bg-warning" style="width:48px;height:48px"><i class="bi bi-journal-text fs-4"></i></span>
                <div><div class="text-muted small">{{ ui('audit_entries') }}</div><div class="fs-4 fw-semibold">{{ $auditCount ?? 0 }}</div></div>
            </div>
        </div>
    </div>
</div>

{{-- License status badge (REQUIRED, doc §9b) --}}
@php
    $licStatus = $licenseStatus ?? 'none';
    $licBadge = match ($licStatus) {
        'active' => 'text-bg-success',
        'expired', 'revoked' => 'text-bg-danger',
        'none' => 'text-bg-secondary',
        default => 'text-bg-warning',
    };
    $licText = match ($licStatus) {
        'active' => 'License: '.($licenseDaysLeft === null ? 'Lifetime' : $licenseDaysLeft.' days left'),
        'expired' => 'Expired — downgraded to Free',
        'revoked' => 'Revoked — downgraded to Free',
        'none' => 'No license — Free plan',
        default => 'License: '.$licStatus,
    };
@endphp
<div class="mb-3">
    <span class="badge {{ $licBadge }} fs-6">
        <i class="bi bi-patch-check me-1"></i>{{ $licText }}
        <span class="opacity-75 ms-1">({{ $activePlan ?? 'free' }})</span>
    </span>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <h5 class="mb-1">{{ ui('welcome', ['name' => auth()->user()->name]) }} 👋</h5>
        <p class="text-muted mb-0">{{ ui('dashboard_subtitle') }}</p>
    </div>
</div>
@endsection
