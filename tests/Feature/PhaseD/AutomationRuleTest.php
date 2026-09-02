<?php

namespace Tests\Feature\PhaseD;

use App\Models\AutomationRule;
use App\Models\Issue;

class AutomationRuleTest extends PhaseDTestCase
{
    /** @test */
    public function test_automation_rule_fires_on_status_change(): void
    {
        [$manager, $project, $user] = $this->setupProject();
        $issue = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'Auto test',
            'type' => 'task', 'status' => 'open', 'priority' => 'low', 'reporter_id' => $user->id,
        ]);

        $rule = AutomationRule::create([
            'project_id' => $project->id,
            'name' => 'Assign on done',
            'event' => 'issue:status_changed',
            'conditions' => [['field' => 'status', 'value' => 'done']],
            'actions' => [['type' => 'assign', 'value' => $manager->id]],
            'enabled' => true,
        ]);

        // Change status to 'done' -> should trigger the rule
        $issue->update(['status' => 'done']);

        $this->assertEquals($manager->id, $issue->fresh()->assignee_id);
        $this->assertDatabaseHas('automation_rule_logs', ['automation_rule_id' => $rule->id, 'issue_id' => $issue->id]);
    }

    /** @test */
    public function test_automation_rule_change_status_action(): void
    {
        [$manager, $project, $user] = $this->setupProject();
        $issue = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'Auto status',
            'type' => 'task', 'status' => 'open', 'priority' => 'low', 'reporter_id' => $user->id,
        ]);

        AutomationRule::create([
            'project_id' => $project->id, 'name' => 'Resolve on close',
            'event' => 'issue:status_changed', 'enabled' => true,
            'conditions' => [['field' => 'status', 'value' => 'closed']],
            'actions' => [['type' => 'change_status', 'value' => 'resolved']],
        ]);

        $issue->update(['status' => 'closed']);

        $this->assertEquals('resolved', $issue->fresh()->status);
    }

    /** @test */
    public function test_automation_logs_visible_in_issue_timeline(): void
    {
        [$manager, $project, $user] = $this->setupProject();

        $rule = AutomationRule::create([
            'project_id' => $project->id,
            'name' => 'Auto-resolve',
            'event' => 'issue:status_changed',
            'trigger' => 'status',
            'conditions' => [['field' => 'status', 'operator' => 'changed_to', 'value' => 'done']],
            'actions' => ['change_status' => 'resolved'],
            'enabled' => true,
        ]);

        $issue = Issue::create([
            'project_id' => $project->id, 'code' => 'TM-8', 'title' => 'Audit log',
            'type' => 'task', 'status' => 'open', 'priority' => 'low', 'reporter_id' => $user->id,
        ]);

        // fire automation -> creates a log
        $this->actingAs($manager)->post(route('issues.status', $issue), ['status' => 'done']);

        $issue->refresh();
        $this->assertCount(1, $issue->automationLogs);
        $this->assertDatabaseHas('automation_rule_logs', ['issue_id' => $issue->id, 'status' => 'completed', 'automation_rule_id' => $rule->id]);

        // visible in show view
        $res = $this->actingAs($user)->get(route('issues.show', $issue));
        $res->assertOk()->assertSee('Auto-resolve')->assertSee('completed');
    }
}
