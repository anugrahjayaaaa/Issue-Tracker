@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>{{ ui('issues') }}</h3>
    @can('issue.create')
    <a href="{{ route('issues.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> {{ ui('new_issue') }}</a>
    @endcan
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
        <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">{{ ui('select_project') }}</option>
            @foreach ($projects as $p)
                <option value="{{ $p->id }}" {{ $project && $project->id == $p->id ? 'selected' : '' }}>{{ $p->key }} - {{ $p->name }}</option>
            @endforeach
        </select>
    </div>
    @if ($project)
    <div class="col-md-2"><select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">{{ ui('all_status') }}</option>
        @foreach ([App\Models\Issue::STATUS_OPEN, App\Models\Issue::STATUS_IN_PROGRESS, App\Models\Issue::STATUS_BLOCKED, App\Models\Issue::STATUS_DONE] as $s)
            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ui('issue_status_'.$s) }}</option>
        @endforeach
    </select></div>
    <div class="col-md-2"><select name="priority" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">{{ ui('all_priority') }}</option>
        @foreach ([App\Models\Issue::PRIORITY_LOW, App\Models\Issue::PRIORITY_MEDIUM, App\Models\Issue::PRIORITY_HIGH, App\Models\Issue::PRIORITY_URGENT] as $pr)
            <option value="{{ $pr }}" {{ request('priority') == $pr ? 'selected' : '' }}>{{ ui('issue_priority_'.$pr) }}</option>
        @endforeach
    </select></div>
    <div class="col-md-3"><select name="assignee_id" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">{{ ui('all_assignee') }}</option>
        @foreach ($project->users as $u)
            <option value="{{ $u->id }}" {{ request('assignee_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
        @endforeach
    </select></div>
    <div class="col-md-2"><select name="label_id" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">{{ ui('all_labels') }}</option>
        @foreach ($project->labels as $l)
            <option value="{{ $l->id }}" {{ request('label_id') == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>
        @endforeach
    </select></div>
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
                <th>{{ ui('issue_code') }}</th><th>{{ ui('title') }}</th>
                <th>{{ ui('type') }}</th><th>{{ ui('status') }}</th><th>{{ ui('priority') }}</th>
                <th>{{ ui('assignee') }}</th><th class="text-end">{{ ui('action') }}</th>
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
                    <td class="text-end">
                        <x-action-buttons
                            :edit="auth()->user()->can('issue.edit') ? route('issues.edit', $issue) : null"
                            :delete="auth()->user()->can('issue.delete') ? route('issues.destroy', $issue) : null" />
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">{{ ui('no_issues_found') }}</td></tr>
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
