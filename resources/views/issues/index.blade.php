@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>{{ ui('issues') }}</h3>
    @can('issue.create')
    <a href="{{ route('issues.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> {{ ui('new_issue') }}</a>
    @endcan
</div>

<form method="GET" class="d-flex align-items-center gap-2 flex-wrap mb-3">
    <div class="col-md-4 ps-0">
        <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">{{ ui('select_project') }}</option>
            @foreach ($projects as $p)
                <option value="{{ $p->id }}" {{ $project && $project->id == $p->id ? 'selected' : '' }}>{{ $p->key }} - {{ $p->name }}</option>
            @endforeach
        </select>
    </div>
    @php
        $filters = [
            'status' => [App\Models\Issue::STATUS_OPEN, App\Models\Issue::STATUS_IN_PROGRESS, App\Models\Issue::STATUS_BLOCKED, App\Models\Issue::STATUS_DONE],
            'priority' => [App\Models\Issue::PRIORITY_LOW, App\Models\Issue::PRIORITY_MEDIUM, App\Models\Issue::PRIORITY_HIGH, App\Models\Issue::PRIORITY_URGENT],
        ];
    @endphp
    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" {{ $project ? '' : 'disabled' }}>
        <option value="">{{ ui('all_status') }}</option>
        @foreach ($filters['status'] as $s)
            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ui('issue_status_'.$s) }}</option>
        @endforeach
    </select>
    <select name="priority" class="form-select form-select-sm" onchange="this.form.submit()" {{ $project ? '' : 'disabled' }}>
        <option value="">{{ ui('all_priority') }}</option>
        @foreach ($filters['priority'] as $pr)
            <option value="{{ $pr }}" {{ request('priority') == $pr ? 'selected' : '' }}>{{ ui('issue_priority_'.$pr) }}</option>
        @endforeach
    </select>
    <select name="assignee_id" class="form-select form-select-sm" onchange="this.form.submit()" {{ $project ? '' : 'disabled' }}>
        <option value="">{{ ui('all_assignee') }}</option>
        @foreach (($project->users ?? collect()) as $u)
            <option value="{{ $u->id }}" {{ request('assignee_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
        @endforeach
    </select>
    <select name="label_id" class="form-select form-select-sm" onchange="this.form.submit()" {{ $project ? '' : 'disabled' }}>
        <option value="">{{ ui('all_labels') }}</option>
        @foreach (($project->labels ?? collect()) as $l)
            <option value="{{ $l->id }}" {{ request('label_id') == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>
        @endforeach
    </select>
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
                    <td>{{ ui('issue_type_'.$issue->type) }}</td>
                    <td><span class="badge text-bg-{{ $issue->status == 'done' ? 'success' : ($issue->status == 'blocked' ? 'danger' : 'warning') }}">{{ ui('issue_status_'.$issue->status) }}</span></td>
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
