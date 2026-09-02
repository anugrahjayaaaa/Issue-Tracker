@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('projects.index') }}" class="btn btn-sm btn-light border rounded-2" data-bs-toggle="tooltip" data-bs-title="{{ ui('back') }}" aria-label="{{ ui('back') }}"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h3 class="mb-0 d-flex align-items-center gap-2">{{ $project->name }} <span class="badge text-bg-secondary fs-6">{{ $project->key }}</span></h3>
            <div class="text-muted small mt-0">{{ ui('owner') }}: {{ $project->owner->name ?? '-' }} <span class="text-secondary">·</span> {{ ui('created') }}: {{ $project->created_at->format('d M Y') }}</div>
            @if ($project->slug)
            <div class="text-muted small mt-0"><i class="bi bi-link-45deg"></i> {{ $project->slug }}</div>
            @endif
        </div>
    </div>
    @can('project.manage')
    <a href="{{ route('projects.edit', $project) }}" class="btn btn-light border rounded-2 align-self-center"><i class="bi bi-pencil me-1"></i> {{ ui('edit') }}</a>
    @endcan
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex align-items-center gap-2"><i class="bi bi-card-text text-secondary"></i> {{ ui('description') }}</div>
            <div class="card-body rich-text">{!! $project->description ?: '-' !!}</div>
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
                        <span class="badge" style="background:{{ $project->statuses->firstWhere('key', $issue->status)?->color ?? '#6c757d' }};color:#fff">{{ $issue->statusName() }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
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
                    @error('user_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
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

        @include('partials.labels')
        @include('partials.components')

        @can('project.manage')
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-gear text-secondary"></i>
                <span>{{ ui('automation') }}</span>
            </div>
            <div class="card-body py-3">
                @if($project->automationRules?->isEmpty())
                    <p class="text-muted small mb-0">{{ ui('no_automation_rules') }}</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($project->automationRules as $rule)
                            <li class="list-group-item border-0 d-flex justify-content-between align-items-center py-2">
                                <div>
                                    <span class="fw-medium">{{ $rule->name }}</span>
                                    <small class="text-muted ms-2">({{ $rule->event }})</small>
                                </div>
                                <a href="{{ route('projects.automation-rules.show', [$project, $rule]) }}" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <a href="{{ route('projects.automation-rules.index', $project) }}" class="btn btn-sm btn-primary mt-2 w-100"><i class="bi bi-list"></i> {{ ui('manage') }}</a>
                <a href="{{ route('projects.automation-rules.create', $project) }}" class="btn btn-sm btn-light mt-2 w-100"><i class="bi bi-plus"></i> {{ ui('create_rule') }}</a>
            </div>
        </div>
        @endcan
    </div>
</div>
@endsection
