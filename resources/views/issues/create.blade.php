@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<div class="d-flex align-items-center mb-3">
    <a href="{{ route('issues.index') }}" class="btn btn-sm btn-light border rounded-2 me-2"><i class="bi bi-arrow-left"></i></a>
    <h3 class="mb-0">{{ ui('new_issue') }}</h3>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('issues.store') }}" id="issue-form">
            @csrf
            <div class="mb-3">
                <label class="form-label">{{ ui('project') }}</label>
                <select name="project_id" class="form-select @error('project_id') is-invalid @enderror"
                        onchange="window.location='{{ route('issues.create') }}?project_id='+this.value">
                    <option value="">{{ ui('select_project') }}</option>
                    @foreach ($projects as $p)
                        <option value="{{ $p->id }}" {{ old('project_id', $project?->id) == $p->id ? 'selected' : '' }}>{{ $p->key }} - {{ $p->name }}</option>
                    @endforeach
                </select>
                @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @if ($project)
            <div class="mb-3">
                <label class="form-label">{{ ui('title') }}</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="row g-2 mb-3">
                <div class="col-md-3">
                    <label class="form-label">{{ ui('type') }}</label>
                    <select name="type" class="form-select">
                        @foreach ($types as $t)<option value="{{ $t }}" {{ old('type') == $t ? 'selected' : '' }}>{{ ui('issue_type_'.$t) }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ ui('priority') }}</label>
                    <select name="priority" class="form-select">
                        @foreach ($priorities as $pr)<option value="{{ $pr }}" {{ old('priority',$pr) == $pr ? 'selected' : '' }}>{{ ui('issue_priority_'.$pr) }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ ui('assignee') }}</label>
                    <select name="assignee_id" class="form-select">
                        <option value="">-</option>
                        @foreach ($users as $u)<option value="{{ $u->id }}" {{ old('assignee_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ ui('due_date') }}</label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ ui('description') }}</label>
                @include('partials.rich-text-field', ['uploadUrl' => ''])
            </div>
            @include('partials.labels-field')
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> {{ ui('save') }}</button>
            @endif
        </form>
    </div>
</div>
@push('scripts')
{{-- ponytail: rich-text-field (TinyMCE) owns the description editor + submit sync. --}}
@endpush
@endsection
