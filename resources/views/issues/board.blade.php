@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>{{ ui('board') }}</h3>
    <div class="d-flex gap-2">
        <form method="GET">
            <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">{{ ui('select_project') }}</option>
                @foreach ($projects as $p)
                    <option value="{{ $p->id }}" {{ $project && $project->id == $p->id ? 'selected' : '' }}>{{ $p->key }} - {{ $p->name }}</option>
                @endforeach
            </select>
        </form>
        @if ($project)
        <form method="GET">
            <input type="hidden" name="project_id" value="{{ $project->id }}">
            <select name="component_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="all">{{ ui('all_components') }}</option>
                @foreach ($project->components as $c)
                    <option value="{{ $c->id }}" {{ request('component_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </form>
        @endif
        @can('issue.create')
        @if ($project)
        <a href="{{ route('issues.create', ['project_id' => $project->id]) }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> {{ ui('new_issue') }}</a>
        @endif
        @endcan
    </div>
</div>

@if (!$project)
<div class="alert alert-info">{{ ui('pick_project_first') }}</div>
@else
<div class="row g-3">
    @foreach ($columns as $status => $cards)
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between">
                <span>{{ $project->statuses->firstWhere('key', $status)?->name ?? $status }}</span>
                <span class="badge text-bg-secondary">{{ $cards->count() }}</span>
            </div>
            <div class="card-body" data-status="{{ $status }}">
                @forelse ($cards as $issue)
                <div class="card mb-2 border-start border-3 border-primary" draggable="true" data-issue="{{ $issue->id }}">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">{{ $issue->code }}</small>
                            <span class="badge text-bg-info">{{ ui('issue_priority_'.$issue->priority) }}</span>
                        </div>
                        <div class="fw-medium small">{{ $issue->title }}</div>
                        <div class="text-muted small">{{ $issue->assignee->name ?? '-' }}</div>
                        <div class="mt-1">
                            @foreach ($issue->labels as $l)
                                <span class="badge" style="background:{{ $l->color }}">{{ $l->name }}</span>
                            @endforeach
                        </div>
                        <a href="{{ route('issues.show', $issue) }}" class="stretched-link"></a>
                    </div>
                </div>
                @empty
                <div class="text-muted small text-center py-3">{{ ui('no_issues') }}</div>
                @endforelse
            </div>
        </div>
    </div>
    @endforeach
</div>

@push('scripts')
<script src="{{ asset('vendor/sortable.min.js') }}"></script>
<script>
document.querySelectorAll('[data-status]').forEach(col => {
    new Sortable(col, {
        group: 'issues',
        animation: 150,
        onEnd(evt) {
            const issueId = evt.item.dataset.issue;
            const status = evt.to.dataset.status;
            fetch('{{ route('issues.status', ['issue' => '__ID__']) }}'.replace('__ID__', issueId), {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json'},
                body: JSON.stringify({status: status, order: evt.newIndex})
            }).then(r => { if (!r.ok) location.reload(); });
        }
    });
});
</script>
@endpush
@endif
@endsection
