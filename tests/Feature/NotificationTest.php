<?php

namespace Tests\Feature;

use App\Models\Issue;
use App\Models\Notification;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function setupProject(): array
    {
        $this->seed();
        $manager = User::factory()->create(['username' => 'manager']);
        $manager->givePermissionTo(['project.manage', 'issue.create', 'issue.edit', 'issue.view', 'comment.create', 'comment.edit']);
        $project = Project::create(['key' => 'HEL', 'name' => 'X', 'owner_id' => $manager->id]);
        ProjectMember::create(['project_id' => $project->id, 'user_id' => $manager->id, 'role' => ProjectMember::ROLE_LEAD]);

        $assignee = User::factory()->create(['username' => 'assignee']);
        $assignee->givePermissionTo(['issue.create', 'issue.edit', 'issue.view', 'comment.create', 'comment.edit']);
        ProjectMember::create(['project_id' => $project->id, 'user_id' => $assignee->id, 'role' => ProjectMember::ROLE_MEMBER]);

        $reporter = User::factory()->create(['username' => 'reporter']);
        $reporter->givePermissionTo(['issue.create', 'issue.edit', 'issue.view', 'comment.create', 'comment.edit']);
        ProjectMember::create(['project_id' => $project->id, 'user_id' => $reporter->id, 'role' => ProjectMember::ROLE_MEMBER]);

        return [$manager, $project, $assignee, $reporter];
    }

    public function test_assign_fires_notification_to_assignee(): void
    {
        [$manager, $project, $assignee] = $this->setupProject();

        $this->actingAs($manager)->post(route('issues.store'), [
            'project_id' => $project->id, 'title' => 'T', 'type' => 'Task',
            'status' => 'Open', 'priority' => 'low', 'assignee_id' => $assignee->id,
        ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $assignee->id,
            'notifiable_type' => User::class,
            'type' => 'App\\Notifications\\IssueAssigned',
        ]);
    }

    public function test_status_change_fires_to_reporter_and_assignee(): void
    {
        [$manager, $project, $assignee, $reporter] = $this->setupProject();
        $issue = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'T',
            'type' => 'Task', 'status' => 'Open', 'priority' => 'low',
            'reporter_id' => $reporter->id, 'assignee_id' => $assignee->id,
        ]);

        $this->actingAs($manager)->post(route('issues.status', $issue), [
            'status' => 'Done', 'order' => 0,
        ]);

        // reporter + assignee notified (not the actor = manager)
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $assignee->id, 'type' => 'App\\Notifications\\IssueStatusChanged',
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $reporter->id, 'type' => 'App\\Notifications\\IssueStatusChanged',
        ]);
    }

    public function test_mention_fires_to_mentioned_user(): void
    {
        [$manager, $project, $assignee, $reporter] = $this->setupProject();
        $issue = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'T',
            'type' => 'Task', 'status' => 'Open', 'priority' => 'low', 'reporter_id' => $manager->id,
        ]);

        $this->actingAs($manager)->post(route('issues.comments.store', $issue), [
            'body' => '<p>hey @assignee please check</p>',
        ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $assignee->id, 'type' => 'App\\Notifications\\Mentioned',
        ]);
    }

    public function test_actor_does_not_get_self_notification(): void
    {
        [$manager, $project, $assignee] = $this->setupProject();

        // manager assigns to self -> no notification to self
        $this->actingAs($manager)->post(route('issues.store'), [
            'project_id' => $project->id, 'title' => 'T', 'type' => 'Task',
            'status' => 'Open', 'priority' => 'low', 'assignee_id' => $manager->id,
        ]);

        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $manager->id]);
    }
}
