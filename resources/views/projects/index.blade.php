@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>{{ ui('projects') }}</h3>
    @can('project.manage')
    <a href="{{ route('projects.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> {{ ui('new_project') }}</a>
    @endcan
</div>

<div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
    <form method="GET" class="d-flex flex-grow-1" style="max-width:420px">
        <div class="input-group input-group-sm shadow-sm w-100">
            <span class="input-group-text bg-body border-0"><i class="bi bi-search"></i></span>
            <input type="text" name="q" class="form-control bg-body border-0" placeholder="{{ ui('search_project') }}" value="{{ request('q') }}">
            <button class="btn btn-primary px-3" type="submit">{{ ui('search') }}</button>
        </div>
    </form>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:60px">#</th>
                    <th>{{ ui('project') }}</th>
                    <th>{{ ui('project_key') }}</th>
                    <th>{{ ui('owner') }}</th>
                    <th class="text-end">{{ ui('action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($projects as $project)
                <tr>
                    <td class="text-muted">{{ $projects->firstItem() + $loop->index }}</td>
                    <td>
                        <a href="{{ route('projects.show', $project) }}" class="fw-medium text-decoration-none">{{ $project->name }}</a>
                    </td>
                    <td><span class="badge text-bg-secondary">{{ $project->key }}</span></td>
                    <td>{{ $project->owner->name ?? '-' }}</td>
                    <td class="text-end">
                        <x-action-buttons
                            :view="route('projects.show', $project)"
                            :edit="auth()->user()->can('project.manage') ? route('projects.edit', $project) : null"
                            :delete="auth()->user()->can('project.manage') ? route('projects.destroy', $project) : null" />
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">{{ ui('no_projects_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
{{ $projects->links() }}
@include('partials.modals.delete-modal')
@endsection
