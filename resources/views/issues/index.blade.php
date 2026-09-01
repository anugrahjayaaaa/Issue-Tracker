@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>{{ ui('issues') }}</h3>
    @can('issue.create')
    <a href="{{ route('issues.create', ['project_id' => $project?->id]) }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> {{ ui('new_issue') }}</a>
    @endcan
</div>

<form method="GET" class="card card-body shadow-sm mb-3">
    <div class="mb-2">
        <label class="form-label small mb-1">{{ ui('select_project') }}</label>
        <select name="project_id" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
            <option value="">{{ ui('select_project') }}</option>
            @foreach ($projects as $p)
                <option value="{{ $p->id }}" {{ $project && $project->id == $p->id ? 'selected' : '' }}>{{ $p->key }} - {{ $p->name }}</option>
            @endforeach
        </select>
    </div>
    @if ($project)
    <div class="d-flex flex-wrap gap-2 align-items-end">
        <div>
            <label class="form-label small mb-1">{{ ui('select_project') }}</label>
            <select name="project_id" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                <option value="">{{ ui('select_project') }}</option>
                @foreach ($projects as $p)
                    <option value="{{ $p->id }}" {{ $project && $project->id == $p->id ? 'selected' : '' }}>{{ $p->key }} - {{ $p->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="vr h-100 mx-1 d-none d-sm-block"></div>

        <div>
            <label class="form-label small mb-1">{{ __('Search') }}</label>
            <input type="search" name="q" class="form-control form-control-sm" style="width:220px" value="{{ request('q') }}" placeholder="{{ __('Search issues...') }}" id="issue-search-input">
            <div class="form-text small">Press <kbd>Cmd</kbd>+<kbd>K</kbd> / <kbd>Ctrl</kbd>+<kbd>K</kbd></div>
        </div>

        <div id="saved-filters-toolbar" class="d-flex flex-column gap-1">
            <label class="form-label small mb-0">{{ ui('saved_filters') ?? 'Saved filters' }}</label>
            <div class="input-group input-group-sm">
                <select id="saved-filter-select" class="form-select">
                    <option value="">{{ ui('select_saved_filter') ?? 'Select filter...' }}</option>
                </select>
                <button id="apply-saved-filter" class="btn btn-outline-secondary" type="button">{{ __('Apply') }}</button>
                <button id="save-current-filter" class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#saveFilterModal">{{ __('Save') }}</button>
            </div>
        </div>

        @if (request()->anyFilled(['q','status','priority','assignee_id','label_id']))
            <a href="{{ route('issues.index', ['project_id' => $project->id]) }}" class="btn btn-sm btn-outline-secondary mb-1">{{ ui('reset') }}</a>
        @endif
    </div>

    <div class="modal fade" id="saveFilterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Save current filter') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form id="save-filter-form" method="POST" action="{{ route('projects.saved-filters.store', $project) }}">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Name') }}</label>
                            <input type="text" name="name" class="form-control" required maxlength="100">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" name="is_public" id="filterIsPublic">
                            <label class="form-check-label" for="filterIsPublic">{{ __('Public') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</form>

@if (!$project)
<div class="alert alert-info">{{ ui('pick_project_first') }}</div>
@else
<script>
document.addEventListener('keydown', function(e) {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        var el = document.getElementById('issue-search-input');
        if (el) { el.focus(); el.select(); }
    }
});

(function() {
    var projectId = @json($project->id);
    var savedFilterUrl = '/projects/' + projectId + '/saved-filters';
    var select = document.getElementById('saved-filter-select');
    var applyBtn = document.getElementById('apply-saved-filter');

    function loadSavedFilters() {
        fetch(savedFilterUrl, { headers: { 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(items) {
                items.forEach(function(item) {
                    var opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = (item.is_public ? '[public] ' : '') + item.name;
                    opt.dataset.params = JSON.stringify(item.filter_params || {});
                    select.appendChild(opt);
                });
            })
            .catch(function() {});
    }

    if (select && applyBtn) {
        loadSavedFilters();
        applyBtn.addEventListener('click', function() {
            var opt = select.options[select.selectedIndex];
            if (!opt || !opt.dataset.params) { return; }
            var params = JSON.parse(opt.dataset.params);
            Object.keys(params).forEach(function(k) {
                if (params[k] === null || params[k] === undefined || params[k] === '') { return; }
                var existing = document.querySelector('[name="' + k + '"]');
                if (existing) { existing.value = params[k]; }
            });
            document.querySelector('form').submit();
        });
    }
})();
</script>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle m-0">
            <thead><tr>
                <x-sortable-th label="{{ ui('issue_code') }}" column="code" :sort="request('sort')" :dir="request('dir', 'asc')" />
                <x-sortable-th label="{{ ui('title') }}" column="title" :sort="request('sort')" :dir="request('dir', 'asc')" />
                <x-sortable-th label="{{ ui('type') }}" column="type" :sort="request('sort')" :dir="request('dir', 'asc')" />
                <x-sortable-th label="{{ ui('status') }}" column="status" :sort="request('sort')" :dir="request('dir', 'asc')" />
                <x-sortable-th label="{{ ui('priority') }}" column="priority" :sort="request('sort')" :dir="request('dir', 'asc')" />
                <x-sortable-th label="{{ ui('assignee') }}" column="assignee_id" :sort="request('sort')" :dir="request('dir', 'asc')" />
                <th>{{ ui('labels') }}</th>
                <x-sortable-th label="{{ ui('due_date') }}" column="due_date" :sort="request('sort')" :dir="request('dir', 'asc')" />
                <th class="text-end">{{ ui('action') }}</th>
            </tr></thead>
            <tbody>
                @forelse ($issues as $issue)
                <tr>
                    <td><span class="badge text-bg-secondary">{{ $issue->code }}</span></td>
                    <td><a href="{{ route('issues.show', $issue) }}" class="text-decoration-none">{{ $issue->title }}</a></td>
                    <td><x-issue-badge :issue="$issue" field="type" /></td>
                    <td><x-issue-badge :issue="$issue" field="status" /></td>
                    <td>{{ ui('issue_priority_'.$issue->priority) }}</td>
                    <td>{{ $issue->assignee->name ?? '-' }}</td>
                    <td>
                        @foreach ($issue->labels as $l)
                            <span class="badge" style="background:{{ $l->color }}">{{ $l->name }}</span>
                        @endforeach
                    </td>
                    <td>{{ $issue->due_date ? $issue->due_date->format('Y-m-d') : '-' }}</td>
                    <td class="text-end">
                        <x-action-buttons
                            :edit="auth()->user()->can('issue.edit') ? route('issues.edit', $issue) : null"
                            :delete="auth()->user()->can('issue.delete') ? route('issues.destroy', $issue) : null" />
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">{{ ui('no_issues_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
{{ $issues->links() }}
@endif
@include('partials.modals.delete-modal')
@endsection
