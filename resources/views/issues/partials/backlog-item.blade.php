@php
    $priorityClass = [
        'urgent' => 'text-danger',
        'high' => 'text-warning',
        'medium' => 'text-info',
        'low' => 'text-muted',
    ][$issue->priority] ?? 'text-muted';
@endphp
<div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-issue-id="{{ $issue->id }}">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-arrows-move cursor-move text-secondary"></i>
        <a href="{{ route('issues.show', $issue) }}" class="text-decoration-none fw-medium">{{ $issue->code }}</a>
        <span class="text-muted">{{ \Illuminate\Support\Str::limit($issue->title, 60) }}</span>
        <span class="badge bg-light text-dark text-uppercase small">{{ $issue->priority }}</span>
    </div>
</div>
