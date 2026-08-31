@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<div class="d-flex align-items-center mb-3">
    <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-light border rounded-2 me-2"><i class="bi bi-arrow-left"></i></a>
    <h3 class="mb-0">{{ ui('issue_fields') }} · {{ $project->name }}</h3>
</div>

<div class="row g-3">
    {{-- Issue types --}}
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between"><span>{{ ui('issue_types') }}</span></div>
            <div class="card-body">
                <form method="POST" action="{{ route('projects.types.store', $project) }}" class="row g-2 mb-3">
                    @csrf
                    <div class="col-5"><input name="name" class="form-control form-control-sm" placeholder="{{ ui('name') }}" required></div>
                    <div class="col-3"><input name="color" type="color" class="form-control form-control-sm" value="#6c757d"></div>
                    <div class="col-4"><input name="icon" class="form-control form-control-sm" placeholder="bi-bug"></div>
                    <div class="col-12"><input name="description" class="form-control form-control-sm" placeholder="{{ ui('description') }}"></div>
                    <div class="col-12"><button class="btn btn-sm btn-primary">{{ ui('add') }}</button></div>
                </form>
                <ul class="list-group list-group-flush">
                    @foreach ($project->issueTypes as $t)
                    <li class="list-group-item d-flex align-items-center gap-2">
                        <span class="badge" style="background:{{ $t->color }};color:#fff"><i class="{{ $t->icon }}"></i> {{ $t->name }}</span>
                        <form method="POST" action="{{ route('projects.types.destroy', [$project, $t]) }}" class="d-inline ms-auto">@csrf @method('DELETE')<button class="btn btn-sm btn-light border"><i class="bi bi-trash"></i></button></form>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- Statuses --}}
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between"><span>{{ ui('statuses') }}</span></div>
            <div class="card-body">
                <form method="POST" action="{{ route('projects.statuses.store', $project) }}" class="row g-2 mb-3">
                    @csrf
                    <div class="col-5"><input name="name" class="form-control form-control-sm" placeholder="{{ ui('name') }}" required></div>
                    <div class="col-3"><input name="color" type="color" class="form-control form-control-sm" value="#6c757d"></div>
                    <div class="col-4"><label class="form-check mt-1"><input type="checkbox" name="is_closed" value="1" class="form-check-input"> {{ ui('closed') }}</label></div>
                    <div class="col-12"><button class="btn btn-sm btn-primary">{{ ui('add') }}</button></div>
                </form>
                <ul class="list-group list-group-flush">
                    @foreach ($project->statuses as $s)
                    <li class="list-group-item d-flex align-items-center gap-2">
                        <span class="badge" style="background:{{ $s->color }};color:#fff">{{ $s->name }}</span>
                        @if ($s->is_closed)<span class="badge text-bg-secondary">{{ ui('closed') }}</span>@endif
                        <form method="POST" action="{{ route('projects.statuses.destroy', [$project, $s]) }}" class="d-inline ms-auto">@csrf @method('DELETE')<button class="btn btn-sm btn-light border"><i class="bi bi-trash"></i></button></form>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- Transitions --}}
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">{{ ui('workflow_transitions') }}</div>
            <div class="card-body">
                <p class="text-muted small">{{ ui('workflow_transitions_hint') }}</p>
                <form method="POST" action="{{ route('projects.transitions.store', $project) }}" class="row g-2 align-items-end mb-3">
                    @csrf
                    <div class="col-auto">
                        <select name="from_status_id" class="form-select form-select-sm">
                            @foreach ($project->statuses as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-auto"><span>→</span></div>
                    <div class="col-auto">
                        <select name="to_status_id" class="form-select form-select-sm">
                            @foreach ($project->statuses as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-auto"><button class="btn btn-sm btn-primary">{{ ui('add') }}</button></div>
                </form>
                <ul class="list-group list-group-flush">
                    @forelse ($project->statusTransitions as $tr)
                    <li class="list-group-item d-flex align-items-center gap-2">
                        <span class="badge" style="background:{{ $tr->from->color }};color:#fff">{{ $tr->from->name }}</span>
                        <span>→</span>
                        <span class="badge" style="background:{{ $tr->to->color }};color:#fff">{{ $tr->to->name }}</span>
                        <form method="POST" action="{{ route('projects.transitions.destroy', [$project, $tr]) }}" class="d-inline ms-auto">@csrf @method('DELETE')<button class="btn btn-sm btn-light border"><i class="bi bi-trash"></i></button></form>
                    </li>
                    @empty
                    <li class="list-group-item text-muted small">{{ ui('no_transitions_free') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
