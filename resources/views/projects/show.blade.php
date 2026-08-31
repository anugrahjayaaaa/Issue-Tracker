@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center">
        <a href="{{ route('projects.index') }}" class="btn btn-sm btn-light border rounded-2 me-3" data-bs-toggle="tooltip" data-bs-title="{{ ui('back') }}" aria-label="{{ ui('back') }}"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h3 class="mb-1"><span class="badge text-bg-secondary me-1">{{ $project->key }}</span> {{ $project->name }}</h3>
            <div class="text-muted small d-flex align-items-center gap-2">
                <span class="avatar avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:22px;height:22px;font-size:.7rem">{{ strtoupper(substr($project->owner->name ?? '?', 0, 1)) }}</span>
                {{ $project->owner->name ?? '-' }}
                <span class="text-secondary">·</span>
                {{ ui('created') }}: {{ $project->created_at->format('d M Y') }}
            </div>
        </div>
    </div>
    @can('project.manage')
    <a href="{{ route('projects.edit', $project) }}" class="btn btn-light border rounded-2"><i class="bi bi-pencil me-1"></i> {{ ui('edit') }}</a>
    @endcan
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex align-items-center gap-2"><i class="bi bi-card-text text-secondary"></i> {{ ui('description') }}</div>
            <div class="card-body">{{ $project->description ? nl2br(e($project->description)) : '-' }}</div>
        </div>

        @if ($project->issues->isNotEmpty())
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="d-flex align-items-center gap-2"><i class="bi bi-list-task text-secondary"></i> {{ ui('issues') }}</span>
                <a href="{{ route('issues.index', ['project_id' => $project->id]) }}" class="small text-decoration-none">{{ ui('view_all') }}</a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach ($project->issues->take(5) as $issue)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-3">
                        <a href="{{ route('issues.show', $issue) }}" class="text-decoration-none">
                            <span class="badge text-bg-secondary me-1">{{ $issue->code }}</span>{{ $issue->title }}
                        </a>
                        <span class="badge bg-{{ match($issue->status) { 'open' => 'primary', 'in_progress' => 'info', 'blocked' => 'danger', 'done' => 'success', default => 'secondary' } }}-subtle text-{{ match($issue->status) { 'open' => 'primary', 'in_progress' => 'info', 'blocked' => 'danger', 'done' => 'success', default => 'secondary' } }} border border-{{ match($issue->status) { 'open' => 'primary', 'in_progress' => 'info', 'blocked' => 'danger', 'done' => 'success', default => 'secondary' } }}-subtle">{{ ui('issue_status_'.$issue->status) }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="d-flex align-items-center gap-2"><i class="bi bi-people text-secondary"></i> {{ ui('members') }} <span class="badge rounded-pill text-bg-secondary ms-1">{{ $project->members->count() }}</span></span>
            </div>
            <div class="card-body">
                @can('project.manage')
                <form method="POST" action="{{ route('projects.members.store', $project) }}" class="mb-3">
                    @csrf
                    <div class="row g-2">
                        <div class="col-6">
                            <select name="user_id" class="form-select form-select-sm @error('user_id') is-invalid @enderror">
                                <option value="">{{ ui('select_user') }}</option>
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <select name="role" class="form-select form-select-sm">
                                <option value="lead">{{ ui('role_lead') }}</option>
                                <option value="member" selected>{{ ui('role_member') }}</option>
                                <option value="viewer">{{ ui('role_viewer') }}</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <button class="btn btn-primary btn-sm w-100" type="submit" data-bs-toggle="tooltip" data-bs-title="{{ ui('add') }}" aria-label="{{ ui('add') }}"><i class="bi bi-plus-lg"></i></button>
                        </div>
                    </div>
                    @error('user_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </form>
                @endcan

                <ul class="list-group list-group-flush">
                    @forelse ($project->members as $m)
                    <li class="list-group-item d-flex justify-content-between align-items-center gap-2 px-0 py-2">
                        <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                            <span class="avatar avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:30px;height:30px">{{ strtoupper(substr($m->user->name ?? '?', 0, 1)) }}</span>
                            <div class="min-w-0">
                                <div class="fw-medium text-truncate">{{ $m->user->name ?? '-' }}</div>
                                <small class="text-muted text-truncate d-block">{{ $m->user->email ?? '' }}</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            @can('project.manage')
                            <form method="POST" action="{{ route('projects.members.update', [$project, $m]) }}" class="d-inline">
                                @csrf @method('PUT')
                                <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="lead" {{ $m->role == 'lead' ? 'selected' : '' }}>{{ ui('role_lead') }}</option>
                                    <option value="member" {{ $m->role == 'member' ? 'selected' : '' }}>{{ ui('role_member') }}</option>
                                    <option value="viewer" {{ $m->role == 'viewer' ? 'selected' : '' }}>{{ ui('role_viewer') }}</option>
                                </select>
                            </form>
                            <form method="POST" action="{{ route('projects.members.destroy', [$project, $m]) }}" class="d-inline" onsubmit="return confirm('{{ ui('confirm_remove_member') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light border rounded-2 text-danger" type="submit" data-bs-toggle="tooltip" data-bs-title="{{ ui('remove') }}" aria-label="{{ ui('remove') }}"><i class="bi bi-trash"></i></button>
                            </form>
                            @else
                            <span class="badge text-bg-info">{{ ui('role_'.$m->role) }}</span>
                            @endcan
                        </div>
                    </li>
                    @empty
                    <li class="list-group-item px-0 text-muted">{{ ui('no_members') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>

        @include('partials.labels')
    </div>
</div>
@endsection
