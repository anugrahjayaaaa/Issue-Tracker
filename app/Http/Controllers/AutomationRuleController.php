<?php

namespace App\Http\Controllers;

use App\Http\Requests\Automation\StoreAutomationRuleRequest;
use App\Http\Requests\Automation\UpdateAutomationRuleRequest;
use App\Http\Controllers\Concerns\AuthorizesProject;
use App\Models\AutomationRule;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AutomationRuleController extends Controller
{
    use AuthorizesProject;

    public function index(Request $request, Project $project): View
    {
        $this->ensureProjectReader($project);

        $rules = $project->automationRules()->latest()->get();

        return view('projects.automation-rules.index', compact('project', 'rules'));
    }

    public function create(Request $request, Project $project): View
    {
        $this->ensureProjectLead($project);

        return view('projects.automation-rules.create', compact('project'));
    }

    public function store(StoreAutomationRuleRequest $request, Project $project): RedirectResponse
    {
        $this->ensureProjectLead($project);

        $project->automationRules()->create($request->validated());

        return redirect()->route('projects.automation-rules.index', $project)
            ->with('success', __('messages.automation_rule_created'));
    }

    public function show(Request $request, Project $project, AutomationRule $rule): View
    {
        $this->ensureProjectReader($project);
        abort_unless($rule->project_id === $project->id, 404);

        $rule->load('logs.issue');

        return view('projects.automation-rules.show', compact('project', 'rule'));
    }

    public function edit(Project $project, AutomationRule $rule): View
    {
        $this->ensureProjectLead($project);
        abort_unless($rule->project_id === $project->id, 404);

        return view('projects.automation-rules.edit', compact('project', 'rule'));
    }

    public function update(UpdateAutomationRuleRequest $request, Project $project, AutomationRule $rule): RedirectResponse
    {
        $this->ensureProjectLead($project);
        abort_unless($rule->project_id === $project->id, 404);

        $rule->update($request->validated());

        return redirect()->route('projects.automation-rules.index', $project)
            ->with('success', __('messages.automation_rule_updated'));
    }

    public function destroy(Project $project, AutomationRule $rule): RedirectResponse
    {
        $this->ensureProjectLead($project);
        abort_unless($rule->project_id === $project->id, 404);

        $rule->delete();

        return back()->with('success', __('messages.automation_rule_deleted'));
    }
}
