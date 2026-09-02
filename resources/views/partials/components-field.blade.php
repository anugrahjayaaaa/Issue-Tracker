@php
    $selected = isset($issue) ? $issue->components->pluck('id')->toArray() : [];
    $selected = old('components', $selected);
@endphp
<div class="mb-3">
    <label class="form-label">{{ ui('components') }}</label>
    @if ($project && $project->components->isNotEmpty())
    <div class="d-flex flex-wrap gap-2">
        @foreach ($project->components as $component)
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="components[]" value="{{ $component->id }}"
                id="component-{{ $component->id }}" {{ in_array($component->id, (array) $selected) ? 'checked' : '' }}>
            <label class="form-check-label" for="component-{{ $component->id }}">
                {{ $component->name }}
            </label>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-muted small">{{ ui('no_components') }}</div>
    @endif
</div>
