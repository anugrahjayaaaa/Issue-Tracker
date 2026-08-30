@can('project.manage')
<div class="card shadow-sm mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>{{ ui('labels') }}</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('projects.labels.store', $project) }}" class="mb-3">
            @csrf
            <div class="row g-2">
                <div class="col-5">
                    <input type="text" name="name" class="form-control form-control-sm @error('name') is-invalid @enderror" placeholder="{{ ui('label_name') }}">
                </div>
                <div class="col-3">
                    <input type="color" name="color" class="form-control form-control-sm form-control-color" value="#3b82f6">
                </div>
                <div class="col-2">
                    <button class="btn btn-primary btn-sm w-100" title="{{ ui('add') }}"><i class="bi bi-plus-lg"></i></button>
                </div>
            </div>
            @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </form>

        <ul class="list-group list-group-flush">
            @forelse ($project->labels as $label)
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge" style="background:{{ $label->color }}">&nbsp;</span>
                    <span>{{ $label->name }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <form method="POST" action="{{ route('projects.labels.update', [$project, $label]) }}" class="d-inline">
                        @csrf @method('PUT')
                        <div class="row g-1 align-items-center">
                            <div class="col-auto"><input type="text" name="name" value="{{ $label->name }}" class="form-control form-control-sm" style="width:120px"></div>
                            <div class="col-auto"><input type="color" name="color" value="{{ $label->color }}" class="form-control form-control-sm form-control-color"></div>
                            <div class="col-auto"><button class="btn btn-sm btn-light border rounded-2" title="{{ ui('save') }}"><i class="bi bi-check-lg"></i></button></div>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('projects.labels.destroy', [$project, $label]) }}" class="d-inline" onsubmit="return confirm('{{ ui('confirm_delete_label') }}')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-light border rounded-2 text-danger"><i class="bi bi-trash"></i></button>
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
