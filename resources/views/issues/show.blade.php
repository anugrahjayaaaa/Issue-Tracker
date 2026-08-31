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

        {{-- Comments --}}
        <div class="card shadow-sm">
            <div class="card-header">{{ ui('comments') }} ({{ $issue->comments->count() }})</div>
            <div class="card-body">
                @foreach ($issue->comments as $comment)
                <div class="d-flex gap-2 mb-3 @if(!$loop->last) border-bottom pb-3 @endif" id="comment-row-{{ $comment->id }}">
                    <span class="avatar avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:32px;height:32px">{{ strtoupper(substr($comment->user->name,0,1)) }}</span>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <strong class="small">{{ $comment->user->name }}</strong>
                            <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="small mb-1 comment-body" id="comment-body-{{ $comment->id }}">{!! $comment->body !!}</div>
                        @if ($comment->attachments->isNotEmpty())
                            <div class="d-flex flex-wrap gap-2">
                            @foreach ($comment->attachments as $att)
                                <a href="{{ $att->url() }}" target="_blank"><img src="{{ $att->url() }}" style="max-height:120px" class="img-thumbnail"></a>
                            @endforeach
                            </div>
                        @endif
                        @can('comment.edit')
                        @if ($comment->user_id === auth()->id() || App\Models\ProjectMember::isLead(auth()->user(), $issue->project))
                        <div class="mt-1">
                            <button class="btn btn-sm btn-light border rounded-2" onclick="editComment({{ $comment->id }})"><i class="bi bi-pencil"></i></button>
                            <form method="POST" action="{{ route('issues.comments.destroy', $comment) }}" class="d-inline" onsubmit="return confirm('{{ ui('confirm_delete_comment') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light border rounded-2 text-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                        {{-- inline edit form (toggled) --}}
                        <form method="POST" action="{{ route('issues.comments.update', $comment) }}" class="mt-2 d-none" id="comment-edit-form-{{ $comment->id }}">
                            @csrf @method('PUT')
                            @include('partials.rich-text-field', ['name' => 'body', 'id' => 'comment-edit-'.$comment->id, 'value' => $comment->body, 'uploadUrl' => route('comments.image.upload', $comment)])
                            <button type="submit" class="btn btn-primary btn-sm mt-2">{{ ui('save') }}</button>
                            <button type="button" class="btn btn-light btn-sm mt-2" onclick="cancelEditComment({{ $comment->id }})">{{ ui('cancel') }}</button>
                        </form>
                        @endif
                        @endcan
                    </div>
                </div>
                @endforeach
                @if ($issue->comments->isEmpty())
                <div class="text-muted small text-center py-3">{{ ui('no_comments') }}</div>
                @endif

                @can('comment.create')
                <form method="POST" action="{{ route('issues.comments.store', $issue) }}" class="mt-3" id="comment-form">
                    @csrf
                    @include('partials.rich-text-field', ['name' => 'body', 'id' => 'comment-new', 'uploadUrl' => ''])
                    <div class="d-flex gap-2 mt-2">
                        <button type="submit" class="btn btn-primary btn-sm" id="comment-submit">{{ ui('post_comment') }}</button>
                    </div>
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
                    <tr><td>{{ ui('type') }}</td><td>{{ ui('issue_type_'.$issue->type) }}</td></tr>
                    <tr><td>{{ ui('status') }}</td><td>{{ ui('issue_status_'.$issue->status) }}</td></tr>
                    <tr><td>{{ ui('priority') }}</td><td>{{ ui('issue_priority_'.$issue->priority) }}</td></tr>
                    <tr><td>{{ ui('assignee') }}</td><td>{{ $issue->assignee->name ?? '-' }}</td></tr>
                    <tr><td>{{ ui('reporter') }}</td><td>{{ $issue->reporter->name ?? '-' }}</td></tr>
                    <tr><td>{{ ui('due_date') }}</td><td>{{ $issue->due_date ?? '-' }}</td></tr>
                    <tr><td>{{ ui('labels') }}</td><td>
                        @forelse ($issue->labels as $l)
                            <span class="badge" style="background:{{ $l->color }}">{{ $l->name }}</span>
                        @empty
                            -
                        @endforelse
                    </td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

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
