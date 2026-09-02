<?php

namespace Tests\Feature\PhaseD;

use App\Models\Issue;
use App\Models\ProjectMember;
use App\Models\StatusTransition;

class WorkflowTest extends PhaseDTestCase
{
    /** @test */
    public function test_strict_transition_requires_role(): void
    {
        [$manager, $project, $user] = $this->setupProject(ProjectMember::ROLE_VIEWER);

        $st = $project->statuses()->orderBy('order')->get();
        $open = $st->firstWhere('key', 'open');
        $done = $st->firstWhere('is_closed', true);

        // Define a transition from Open -> Done that requires 'lead' role.
        StatusTransition::create([
            'project_id' => $project->id,
            'from_status_id' => $open->id,
            'to_status_id' => $done->id,
            'required_role' => ProjectMember::ROLE_LEAD,
        ]);

        $issue = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'Strict',
            'type' => 'task', 'status' => 'open', 'priority' => 'low', 'reporter_id' => $user->id,
        ]);

        // viewer cannot transition to done
        $this->assertFalse($issue->canTransitionTo('done', $user));

        // lead can
        $this->assertTrue($issue->canTransitionTo('done', $manager));
    }
}
