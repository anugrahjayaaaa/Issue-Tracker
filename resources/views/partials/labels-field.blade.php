@php
    $selected = isset($issue) ? $issue->labels->pluck('id')->toArray() : [];
    $selected = old('labels', $selected);
@endphp
<div class="mb-3">
    <label class="form-label">{{ ui('labels') }}</label>
    @if ($project && $project->labels->isNotEmpty())
    <div class="d-flex flex-wrap gap-2">
        @foreach ($project->labels as $label)
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="labels[]" value="{{ $label->id }}"
                id="label-{{ $label->id }}" {{ in_array($label->id, (array) $selected) ? 'checked' : '' }}>
            <label class="form-check-label" for="label-{{ $label->id }}">
                <span class="badge" style="background:{{ $label->color }}">{{ $label->name }}</span>
            </label>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-muted small">{{ ui('no_labels_hint') }}</div>
    @endif
</div>
