<?php

namespace App\Observers;

use App\Models\Project;
use Illuminate\Support\Facades\Request;

class ProjectObserver
{
    public function created(Project $project): void
    {
        $project->seedDefaultFields();
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($project)->log('project_created');
    }

    public function updated(Project $project): void
    {
        $dirty = $project->getDirty();
        $old = [];
        foreach ($dirty as $k => $v) {
            $old[$k] = $project->getOriginal($k);
        }
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
            'old' => $old, 'new' => $dirty,
        ])->performedOn($project)->log('project_updated');
    }

    public function deleted(Project $project): void
    {
        // ponytail: root-cause cleanup — project folder + all descendant issue folders.
        deleteStorageFolder('projects/'.$project->folder());
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($project)->log('project_deleted');
    }

    public function restored(Project $project): void
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($project)->log('project_restored');
    }
}
