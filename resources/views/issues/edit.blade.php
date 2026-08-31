@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<div class="d-flex align-items-center mb-3">
    <a href="{{ route('issues.index', ['project_id' => $issue->project_id]) }}" class="btn btn-sm btn-light border rounded-2 me-2"><i class="bi bi-arrow-left"></i></a>
    <h3 class="mb-0">{{ ui('edit_issue') }} <span class="badge text-bg-secondary">{{ $issue->code }}</span></h3>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('issues.update', $issue) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">{{ ui('title') }}</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $issue->title) }}">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="row g-2 mb-3">
                <div class="col-md-3">
                    <label class="form-label">{{ ui('status') }}</label>
                    <select name="status" class="form-select">
                        @foreach ($issue->project->statuses as $s)<option value="{{ $s->key }}" {{ old('status', $issue->status) == $s->key ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ ui('type') }}</label>
                    <select name="type" class="form-select">
                        @foreach ($issue->project->issueTypes as $t)<option value="{{ $t->key }}" {{ old('type', $issue->type) == $t->key ? 'selected' : '' }}>{{ $t->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ ui('priority') }}</label>
                    <select name="priority" class="form-select">
                        @foreach ($priorities as $pr)<option value="{{ $pr }}" {{ old('priority', $issue->priority) == $pr ? 'selected' : '' }}>{{ ui('issue_priority_'.$pr) }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ ui('due_date') }}</label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $issue->due_date) }}">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ ui('assignee') }}</label>
                <select name="assignee_id" class="form-select">
                    <option value="">-</option>
                    @foreach ($users as $u)<option value="{{ $u->id }}" {{ old('assignee_id', $issue->assignee_id) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>@endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ ui('description') }}</label>
                @include('partials.rich-text-field', [
                    'value' => old('description', $issue->description),
                    'uploadUrl' => route('issues.image.upload', $issue),
                ])
            </div>
            @include('partials.labels-field')
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> {{ ui('save') }}</button>
        </form>
    </div>
</div>
@push('scripts')
{{-- ponytail: rich-text-field (TinyMCE) owns the description editor + submit sync. --}}
@endpush
@endsection
