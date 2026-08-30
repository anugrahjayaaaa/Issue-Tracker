@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center">
        <a href="{{ route('projects.index') }}" class="btn btn-sm btn-light border rounded-2 me-2"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h3 class="mb-0"><span class="badge text-bg-secondary me-1">{{ $project->key }}</span> {{ $project->name }}</h3>
            <div class="text-muted small">{{ ui('owner') }}: {{ $project->owner->name ?? '-' }}</div>
        </div>
    </div>
    @can('project.manage')
    <a href="{{ route('projects.edit', $project) }}" class="btn btn-light border rounded-2"><i class="bi bi-pencil me-1"></i> {{ ui('edit') }}</a>
    @endcan
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header">{{ ui('description') }}</div>
            <div class="card-body">{{ $project->description ? nl2br(e($project->description)) : '-' }}</div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>{{ ui('members') }}</span>
            </div>
            <div class="card-body">
                @can('project.manage')
                <form method="POST" action="{{ route('projects.members.store', $project) }}" class="mb-3">
                    @csrf
                    <div class="row g-2">
                        <div class="col-7">
                            <select name="user_id" class="form-select form-select-sm @error('user_id') is-invalid @enderror">
                                <option value="">{{ ui('select_user') }}</option>
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-3">
                            <select name="role" class="form-select form-select-sm">
                                <option value="lead">{{ ui('role_lead') }}</option>
                                <option value="member" selected>{{ ui('role_member') }}</option>
                                <option value="viewer">{{ ui('role_viewer') }}</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <button class="btn btn-primary btn-sm w-100"><i class="bi bi-plus-lg"></i></button>
                        </div>
                    </div>
                    @error('user_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </form>
                @endcan

                <ul class="list-group list-group-flush">
                    @forelse ($project->members as $m)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            <div class="fw-medium">{{ $m->user->name ?? '-' }}</div>
                            <small class="text-muted">{{ $m->user->email ?? '' }}</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
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
                                <button class="btn btn-sm btn-light border rounded-2 text-danger"><i class="bi bi-trash"></i></button>
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
    </div>
</div>
@endsection
