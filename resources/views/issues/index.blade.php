@extends('layouts.app')
@section('content')
@include('partials.flash-message')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>{{ ui('issues') }}</h3>
    @can('issue.create')
    <a href="{{ route('issues.create', ['project_id' => $project?->id]) }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> {{ ui('new_issue') }}
    </a>
    @endcan
</div>

<form method="GET" class="card card-body shadow-sm mb-3">
    {{-- Project selector --}}
    <div class="mb-2">
        <label class="form-label small mb-1">{{ ui('select_project') }}</label>
        <select name="project_id" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
            <option value="">{{ ui('select_project') }}</option>
            @foreach ($projects as $p)
                <option value="{{ $p->id }}" {{ $project && $project->id == $p->id ? 'selected' : '' }}>
                    {{ $p->key }} - {{ $p->name }}
                </option>
            @endforeach
        </select>
    </div>

    @if ($project)
    {{-- Toolbar: search left, filters right --}}
    <div class="d-flex align-items-end flex-wrap gap-2">
        {{-- Search --}}
        <div class="flex-grow-1" style="max-width: 280px;">
            <label class="form-label small mb-1">{{ ui('search') }}</label>
            <div class="position-relative">
                <input type="search" name="q" class="form-control form-control-sm ps-5"
                    value="{{ request('q') }}" placeholder="{{ ui('search_issues') }}"
                    id="issue-search-input" aria-label="{{ ui('search') }}">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted"></i>
            </div>
            <div class="form-text small">{{ ui('kbd_cmd_k') }}</div>
        </div>

        <div class="vr h-100 mx-1 d-none d-sm-block"></div>

        {{-- Filter: status --}}
        <div>
            <label class="form-label small mb-1">{{ ui('status') }}</label>
            <select name="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                <option value="">{{ ui('all_status') }}</option>
                @foreach ($filters['status'] as $st)
                    <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>
                        {{ ui('issue_status_' . $st) ?? $st }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Filter: priority --}}
        <div>
            <label class="form-label small mb-1">{{ ui('priority') }}</label>
            <select name="priority" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                <option value="">{{ ui('all_priority') }}</option>
                @foreach ($filters['priority'] as $pr)
                    <option value="{{ $pr }}" {{ request('priority') == $pr ? 'selected' : '' }}>
                        {{ ui('issue_priority_' . $pr) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Filter: component --}}
        @if ($project->components->isNotEmpty())
        <div>
            <label class="form-label small mb-1">{{ ui('component') }}</label>
            <select name="component_id" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                <option value="all">{{ ui('all_components') }}</option>
                @foreach ($project->components as $c)
                    <option value="{{ $c->id }}" {{ request('component_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Saved filters --}}
        <div id="saved-filters-toolbar" class="d-flex flex-column gap-1">
            <label class="form-label small mb-0">{{ ui('saved_filters') }}</label>
            <div class="input-group input-group-sm">
                <select id="saved-filter-select" class="form-select">
                    <option value="">{{ ui('select_saved_filter') }}</option>
                </select>
                <button id="apply-saved-filter" class="btn btn-outline-secondary" type="button">{{ ui('apply') }}</button>
                <button id="save-current-filter" class="btn btn-primary" type="button"
                    data-bs-toggle="modal" data-bs-target="#saveFilterModal">{{ ui('save') }}</button>
                <button id="delete-saved-filter" class="btn btn-outline-danger" type="button">{{ ui('delete') }}</button>
            </div>
        </div>

        {{-- Reset --}}
        @if (request()->anyFilled(['q','status','priority','assignee_id','label_id','component_id']))
        <a href="{{ route('issues.index', ['project_id' => $project->id]) }}"
            class="btn btn-sm btn-outline-secondary mb-1">{{ ui('reset') }}</a>
        @endif
    </div>

    {{-- Save filter modal --}}
    <div class="modal fade" id="saveFilterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ ui('save_filter') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ ui('close') }}"></button>
                </div>
                <form id="save-filter-form" method="POST" action="{{ route('projects.saved-filters.store', $project) }}">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                        <div class="mb-3">
                            <label class="form-label">{{ ui('name') }}</label>
                            <input type="text" name="name" class="form-control" required maxlength="100">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1"
                                name="is_public" id="filterIsPublic">
                            <label class="form-check-label" for="filterIsPublic">{{ ui('public') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ ui('cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ ui('save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</form>

@push('scripts')
<script>
document.addEventListener('keydown', (e) => {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        const el = document.getElementById('issue-search-input');
        if (el) { el.focus(); el.select(); }
    }
});

(function() {
    const projectId = @json($project->id ?? null);
    if (!projectId) return;
    const savedFilterUrl = '/projects/' + projectId + '/saved-filters';
    const select = document.getElementById('saved-filter-select');
    const applyBtn = document.getElementById('apply-saved-filter');
    const deleteBtn = document.getElementById('delete-saved-filter');

    function loadSavedFilters() {
        fetch(savedFilterUrl, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(items => {
                select.innerHTML = '<option value="">{{ ui('select_saved_filter') }}</option>';
                items.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = (item.is_public ? '[public] ' : '') + item.name;
                    opt.dataset.params = JSON.stringify(item.filter_params || {});
                    opt.dataset.ownerId = item.user_id;
                    select.appendChild(opt);
                });
            })
            .catch(() => {});
    }

    function deleteSelectedFilter() {
        const opt = select.options[select.selectedIndex];
        if (!opt || !opt.value) return;
        if (!confirm('{{ ui('confirm_delete_filter') }}')) return;
        fetch(savedFilterUrl + '/' + opt.value, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
            }
        }).then(r => {
            if (r.status === 204) loadSavedFilters();
            else if (r.status === 403) alert('{{ ui('not_allowed') }}');
        }).catch(() => {});
    }

    if (select && applyBtn && deleteBtn) {
        loadSavedFilters();
        applyBtn.addEventListener('click', () => {
            const opt = select.options[select.selectedIndex];
            if (!opt || !opt.dataset.params) return;
            const params = JSON.parse(opt.dataset.params);
            Object.keys(params).forEach(k => {
                const v = params[k];
                if (v === null || v === undefined || v === '') return;
                const existing = document.querySelector('[name="' + k + '"]');
                if (existing) existing.value = v;
            });
            document.querySelector('form').submit();
        });
        deleteBtn.addEventListener('click', deleteSelectedFilter);
    }
})();
</script>
@endpush

@if (!$project)
<div class="alert alert-info">{{ ui('pick_project_first') }}</div>
@else
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
@include('partials.pagination-info', ['items' => $issues])
{{ $issues->links() }}
@endif

@include('partials.modals.delete-modal')
@endsection
