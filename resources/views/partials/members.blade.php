<div class="card shadow-sm mb-3">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-people text-secondary"></i> {{ ui('members') }} <span class="badge rounded-pill text-bg-secondary ms-1">{{ $project->members->count() }}</span>
    </div>
    <div class="card-body">
        @can('project.manage')
        <form method="POST" action="{{ route('projects.members.store', $project) }}" class="mb-3">
            @csrf
            <div class="input-group input-group-sm mb-2">
                <select name="user_id" class="form-select @error('user_id') is-invalid @enderror">
                    <option value="">{{ ui('select_user') }}</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                    @endforeach
                </select>
                <button class="btn btn-primary" type="submit" data-bs-toggle="tooltip" data-bs-title="{{ ui('add') }}" aria-label="{{ ui('add') }}"><i class="bi bi-plus-lg"></i></button>
            </div>
            <select name="role" class="form-select form-select-sm" aria-label="{{ ui('role') }}">
                <option value="lead">{{ ui('role_lead') }}</option>
                <option value="member" selected>{{ ui('role_member') }}</option>
                <option value="viewer">{{ ui('role_viewer') }}</option>
            </select>
            @error('user_id')<div class="invalid-feedback d-block small mt-1">{{ $message }}</div>@enderror
        </form>
        @endcan

        <ul class="list-group list-group-flush">
            @forelse ($project->members as $m)
            <li class="list-group-item border-0 d-flex justify-content-between align-items-center gap-2 px-3 py-2">
                <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                    <span class="avatar avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:28px;height:28px;font-size:.7rem">{{ strtoupper(substr($m->user->name ?? '?', 0, 1)) }}</span>
                    <div class="min-w-0">
                        <div class="fw-medium text-truncate">{{ $m->user->name ?? '-' }}</div>
                        <small class="text-secondary text-truncate d-block">{{ $m->user->email ?? '' }}</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    @can('project.manage')
                    <form method="POST" action="{{ route('projects.members.update', [$project, $m]) }}" class="d-inline">
                        @csrf @method('PUT')
                        <select name="role" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                            <option value="lead" {{ $m->role == 'lead' ? 'selected' : '' }}>{{ ui('role_lead') }}</option>
                            <option value="member" {{ $m->role == 'member' ? 'selected' : '' }}>{{ ui('role_member') }}</option>
                            <option value="viewer" {{ $m->role == 'viewer' ? 'selected' : '' }}>{{ ui('role_viewer') }}</option>
                        </select>
                    </form>
                    <form method="POST" action="{{ route('projects.members.destroy', [$project, $m]) }}" class="d-inline" onsubmit="return confirm('{{ ui('confirm_remove_member') }}')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-light border-0 text-danger p-1" type="submit" data-bs-toggle="tooltip" data-bs-title="{{ ui('remove') }}" aria-label="{{ ui('remove') }}"><i class="bi bi-trash"></i></button>
                    </form>
                    @else
                    <span class="badge text-bg-info">{{ ui('role_'.$m->role) }}</span>
                    @endcan
                </div>
            </li>
            @empty
            <li class="list-group-item border-0 px-0 text-muted">{{ ui('no_members') }}</li>
            @endforelse
        </ul>
    </div>
</div>
