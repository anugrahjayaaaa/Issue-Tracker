@extends('layouts.app')
@section('content')
@include('partials.flash-message')

<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('projects.automation-rules.index', $project) }}" class="text-decoration-none">{{ $project->key }} — {{ $project->name }}</a>
    <span class="text-muted">/</span>
    <span>{{ ui('edit_rule') }}: {{ $rule->name }}</span>
</div>

<form method="POST" action="{{ route('projects.automation-rules.update', [$project, $rule]) }}">
    @csrf @method('PUT')
    @include('projects.automation-rules._form')
    <button type="submit" class="btn btn-sm btn-primary">{{ ui('save') }}</button>
    <a href="{{ route('projects.automation-rules.index', $project) }}" class="btn btn-sm btn-light">{{ ui('cancel') }}</a>
</form>
@endsection
