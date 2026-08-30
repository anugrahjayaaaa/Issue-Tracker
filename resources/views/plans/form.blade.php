@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<h3>{{ $plan ? ui('edit_plan') : ui('new_plan') }}</h3>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ $plan ? route('plans.update', $plan) : route('plans.store') }}">
            @csrf
            @if($plan) @method('PUT') @endif

            {{-- General --}}
            <h6 class="text-uppercase text-muted small fw-bold mb-3">{{ ui('common_info') }}</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">{{ ui('name') }}</label>
                    <input type="text" name="name" id="plan-name" class="form-control"
                           value="{{ old('name', $plan->name ?? '') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ ui('slug') }}</label>
                    <input type="text" name="slug" id="plan-slug" class="form-control"
                           value="{{ old('slug', $plan->slug ?? '') }}"
                           placeholder="{{ ui('slug_auto') }}" {{ $plan && $plan->slug === 'free' ? 'readonly' : '' }}>
                    <div class="form-text">{{ ui('slug_help') }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ ui('price_monthly') }}</label>
                    <input type="number" step="0.01" min="0" name="price_monthly" class="form-control"
                           value="{{ old('price_monthly', $plan->price_monthly ?? 0) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ ui('billing_period') }}</label>
                    <select name="billing_period" class="form-select">
                        <option value="monthly" @selected(old('billing_period', $plan->billing_period ?? 'monthly') === 'monthly')>{{ ui('period_monthly') }}</option>
                        <option value="lifetime" @selected(old('billing_period', $plan->billing_period ?? 'monthly') === 'lifetime')>{{ ui('period_lifetime') }}</option>
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is_active"
                               {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">{{ ui('active') }}</label>
                    </div>
                </div>
            </div>

            {{-- Capacity --}}
            <h6 class="text-uppercase text-muted small fw-bold mb-3">{{ ui('capacity_limits') }}</h6>
            <div class="row g-3 mb-1">
                <div class="col-md-4 col-lg-2">
                    <label class="form-label">{{ ui('limit_max_members') }}</label>
                    <input type="number" min="0" name="max_members" class="form-control"
                           value="{{ old('max_members', $plan?->limit('max_members') ?? 0) }}">
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label">{{ ui('limit_max_storage_mb') }}</label>
                    <input type="number" min="0" name="max_storage_mb" class="form-control"
                           value="{{ old('max_storage_mb', $plan?->limit('max_storage_mb') ?? 0) }}">
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label">{{ ui('limit_max_roles') }}</label>
                    <input type="number" min="0" name="max_roles" class="form-control"
                           value="{{ old('max_roles', $plan?->limit('max_roles') ?? 0) }}">
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label">{{ ui('limit_max_permissions') }}</label>
                    <input type="number" min="0" name="max_permissions" class="form-control"
                           value="{{ old('max_permissions', $plan?->limit('max_permissions') ?? 0) }}">
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label">{{ ui('limit_max_features') }}</label>
                    <input type="number" min="0" name="max_features" id="max_features" class="form-control"
                           value="{{ old('max_features', $plan?->limit('max_features') ?? 0) }}">
                </div>
                <div class="col-md-4 col-lg-2 d-flex align-items-end">
                    <small class="text-muted">{{ ui('roles_auto_create') }}</small>
                </div>
            </div>

            {{-- Features --}}
            <h6 class="text-uppercase text-muted small fw-bold mt-4 mb-2 d-flex justify-content-between">
                <span>{{ ui('features') }}</span>
                <span class="badge bg-secondary" id="feature-counter">0</span>
            </h6>
            <div class="row row-cols-2 row-cols-md-3 g-2 mb-4" id="feature-list">
                @foreach(config('pennant.features') as $slug => $cfg)
                    @php($checked = in_array($slug, old('features', $plan->features ?? [])))
                    <div class="col">
                        <div class="form-check">
                            <input class="form-check-input feat-toggle" type="checkbox" name="features[]"
                                   value="{{ $slug }}" id="feat-{{ $slug }}" @checked($checked)
                                   data-feature="{{ $slug }}">
                            <label class="form-check-label" for="feat-{{ $slug }}">{{ $cfg['label'] ?? $slug }}</label>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Permissions (grouped by feature, filtered by enabled features) --}}
            <h6 class="text-uppercase text-muted small fw-bold mb-2">{{ ui('allowed_permissions') }}</h6>
            <div class="form-text mb-3">{{ ui('allowed_permissions_help') }}</div>
            <div class="accordion" id="perm-accordion">
                @php($grouped = $permissions->groupBy(fn ($p) => App\Models\Permission::featureOf($p)))
                @foreach(config('pennant.features') as $slug => $cfg)
                    <div class="accordion-item perm-group" data-feature="{{ $slug }}">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#perm-{{ $slug }}" aria-expanded="false">
                                {{ $cfg['label'] ?? $slug }}
                            </button>
                        </h2>
                        <div id="perm-{{ $slug }}" class="accordion-collapse collapse" data-bs-parent="#perm-accordion">
                            <div class="accordion-body row row-cols-2 row-cols-md-3 g-2">
                                @foreach($grouped->get($slug, []) as $perm)
                                    @php($checked = in_array($perm, old('allowed_permissions', $plan->limits['allowed_permissions'] ?? [])))
                                    <div class="col">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="allowed_permissions[]"
                                                   value="{{ $perm }}" id="perm-{{ Str::slug($perm) }}" @checked($checked)>
                                            <label class="form-check-label" for="perm-{{ Str::slug($perm) }}">{{ $perm }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                <button class="btn btn-primary">{{ ui('save') }}</button>
                <a href="{{ route('plans.index') }}" class="btn btn-light border">{{ ui('cancel') }}</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Slug auto-generates from name; user can override until they type in slug.
    (() => {
        const name = document.getElementById('plan-name');
        const slug = document.getElementById('plan-slug');
        if (name && slug && !slug.readOnly) {
            let touched = slug.value.trim() !== '';
            slug.addEventListener('input', () => { touched = true; });
            name.addEventListener('input', () => {
                if (!touched) slug.value = name.value.toLowerCase().trim()
                    .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            });
        }
    })();

    // Feature checkboxes: respect max_features (disable extras), update counter,
    // and toggle permission accordion groups by enabled feature.
    (() => {
        const maxInput = document.getElementById('max_features');
        const toggles = Array.from(document.querySelectorAll('.feat-toggle'));
        const counter = document.getElementById('feature-counter');
        const groups = Array.from(document.querySelectorAll('.perm-group'));

        const apply = () => {
            const on = toggles.filter(t => t.checked).map(t => t.dataset.feature);
            const max = parseInt(maxInput?.value || '0', 10);
            if (max > 0) {
                toggles.forEach(t => {
                    if (!t.checked && on.length >= max) t.disabled = true;
                    else t.disabled = false;
                });
            } else {
                toggles.forEach(t => t.disabled = false);
            }
            if (counter) counter.textContent = on.length + (max > 0 ? ' / ' + max : '');
            const set = new Set(on);
            groups.forEach(g => {
                const show = set.has(g.dataset.feature);
                g.style.display = show ? '' : 'none';
            });
        };
        toggles.forEach(t => t.addEventListener('change', apply));
        maxInput?.addEventListener('input', apply);
        apply();
    })();
</script>
@endpush
@endsection
