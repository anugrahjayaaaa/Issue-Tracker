@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center">
        <a href="{{ route('issues.index', ['project_id' => $issue->project_id]) }}" class="btn btn-sm btn-light border rounded-2 me-2"><i class="bi bi-arrow-left"></i></a>
        <h3 class="mb-0"><span class="badge text-bg-secondary me-1">{{ $issue->code }}</span> {{ $issue->title }}</h3>
    </div>
    @can('issue.edit')
    <a href="{{ route('issues.edit', $issue) }}" class="btn btn-light border rounded-2"><i class="bi bi-pencil me-1"></i> {{ ui('edit') }}</a>
    @endcan
</div>
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header">{{ ui('description') }}</div>
            <div class="card-body">{!! $issue->description ?: '-' !!}</div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header">{{ ui('details') }}</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td>{{ ui('project') }}</td><td>{{ $issue->project->key }}</td></tr>
                    <tr><td>{{ ui('type') }}</td><td>{{ ui('issue_type_'.$issue->type) }}</td></tr>
                    <tr><td>{{ ui('status') }}</td><td>{{ ui('issue_status_'.$issue->status) }}</td></tr>
                    <tr><td>{{ ui('priority') }}</td><td>{{ ui('issue_priority_'.$issue->priority) }}</td></tr>
                    <tr><td>{{ ui('assignee') }}</td><td>{{ $issue->assignee->name ?? '-' }}</td></tr>
                    <tr><td>{{ ui('reporter') }}</td><td>{{ $issue->reporter->name ?? '-' }}</td></tr>
                    <tr><td>{{ ui('due_date') }}</td><td>{{ $issue->due_date ?? '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
