@extends('layouts.app')
@section('content')
@include('partials.flash-message')

<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('projects.automation-rules.index', $project) }}" class="text-decoration-none">{{ $project->key }} — {{ $project->name }}</a>
    <span class="text-muted">/</span>
    <span>{{ $rule->name }}</span>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="mb-2"><strong>{{ ui('trigger') }}:</strong> <code>{{ $rule->event }}</code></div>
        <div class="mb-2"><strong>{{ ui('conditions') }}:</strong> <code>{{ json_encode($rule->conditions) }}</code></div>
        <div class="mb-2"><strong>{{ ui('actions') }}:</strong> <code>{{ json_encode($rule->actions) }}</code></div>
        <div class="mb-2"><strong>{{ ui('status') }}:</strong>
            @if($rule->enabled)
                <span class="badge bg-success">{{ ui('enabled') }}</span>
            @else
                <span class="badge bg-secondary">{{ ui('disabled') }}</span>
            @endif
        </div>
        <a href="{{ route('projects.automation-rules.edit', [$project, $rule]) }}" class="btn btn-sm btn-primary">{{ ui('edit') }}</a>
    </div>
</div>

@if($rule->logs->isNotEmpty())
    <div class="card shadow-sm mt-3">
        <div class="card-header">{{ ui('logs') }}</div>
        <div class="card-body">
            <table class="table table-sm">
                <thead><tr><th>{{ ui('timestamp') }}</th><th>{{ ui('result') }}</th><th>{{ ui('issue') }}</th></tr></thead>
                <tbody>
                    @foreach($rule->logs as $log)
                        <tr>
                            <td>{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                            <td><span class="badge bg-{{ $log->status === 'success' ? 'success' : 'danger' }}">{{ $log->status }}</span></td>
                            <td>{{ $log->issue->code ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
