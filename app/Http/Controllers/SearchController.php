<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesProject;
use App\Http\Requests\Issue\IssueIndexRequest;
use App\Models\Issue;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    use AuthorizesProject;

    public function __invoke(Request $request): View
    {
        $projects = Project::query()
            ->whereHas('members', fn ($q) => $q->where('user_id', auth()->id()))
            ->orderBy('name')->get();

        $project = $request->filled('project_id') ? Project::findOrFail($request->project_id) : null;

        $issues = collect();
        if ($project) {
            $this->ensureProjectReader($project);

            $issues = Issue::with(['assignee', 'labels'])
                ->where('project_id', $project->id)
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->when($request->filled('assignee_id'), fn ($q) => $q->where('assignee_id', $request->assignee_id))
                ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->priority))
                ->when($request->filled('label_id'), fn ($q) => $q->whereHas('labels', fn ($l) => $l->where('labels.id', $request->label_id)))
                ->when($request->filled('q'), function ($q) use ($request) {
                    $term = $request->query('q');
                    $q->where(function ($q2) use ($term) {
                        $q2->whereRaw('MATCH(title, description) AGAINST(? IN BOOLEAN MODE)', [$term.'*'])
                            ->orWhere('code', 'like', '%'.$term.'%');
                    });
                })
                ->orderByDesc('id')
                ->paginate(20)
                ->withQueryString();
        }

        return view('issues.index', compact('projects', 'project', 'issues'))
            ->with('filters', [
                'status' => $project?->statuses->pluck('key')->all() ?? [],
                'priority' => [Issue::PRIORITY_LOW, Issue::PRIORITY_MEDIUM, Issue::PRIORITY_HIGH, Issue::PRIORITY_URGENT],
            ]);
    }
}
