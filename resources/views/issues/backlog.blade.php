@extends('layouts.app')
@section('content')
@include('partials.flash-message')

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('issues.board') }}" class="btn btn-sm btn-light border rounded-2"><i class="bi bi-kanban"></i></a>
        <h3 class="mb-0">{{ ui('backlog') }}</h3>
    </div>

    <div class="d-flex align-items-center gap-2">
        <form method="GET" class="mb-0">
            <select name="project_id" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                <option value="">{{ ui('select_project') ?? 'Select project' }}</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ $p->id === $project->id ? 'selected' : '' }}>{{ $p->key }} - {{ $p->name }}</option>
                @endforeach
            </select>
        </form>
    </div>
</div>

@if($backlogIssues->isEmpty())
    <div class="alert alert-info">{{ ui('no_issues_in_backlog') ?? 'No issues in backlog' }}</div>
@else
    <div class="list-group">
        @foreach($backlogIssues as $issue)
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ route('issues.show', $issue) }}" class="text-decoration-none fw-medium">{{ $issue->code }}</a>
                    <span class="text-muted">{{ $issue->title }}</span>
                </div>
                <span class="badge bg-secondary">{{ $issue->sprint ? $issue->sprint->name : ui('unstarted') }}</span>
            </div>
        @endforeach
    </div>
@endif
@endsection