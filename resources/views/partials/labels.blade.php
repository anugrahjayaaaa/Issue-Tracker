@can('project.manage')
<div class="card shadow-sm mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="d-flex align-items-center gap-2">
            <i class="bi bi-tags text-secondary"></i> {{ ui('labels') }}
            <i class="bi bi-info-circle text-muted" data-bs-toggle="tooltip" data-bs-title="{{ ui('labels_hint') }}"></i>
        </span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('projects.labels.store', $project) }}" class="mb-3">
            @csrf
            <div class="row g-2">
                <div class="col-6">
                    <input type="text" name="name" class="form-control form-control-sm @error('name') is-invalid @enderror" placeholder="{{ ui('label_name') }}">
                </div>
                <div class="col-4">
                    <input type="color" name="color" class="form-control form-control-sm form-control-color" value="#3b82f6" data-bs-toggle="tooltip" data-bs-title="{{ ui('color') }}">
                </div>
                <div class="col-2">
                    <button class="btn btn-primary btn-sm w-100" type="submit" data-bs-toggle="tooltip" data-bs-title="{{ ui('add') }}" aria-label="{{ ui('add') }}"><i class="bi bi-plus-lg"></i></button>
                </div>
            </div>
            @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </form>

        <ul class="list-group list-group-flush">
            @forelse ($project->labels as $label)
            <li class="list-group-item d-flex justify-content-between align-items-center gap-2 px-0 py-2">
                <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                    <span class="badge flex-shrink-0" style="background:{{ $label->color }};width:14px;height:14px;padding:0;border-radius:4px">&nbsp;</span>
                    <span class="text-truncate">{{ $label->name }}</span>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <form method="POST" action="{{ route('projects.labels.update', [$project, $label]) }}" class="d-inline">
                        @csrf @method('PUT')
                        <div class="row g-1 align-items-center flex-nowrap">
                            <div class="col-auto"><input type="text" name="name" value="{{ $label->name }}" class="form-control form-control-sm" style="width:96px"></div>
                            <div class="col-auto"><input type="color" name="color" value="{{ $label->color }}" class="form-control form-control-sm form-control-color"></div>
                            <div class="col-auto">
                                <button class="btn btn-sm btn-light border rounded-2" type="submit" data-bs-toggle="tooltip" data-bs-title="{{ ui('save') }}" aria-label="{{ ui('save') }}"><i class="bi bi-check-lg"></i></button>
                            </div>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('projects.labels.destroy', [$project, $label]) }}" class="d-inline" onsubmit="return confirm('{{ ui('confirm_delete_label') }}')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-light border rounded-2 text-danger" type="submit" data-bs-toggle="tooltip" data-bs-title="{{ ui('remove') }}" aria-label="{{ ui('remove') }}"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </li>
            @empty
            <li class="list-group-item px-0 text-muted">{{ ui('no_labels') }}</li>
            @endforelse
        </ul>
    </div>
</div>
@endcan
