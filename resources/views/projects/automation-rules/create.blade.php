@extends('layouts.app')
@section('content')
@include('partials.flash-message')

<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('projects.automation-rules.index', $project) }}" class="text-decoration-none">{{ $project->key }} — {{ $project->name }}</a>
    <span class="text-muted">/</span>
    <span>{{ ui('create_rule') }}</span>
</div>

<form method="POST" action="{{ route('projects.automation-rules.store', $project) }}">
    @csrf
    @include('projects.automation-rules._form', ['rule' => null])
    <button type="submit" class="btn btn-sm btn-primary">{{ ui('create') }}</button>
</form>
@endsection
