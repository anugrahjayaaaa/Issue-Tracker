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
        @include('partials.members', ['project' => $project, 'users' => $users])
        @include('partials.labels')
        @include('partials.components')
        @include('partials.automation')
    </div>
</div>
@endsection
