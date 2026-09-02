@php
    $rule ??= null;
    $events = ['issue:status_changed', 'issue:created', 'issue:assigned'];
@endphp

<div class="mb-3">
    <label class="form-label">{{ ui('name') }}</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $rule->name ?? '') }}" required>
</div>

<div class="mb-3">
    <label class="form-label">{{ ui('trigger') }}</label>
    <select name="event" class="form-select" required>
        @foreach($events as $e)
            <option value="{{ $e }}" {{ (old('event', $rule->event ?? '') === $e) ? 'selected' : '' }}>{{ $e }}</option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label class="form-label">{{ ui('conditions') }} <small class="text-muted">({{ ui('conditions_help') ?? 'JSON array' }})</small></label>
    <textarea name="conditions" class="form-control" rows="2">{{ old('conditions', json_encode($rule->conditions ?? [])) }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label">{{ ui('actions') }}</label>
    <textarea name="actions" class="form-control" rows="2" required>{{ old('actions', json_encode($rule->actions ?? [])) }}</textarea>
</div>

<div class="mb-3 form-check">
    <input type="checkbox" name="enabled" value="1" class="form-check-input" {{ (old('enabled', $rule->enabled ?? true)) ? 'checked' : '' }}>
    <label class="form-check-label">{{ ui('enabled') }}</label>
</div>
