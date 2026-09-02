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
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="{{ ui('component_name') }}">
                <button class="btn btn-primary" type="submit" data-bs-toggle="tooltip" data-bs-title="{{ ui('add') }}" aria-label="{{ ui('add') }}"><i class="bi bi-plus-lg"></i></button>
            </div>
            @error('name')<div class="invalid-feedback d-block small mt-1">{{ $message }}</div>@enderror
        </form>

        <ul class="list-group list-group-flush">
            @forelse ($project->components as $component)
            <li class="list-group-item border-0 d-flex justify-content-between align-items-center px-3 py-2">
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-medium text-truncate">{{ $component->name }}</div>
                    @if ($component->lead)
                    <small class="text-muted">{{ ui('role_lead') }}: {{ $component->lead->name }}</small>
                    @endif
                </div>
                @can('project.manage')
                <form method="POST" action="{{ route('projects.components.destroy', [$project, $component]) }}" class="d-inline ms-2" onsubmit="return confirm('{{ ui('confirm_delete') }}')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-light border-0 text-danger p-1" type="submit" data-bs-toggle="tooltip" data-bs-title="{{ ui('remove') }}" aria-label="{{ ui('remove') }}"><i class="bi bi-trash"></i></button>
                </form>
                @endcan
            </li>
            @empty
            <li class="list-group-item border-0 px-3 py-2 text-muted small">{{ ui('no_components') }}</li>
            @endforelse
        </ul>
    </div>
</div>
@endcan
