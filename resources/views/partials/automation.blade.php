@can('project.manage')
<div class="card shadow-sm mb-3">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-gear text-secondary"></i>
        <span>{{ ui('automation') }}</span>
    </div>
    <div class="card-body py-3">
        @if($project->automationRules?->isEmpty())
            <p class="text-muted small mb-0">{{ ui('no_automation_rules') }}</p>
        @else
            <ul class="list-group list-group-flush">
                @foreach($project->automationRules as $rule)
                    <li class="list-group-item border-0 d-flex justify-content-between align-items-center py-2">
                        <div>
                            <span class="fw-medium">{{ $rule->name }}</span>
                            <small class="text-muted ms-2">({{ $rule->event }})</small>
                        </div>
                        <a href="{{ route('projects.automation-rules.show', [$project, $rule]) }}" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a>
                    </li>
                @endforeach
            </ul>
        @endif
        <a href="{{ route('projects.automation-rules.index', $project) }}" class="btn btn-sm btn-primary mt-2 w-100"><i class="bi bi-list"></i> {{ ui('manage') }}</a>
        <a href="{{ route('projects.automation-rules.create', $project) }}" class="btn btn-sm btn-light mt-2 w-100"><i class="bi bi-plus"></i> {{ ui('create_rule') }}</a>
    </div>
</div>
@endcan
