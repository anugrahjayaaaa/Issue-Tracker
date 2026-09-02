@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center">
        <a href="{{ route('issues.index', ['project_id' => $issue->project_id]) }}" class="btn btn-sm btn-light border rounded-2 me-2"><i class="bi bi-arrow-left"></i></a>
        <div>
            @if ($issue->parent)
                <div class="small text-muted mb-1">
                    <i class="bi bi-arrow-return-right"></i> <a href="{{ route('issues.show', $issue->parent) }}" class="text-decoration-none">{{ $issue->parent->code }} · {{ $issue->parent->title }}</a>
                </div>
            @endif
            <h3 class="mb-0"><span class="badge text-bg-secondary me-1">{{ $issue->code }}</span> {{ $issue->title }}</h3>
        </div>
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

        {{-- Sub-tasks (Phase B) --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>{{ ui('subtasks') }}
                    @php $prog = $issue->subtaskProgress(); @endphp
                    @if ($prog['total'] > 0)
                        <span class="badge text-bg-light border ms-1">{{ $prog['done'] }}/{{ $prog['total'] }}</span>
                    @endif
                </span>
                @can('issue.create')
                <a href="{{ route('issues.create', ['project_id' => $issue->project_id, 'parent_id' => $issue->id]) }}" class="btn btn-sm btn-light border rounded-2"><i class="bi bi-plus-lg"></i> {{ ui('add_subtask') }}</a>
                @endcan
            </div>
            <div class="card-body">
                @if ($issue->children->isNotEmpty())
                    <ul class="list-unstyled mb-0">
                        @foreach ($issue->children as $child)
                            <li class="d-flex justify-content-between align-items-center py-1 @if(!$loop->last) border-bottom @endif">
                                <a href="{{ route('issues.show', $child) }}" class="text-decoration-none">
                                    <span class="badge text-bg-secondary me-1">{{ $child->code }}</span>
                                    {{ $child->title }}
                                </a>
                                <x-issue-badge :issue="$child" field="status" />
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-muted small">{{ ui('no_subtasks') }}</div>
                @endif
            </div>
        </div>

        {{-- Comments --}}
        <div class="card shadow-sm">
            <div class="card-header">{{ ui('comments') }} ({{ $issue->comments->whereNull('parent_id')->count() }})</div>
            <div class="card-body" style="max-height:520px;overflow-y:auto">
                @foreach ($issue->comments->whereNull('parent_id') as $comment)
                    @include('issues.partials.comment', ['comment' => $comment])
                @endforeach
                @if ($issue->comments->whereNull('parent_id')->isEmpty())
                <div class="text-muted small text-center py-3">{{ ui('no_comments') }}</div>
                @endif

                @can('comment.create')
                <form method="POST" action="{{ route('issues.comments.store', $issue) }}" class="mt-3" id="comment-form">
                    @csrf
                    @include('partials.rich-text-field', ['name' => 'body', 'id' => 'comment-new', 'label' => ui('comment'), 'uploadUrl' => ''])
                    <div class="d-flex gap-2 mt-2">
                        <button type="submit" class="btn btn-primary btn-sm" id="comment-submit">{{ ui('post_comment') }}</button>
                    </div>
                </form>
                @endcan
            </div>
        </div>
        {{-- Issue-level attachments (Phase A: list + upload, scoped folder) --}}
        <div class="card shadow-sm mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>{{ ui('attachments') }}</span>
            </div>
            <div class="card-body">
                @if ($issue->attachments->isNotEmpty())
                <div class="d-flex flex-wrap gap-2 mb-2">
                    @foreach ($issue->attachments as $att)
                        <span class="d-inline-flex align-items-center gap-1">
                            <a href="{{ $att->url() }}" target="_blank" class="text-decoration-none">
                                <span class="badge text-bg-light border">{{ basename($att->path) }}</span>
                            </a>
                            @can('issue.edit')
                            <form method="POST" action="{{ route('issues.attachments.destroy', [$issue, $att]) }}" class="d-inline" onsubmit="return confirm('{{ ui('confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-x-circle"></i></button>
                            </form>
                            @endcan
                        </span>
                    @endforeach
                </div>
                @else
                <div class="text-muted small">{{ ui('no_attachments') }}</div>
                @endif
                @can('comment.create')
                <form method="POST" action="{{ route('issues.attachments.store', $issue) }}" enctype="multipart/form-data" class="mt-2">
                    @csrf
                    <input type="file" name="file" class="form-control form-control-sm" required>
                    <button type="submit" class="btn btn-sm btn-outline-primary mt-2">{{ ui('add') }}</button>
                </form>
                @endcan
            </div>
        </div>
        {{-- Activity timeline --}}
        <div class="card shadow-sm">
            <div class="card-header">{{ ui('activity_timeline') }}</div>
            <div class="card-body">
                @php $timeline = $issue->activityTimeline(); @endphp
                @if ($timeline->isEmpty())
                <div class="text-muted small text-center py-3">{{ ui('no_activity') }}</div>
                @else
                <ul class="list-unstyled mb-0">
                    @foreach ($timeline as $entry)
                    <li class="d-flex gap-2 pb-3 @if(!$loop->last) border-bottom @endif">
                        <i class="bi bi-circle-fill text-{{ $entry->description === 'issue_created' || $entry->description === 'comment_created' ? 'success' : ($entry->description === 'issue_deleted' || $entry->description === 'comment_deleted' ? 'danger' : 'secondary') }} small mt-1"></i>
                        <div>
                            <div class="small">{{ __('messages.'.$entry->description) }}</div>
                            <div class="text-muted" style="font-size:11px">{{ $entry->causer->name ?? '-' }} · {{ $entry->created_at->diffForHumans() }}</div>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header">{{ ui('details') }}</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td>{{ ui('project') }}</td><td>{{ $issue->project->key }}</td></tr>
                    <tr><td>{{ ui('type') }}</td><td><x-issue-badge :issue="$issue" field="type" /></td></tr>
                    <tr><td>{{ ui('status') }}</td><td><x-issue-badge :issue="$issue" field="status" /></td></tr>
                    <tr><td>{{ ui('priority') }}</td><td>{{ ui('issue_priority_'.$issue->priority) }}</td></tr>
                    @php
                        $canEditMeta = App\Models\ProjectMember::hasRole(auth()->user(), $issue->project, [App\Models\ProjectMember::ROLE_LEAD, App\Models\ProjectMember::ROLE_MEMBER]);
                    @endphp
                    <tr>
                        <td>{{ ui('assignee') }}</td>
                        <td>
                            @if ($canEditMeta)
                            <form method="POST" action="{{ route('issues.update', $issue) }}" class="d-inline">
                                @csrf @method('PUT')
                                <input list="assignee-options" name="assignee_id" class="form-control form-control-sm" value="{{ $issue->assignee_id ?? '' }}" placeholder="-">
                                <datalist id="assignee-options">
                                    <option value="">-</option>
                                    @foreach ($issue->project->users as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                    @endforeach
                                </datalist>
                                <button type="submit" class="btn btn-sm btn-light border rounded-2 mt-1"><i class="bi bi-check-lg"></i></button>
                            </form>
                            @else
                                {{ $issue->assignee->name ?? '-' }}
                            @endif
                        </td>
                    </tr>
                    <tr><td>{{ ui('reporter') }}</td><td>{{ $issue->reporter->name ?? '-' }}</td></tr>
                    <tr>
                        <td>{{ ui('due_date') }}</td>
                        <td>
                            @if ($canEditMeta)
                            <form method="POST" action="{{ route('issues.update', $issue) }}" class="d-inline">
                                @csrf @method('PUT')
                                <input type="date" name="due_date" class="form-control form-control-sm" value="{{ $issue->due_date?->format('Y-m-d') }}">
                                <button type="submit" class="btn btn-sm btn-light border rounded-2 mt-1"><i class="bi bi-check-lg"></i></button>
                            </form>
                            @else
                                {{ $issue->due_date ?? '-' }}
                            @endif
                        </td>
                    </tr>
                    <tr>
        <td>{{ ui('labels') }}</td>
        <td>
            @if ($canEditMeta)
            <form method="POST" action="{{ route('issues.update', $issue) }}">
                @csrf @method('PUT')
                <select name="labels[]" class="form-select form-select-sm" multiple size="3">
                    @foreach ($issue->project->labels as $l)
                        <option value="{{ $l->id }}" @selected($issue->labels->contains($l->id))>{{ $l->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-light border rounded-2 mt-1"><i class="bi bi-check-lg"></i></button>
            </form>
            @else
                @forelse ($issue->labels as $l)
                    <span class="badge" style="background:{{ $l->color }}">{{ $l->name }}</span>
                @empty
                    -
                @endforelse
            @endif
        </td>
    </tr>
                </table>
            </div>
        </div>

        {{-- Watchers (Phase B) --}}
        <div class="card shadow-sm mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>{{ ui('watchers') }} <span class="badge text-bg-light border ms-1">{{ $issue->watchers->count() }}</span></span>
                @if ($issue->watchers->contains(auth()->id()))
                    <form method="POST" action="{{ route('issues.unwatch', $issue) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-light border rounded-2"><i class="bi bi-eye-slash"></i> {{ ui('unwatch') }}</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('issues.watch', $issue) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-light border rounded-2"><i class="bi bi-eye"></i> {{ ui('watch') }}</button>
                    </form>
                @endif
            </div>
            <div class="card-body">
                @if ($issue->watchers->isNotEmpty())
                    <div class="d-flex flex-wrap gap-1">
                        @foreach ($issue->watchers as $w)
                            <span class="badge text-bg-light border">{{ $w->name }}</span>
                        @endforeach
                    </div>
                @else
                    <div class="text-muted small">{{ ui('no_watchers') }}</div>
                @endif
            </div>
        </div>

        {{-- Components --}}
        <div class="card shadow-sm mt-3">
            <div class="card-header">{{ ui('components') }}</div>
            <div class="card-body">
                @if ($issue->components->isNotEmpty())
                    <div class="d-flex flex-wrap gap-1">
                        @foreach ($issue->components as $c)
                            <span class="badge text-bg-light border">{{ $c->name }}</span>
                        @endforeach
                    </div>
                @else
                    <div class="text-muted small">{{ ui('no_components') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('partials.modals.delete-modal')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    window._editComment = function (id) {
        const body = document.getElementById('comment-body-' + id);
        const form = document.getElementById('comment-edit-form-' + id);
        body.classList.add('d-none');
        form.classList.remove('d-none');
        const ta = form.querySelector('textarea[data-upload-url]');
        if (ta) window.initTinyMCE(ta.id, ta.dataset.uploadUrl);
    };
    window._cancelEditComment = function (id) {
        document.getElementById('comment-edit-form-' + id).classList.add('d-none');
        document.getElementById('comment-body-' + id).classList.remove('d-none');
    };
});
</script>
@endpush
@endsection
