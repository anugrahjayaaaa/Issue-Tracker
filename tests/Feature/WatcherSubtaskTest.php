<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Issue;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WatcherSubtaskTest extends TestCase
{
    use RefreshDatabase;

    private function seedProject(): array
    {
        Notification::fake();
        Storage::fake('public');
        $this->seed();
        $manager = User::factory()->create();
        $manager->givePermissionTo(['project.manage', 'issue.create', 'issue.edit', 'issue.delete', 'issue.view']);
        $member = User::factory()->create();
        $member->givePermissionTo(['issue.create', 'issue.edit', 'issue.view', 'comment.create']);
        $project = Project::create(['key' => 'HEL', 'name' => 'Helpdesk', 'owner_id' => $manager->id]);
        $project->members()->create(['user_id' => $manager->id, 'role' => 'lead']);
        $project->members()->create(['user_id' => $member->id, 'role' => 'member']);
        $project->seedDefaultFields();

        return [$manager, $member, $project];
    }

    public function test_reporter_and_assignee_auto_subscribe_on_create(): void
    {
        [$manager, $member, $project] = $this->seedProject();
        $this->actingAs($manager);

        $issue = Issue::create([
            'project_id' => $project->id,
            'code' => 'HEL-1',
            'title' => 'Bug',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'high',
            'reporter_id' => $manager->id,
            'assignee_id' => $member->id,
        ]);

        $this->assertTrue($issue->watchers->contains($manager->id));
        $this->assertTrue($issue->watchers->contains($member->id));
    }

    public function test_commenter_auto_subscribes(): void
    {
        [$manager, $member, $project] = $this->seedProject();
        $issue = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'T',
            'type' => 'task', 'status' => 'open', 'priority' => 'high', 'reporter_id' => $manager->id,
        ]);
        $this->actingAs($member);
        $this->post(route('issues.comments.store', $issue), ['body' => 'hi'])->assertRedirect();

        $this->assertTrue($issue->fresh()->watchers->contains($member->id));
    }

    public function test_status_change_notifies_watchers(): void
    {
        [$manager, $member, $project] = $this->seedProject();
        $issue = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'T',
            'type' => 'task', 'status' => 'open', 'priority' => 'high', 'reporter_id' => $manager->id,
        ]);
        $issue->syncWatchers([$member->id]); // watcher who is neither reporter nor assignee

        $this->actingAs($manager);
        $this->post(route('issues.status', $issue), ['status' => 'in-progress'])->assertRedirect();

        Notification::assertSentTo($member, \App\Notifications\IssueStatusChanged::class);
    }

    public function test_watch_and_unwatch_toggle(): void
    {
        [$manager, $member, $project] = $this->seedProject();
        $issue = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'T',
            'type' => 'task', 'status' => 'open', 'priority' => 'high', 'reporter_id' => $manager->id,
        ]);
        $this->actingAs($member);
        $this->post(route('issues.watch', $issue))->assertRedirect();
        $this->assertTrue($issue->fresh()->watchers->contains($member->id));

        $this->post(route('issues.unwatch', $issue))->assertRedirect();
        $this->assertFalse($issue->fresh()->watchers->contains($member->id));
    }

    public function test_parent_cycle_rejected_on_update(): void
    {
        [$manager, $member, $project] = $this->seedProject();
        $parent = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'Parent',
            'type' => 'task', 'status' => 'open', 'priority' => 'high', 'reporter_id' => $manager->id,
        ]);
        $child = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-2', 'title' => 'Child',
            'type' => 'task', 'status' => 'open', 'priority' => 'high', 'reporter_id' => $manager->id, 'parent_id' => $parent->id,
        ]);

        $this->actingAs($manager);
        $this->put(route('issues.update', $parent), [
            'title' => $parent->title, 'type' => 'task', 'status' => 'open', 'priority' => 'high', 'parent_id' => $child->id,
        ])->assertRedirect(); // wouldCreateCycle -> redirect back with error flash

        // Cycle guard held: parent did NOT become a child of its own sub-task.
        $this->assertNull($parent->fresh()->parent_id);
        $this->assertEquals($parent->id, $child->fresh()->parent_id);
    }

    public function test_subtask_progress_counts_closed(): void
    {
        [$manager, $member, $project] = $this->seedProject();
        $parent = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'P',
            'type' => 'task', 'status' => 'open', 'priority' => 'high', 'reporter_id' => $manager->id,
        ]);
        $doneStatus = $project->statuses()->where('is_closed', true)->first();
        Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-2', 'title' => 'C1',
            'type' => 'task', 'status' => 'open', 'priority' => 'high', 'reporter_id' => $manager->id, 'parent_id' => $parent->id,
        ]);
        $c2 = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-3', 'title' => 'C2',
            'type' => 'task', 'status' => $doneStatus->key, 'priority' => 'high', 'reporter_id' => $manager->id, 'parent_id' => $parent->id,
        ]);

        $prog = $parent->subtaskProgress();
        $this->assertEquals(2, $prog['total']);
        $this->assertEquals(1, $prog['done']);
    }

    public function test_attachment_delete_removes_file(): void
    {
        [$manager, $member, $project] = $this->seedProject();
        $issue = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'T',
            'type' => 'task', 'status' => 'open', 'priority' => 'high', 'reporter_id' => $manager->id,
        ]);
        $path = $issue->code.'/attachments/note.txt';
        Storage::disk('public')->put($path, 'x');
        $att = Attachment::create(['issue_id' => $issue->id, 'user_id' => $manager->id, 'path' => $path, 'mime' => 'text/plain', 'size' => 1]);

        $this->actingAs($manager);
        $this->delete(route('issues.attachments.destroy', [$issue, $att]))->assertRedirect();

        Storage::disk('public')->assertMissing($path);
        $this->assertDatabaseMissing('attachments', ['id' => $att->id]);
    }
}
