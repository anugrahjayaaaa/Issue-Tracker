@php
    $isReply = $comment->parent_id !== null;
    $indentClass = $isReply ? 'ms-4 ms-md-5 border-start border-secondary' : '';
@endphp
<div class="d-flex gap-2 mb-2 {{ $indentClass }}" id="comment-row-{{ $comment->id }}">
    <span class="avatar avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:32px;height:32px">{{ strtoupper(substr($comment->user->name ?? '?', 0, 1)) }}</span>
    <div class="flex-grow-1">
        <div class="d-flex justify-content-between">
            <strong class="small">{{ $comment->user->name ?? '-' }}</strong>
            <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
        </div>
        <div class="small mb-1 comment-body" id="comment-body-{{ $comment->id }}">{{ $comment->body ?: '' }}</div>

        @if ($comment->attachments->isNotEmpty())
        <div class="d-flex flex-wrap gap-2">
            @foreach ($comment->attachments as $att)
            <a href="{{ $att->url() }}" target="_blank"><img src="{{ $att->url() }}" style="max-height:120px" class="img-thumbnail"></a>
            @endforeach
        </div>
        @endif

        @can('comment.edit')
        @if ($comment->user_id === auth()->id())
        <div class="mt-1 d-flex gap-1 align-items-center">
            <button type="button" class="btn btn-sm btn-light border rounded-2" onclick="editComment({{ $comment->id }})"><i class="bi bi-pencil"></i></button>
            <button type="button" class="btn btn-sm btn-light border rounded-2 text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-action="{{ route('issues.comments.destroy', $comment) }}"><i class="bi bi-trash"></i></button>
        </div>
        {{-- inline edit form (toggled) --}}
        <form method="POST" action="{{ route('issues.comments.update', $comment) }}" class="mt-2 d-none" id="comment-edit-form-{{ $comment->id }}">
            @csrf @method('PUT')
            @include('partials.rich-text-field', ['name' => 'body', 'id' => 'comment-edit-'.$comment->id, 'label' => ui('comment'), 'value' => $comment->body, 'uploadUrl' => route('comments.image.upload', $comment)])
            <button type="submit" class="btn btn-primary btn-sm mt-2">{{ ui('save') }}</button>
            <button type="button" class="btn btn-light btn-sm mt-2" onclick="cancelEditComment({{ $comment->id }})">{{ ui('cancel') }}</button>
        </form>
        @endif
        @endcan

        @if ($isReply && $comment->replies->isNotEmpty())
        <div class="mt-2">
            @else
        <div class="mt-2">
            @endif

            @if ($comment->replies->isNotEmpty())
                @foreach ($comment->replies as $reply)
                    @include('issues.partials.comment', ['comment' => $reply])
                @endforeach
            @endif

            @can('comment.create')
            @if (!$isReply)
            <button type="button" class="btn btn-sm btn-light border rounded-2" onclick="showReplyForm({{ $comment->id }})">
                <i class="bi bi-reply"></i> {{ ui('reply') ?? 'Reply' }}
            </button>
            @endif
            @endcan

            @can('comment.create')
            <div id="reply-form-{{ $comment->id }}" class="d-none mt-2">
                <form method="POST" action="{{ route('issues.comments.store', $issue) }}" class="mt-1">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                    @include('partials.rich-text-field', ['name' => 'body', 'id' => 'reply-'.$comment->id, 'label' => ui('comment'), 'uploadUrl' => ''])
                    <button type="submit" class="btn btn-primary btn-sm mt-2">{{ ui('post_reply') ?? 'Post reply' }}</button>
                    <button type="button" class="btn btn-light btn-sm mt-2" onclick="hideReplyForm({{ $comment->id }})">{{ ui('cancel') }}</button>
                </form>
            </div>
            @endcan
        </div>
    </div>
</div>
