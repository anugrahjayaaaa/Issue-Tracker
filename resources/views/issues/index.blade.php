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
    @php
        $filters = [
            'status' => $project->statuses->pluck('name')->all(),
            'priority' => [App\Models\Issue::PRIORITY_LOW, App\Models\Issue::PRIORITY_MEDIUM, App\Models\Issue::PRIORITY_HIGH, App\Models\Issue::PRIORITY_URGENT],
        ];
        $statusMap = $project->statuses->pluck('color', 'name')->all();
        $typeMap = $project->issueTypes->pluck('color', 'name')->all();
    @endphp
    <div class="d-flex flex-wrap gap-2 align-items-end">
        <div>
            <label class="form-label small mb-1">{{ ui('all_status') }}</label>
            <select name="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                <option value="">{{ ui('all_status') }}</option>
                @foreach ($filters['status'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label small mb-1">{{ ui('all_priority') }}</label>
            <select name="priority" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                <option value="">{{ ui('all_priority') }}</option>
                @foreach ($filters['priority'] as $pr)
                    <option value="{{ $pr }}" {{ request('priority') == $pr ? 'selected' : '' }}>{{ ui('issue_priority_'.$pr) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label small mb-1">{{ ui('all_assignee') }}</label>
            <select name="assignee_id" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                <option value="">{{ ui('all_assignee') }}</option>
                @foreach ($project->users as $u)
                    <option value="{{ $u->id }}" {{ request('assignee_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label small mb-1">{{ ui('all_labels') }}</label>
            <select name="label_id" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                <option value="">{{ ui('all_labels') }}</option>
                @foreach ($project->labels as $l)
                    <option value="{{ $l->id }}" {{ request('label_id') == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>
                @endforeach
            </select>
        </div>
        @if (request()->anyFilled(['status','priority','assignee_id','label_id']))
            <a href="{{ route('issues.index', ['project_id' => $project->id]) }}" class="btn btn-sm btn-outline-secondary mb-1">{{ ui('reset') }}</a>
        @endif
    </div>
    @endif
</form>

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
                    <td><span class="badge" style="background:{{ $typeMap[$issue->type] ?? '#6c757d' }};color:#fff">{{ $issue->type }}</span></td>
                    <td><span class="badge" style="background:{{ $statusMap[$issue->status] ?? '#6c757d' }};color:#fff">{{ $issue->status }}</span></td>
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
