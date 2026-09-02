@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<div class="d-flex align-items-center mb-3">
    <a href="{{ route('projects.index') }}" class="btn btn-sm btn-light border rounded-2 me-2"><i class="bi bi-arrow-left"></i></a>
    <h3 class="mb-0">{{ ui('edit_project') }}</h3>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('projects.update', $project) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">{{ ui('project') }}</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $project->name) }}">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">{{ ui('slug') }}</label>
                <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $project->slug) }}" disabled>
                <div class="form-text">{{ ui('slug_hint') }}</div>
                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">{{ ui('project_key') }}</label>
                <input type="text" name="key" class="form-control @error('key') is-invalid @enderror" value="{{ old('key', $project->key) }}" maxlength="10" style="text-transform:uppercase">
                @error('key')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @include('partials.rich-text-field', [
                'value' => old('description', $project->description),
                'uploadUrl' => route('projects.image.upload', $project),
            ])
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> {{ ui('save') }}</button>
        </form>
    </div>
</div>
@endsection
