@extends('layouts.app')
@section('content')
@include('partials.flash-message')

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('projects.show', $project) }}" class="text-decoration-none">{{ $project->key }} — {{ $project->name }}</a>
    </div>
    @can('project.manage')
        <a href="{{ route('projects.automation-rules.create', $project) }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> {{ ui('create') }}</a>
    @endcan
</div>

@if($rules->isEmpty())
    <div class="alert alert-info">{{ ui('no_automation_rules') }}</div>
@else
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>{{ ui('name') }}</th>
                <th>{{ ui('trigger') }}</th>
                <th>{{ ui('conditions') }}</th>
                <th>{{ ui('actions') }}</th>
                <th>{{ ui('status') }}</th>
                <th class="text-end">{{ ui('actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rules as $rule)
                <tr>
                    <td>{{ $rule->name }}</td>
                    <td><code>{{ $rule->event }}</code></td>
                    <td><code>{{ json_encode($rule->conditions) }}</code></td>
                    <td><code>{{ json_encode($rule->actions) }}</code></td>
                    <td>
                        @if($rule->enabled)
                            <span class="badge bg-success">{{ ui('enabled') }}</span>
                        @else
                            <span class="badge bg-secondary">{{ ui('disabled') }}</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('projects.automation-rules.edit', [$project, $rule]) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('projects.automation-rules.destroy', [$project, $rule]) }}" class="d-inline" onsubmit="return confirm('{{ ui('confirm_delete') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
