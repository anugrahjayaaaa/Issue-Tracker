@can('project.manage')
<div class="card shadow-sm mb-3">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-boxes text-secondary"></i> {{ ui('components') }}
        <i class="bi bi-info-circle text-muted" data-bs-toggle="tooltip" data-bs-title="{{ ui('component_hint') }}"></i>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('projects.components.store', $project) }}" class="mb-3">
            @csrf
            <div class="input-group input-group-sm">
                <input type="text" name="component_name" class="form-control @error('component_name') is-invalid @enderror" placeholder="{{ ui('component_name') }}">
                <button class="btn btn-primary" type="submit" data-bs-toggle="tooltip" data-bs-title="{{ ui('add') }}" aria-label="{{ ui('add') }}"><i class="bi bi-plus-lg"></i></button>
            </div>
            @error('component_name')<div class="invalid-feedback d-block small mt-1">{{ $message }}</div>@enderror
        </form>

        <ul class="list-group list-group-flush">
            @forelse ($project->components as $component)
            <li class="list-group-item border-0 px-3 py-2">
                <details class="component-row">
                    <summary class="d-flex justify-content-between align-items-center gap-2" style="cursor:pointer;list-style:none">
                        <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                            <span class="fw-medium text-truncate">{{ $component->name }}</span>
                            @if ($component->lead)
                            <small class="text-muted">{{ ui('role_lead') }}: {{ $component->lead->name }}</small>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            <i class="bi bi-pencil text-secondary" data-bs-toggle="tooltip" title="{{ ui('edit') }}"></i>
                            <button type="button" class="btn btn-sm btn-light border-0 text-danger p-1" data-bs-toggle="modal" data-bs-target="#deleteModal" data-action="{{ route('projects.components.destroy', [$project, $component]) }}" data-bs-title="{{ ui('remove') }}" aria-label="{{ ui('remove') }}"><i class="bi bi-trash"></i></button>
                        </div>
                    </summary>
                    <form method="POST" action="{{ route('projects.components.update', [$project, $component]) }}" class="d-flex gap-2 mt-2">
                        @csrf @method('PUT')
                        <input type="text" name="component_name" value="{{ $component->name }}" class="form-control form-control-sm flex-grow-1">
                        <button class="btn btn-sm btn-primary" type="submit" data-bs-toggle="tooltip" title="{{ ui('save') }}"><i class="bi bi-check-lg"></i></button>
                    </form>
                </details>
            </li>
            @empty
            <li class="list-group-item border-0 px-3 py-2 text-muted small">{{ ui('no_components') }}</li>
            @endforelse
        </ul>
    </div>
</div>
@endcan
