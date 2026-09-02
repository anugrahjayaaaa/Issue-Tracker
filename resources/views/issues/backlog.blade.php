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

@php
    // Group issues by sprint; unstarted = null sprint
    $grouped = $backlogIssues->groupBy(fn($i) => $i->sprint_id ?? 'unstarted');
@endphp

<div class="row g-3">
    {{-- Unstarted (no sprint) — draggable --}}
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-transparent">
                <h5 class="mb-0">{{ ui('unstarted') }}</h5>
            </div>
            <div class="card-body" id="sprint-unstarted" data-sprint-id="">
                @forelse($grouped->get('unstarted', []) as $issue)
                    @include('issues.partials.backlog-item', ['issue' => $issue])
                @empty
                    <p class="text-muted small mb-0">{{ ui('no_issues') ?? 'No issues' }}</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Active sprints --}}
    @foreach($sprints as $sprint)
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="mb-0">{{ $sprint->name }}</h5>
                        <small class="text-muted">{{ $sprint->goal ?? '' }}</small>
                    </div>
                    @can('project.manage')
                    <form method="POST" action="{{ route('projects.sprints.complete', [$project, $sprint]) }}" onsubmit="return confirm('Complete sprint? Unfinished issues move to backlog.')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-warning">{{ ui('complete_sprint') }}</button>
                    </form>
                    @endcan
                </div>
                <div class="card-body" id="sprint-{{ $sprint->id }}" data-sprint-id="{{ $sprint->id }}">
                    @forelse($grouped->get($sprint->id, []) as $issue)
                        @include('issues.partials.backlog-item', ['issue' => $issue])
                    @empty
                        <p class="text-muted small mb-0">{{ ui('no_issues') ?? 'No issues' }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach
</div>

@section('scripts')
@verbatim
<script src="{{ url('vendor/sortable.min.js') }}"></script>
<script>
// ponytail: drag-drop between sprints — minimal, fetch PUT to issues.sprint
(function() {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const headers = { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' };

    document.querySelectorAll('[id^="sprint-"]').forEach(el => {
        new Sortable(el, {
            group: 'backlog',
            animation: 150,
            onEnd: async function(evt) {
                const targetId = evt.to.dataset.sprintId; // '' = unstarted
                const issueId = evt.item.dataset.issueId;
                try {
                    const res = await fetch(`/issues/${issueId}/sprint`, {
                        method: 'PUT',
                        headers: headers,
                        body: JSON.stringify({ sprint_id: targetId || null })
                    });
                    if (!res.ok) {
                        alert('Failed to update sprint');
                        location.reload();
                    }
                } catch(e) {
                    alert('Network error: ' + e.message);
                    location.reload();
                }
            }
        });
    });
})();
</script>
@endverbatim
@endsection
