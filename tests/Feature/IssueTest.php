<?php

namespace Tests\Feature;

use App\Models\Issue;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\StatusTransition;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IssueTest extends TestCase
{
    use RefreshDatabase;

    private function member(User $user, Project $project, string $role = ProjectMember::ROLE_MEMBER): void
    {
        ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => $role]);
    }

    private function seedAndProject(string $role = ProjectMember::ROLE_MEMBER): array
    {
        $this->seed();
        $manager = User::factory()->create();
        $manager->givePermissionTo(['project.manage', 'issue.create', 'issue.edit', 'issue.delete', 'issue.view']);
        $project = Project::create(['key' => 'HEL', 'name' => 'Helpdesk', 'owner_id' => $manager->id]);
        $this->member($manager, $project, ProjectMember::ROLE_LEAD);

        $user = User::factory()->create();
        $user->givePermissionTo(['issue.create', 'issue.edit', 'issue.delete', 'issue.view']);
        $this->member($user, $project, $role);

        return [$manager, $project, $user];
    }

    public function test_member_can_create_issue_with_generated_code(): void
    {
        [$manager, $project, $user] = $this->seedAndProject();

        $response = $this->actingAs($user)->post(route('issues.store'), [
            'project_id' => $project->id,
            'title' => 'Login broken',
            'type' => 'Bug',
            'status' => 'Open',
            'priority' => 'high',
        ]);

        $response->assertRedirect(route('issues.index', ['project_id' => $project->id]));
        $this->assertDatabaseHas('issues', ['project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'Login broken']);
    }

    public function test_issue_code_increments_per_project(): void
    {
        [$manager, $project, $user] = $this->seedAndProject();
        $this->actingAs($user)->post(route('issues.store'), ['project_id' => $project->id, 'title' => 'A', 'type' => 'Task', 'status' => 'Open', 'priority' => 'low']);
        $this->actingAs($user)->post(route('issues.store'), ['project_id' => $project->id, 'title' => 'B', 'type' => 'Task', 'status' => 'Open', 'priority' => 'low']);

        $this->assertDatabaseHas('issues', ['code' => 'HEL-1']);
        $this->assertDatabaseHas('issues', ['code' => 'HEL-2']);
    }

    public function test_status_transition_blocked_when_not_in_workflow(): void
    {
        [$manager, $project, $user] = $this->seedAndProject();
        $open = $project->statuses()->where('name', 'Open')->first();
        $inProgress = $project->statuses()->where('name', 'In Progress')->first();
        $done = $project->statuses()->where('name', 'Done')->first();
        // workflow: Open -> In Progress only (Done not reachable from Open)
        StatusTransition::create(['project_id' => $project->id, 'from_status_id' => $open->id, 'to_status_id' => $inProgress->id]);

        $issue = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'X',
            'type' => 'Task', 'status' => 'Open', 'priority' => 'low', 'reporter_id' => $user->id,
        ]);

        // allowed
        $this->actingAs($user)->post(route('issues.status', $issue), ['status' => 'In Progress', 'order' => 0])
            ->assertRedirect();
        $issue->refresh();
        $this->assertSame('In Progress', $issue->status);

        // blocked: In Progress -> Done has no rule
        $this->actingAs($user)->post(route('issues.status', $issue), ['status' => 'Done', 'order' => 0])
            ->assertRedirect();
        $issue->refresh();
        $this->assertSame('In Progress', $issue->status, 'status must not change when transition disallowed');
    }

    public function test_show_renders_with_timeline_and_comments(): void
    {
        [$manager, $project, $user] = $this->seedAndProject();
        $issue = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'X',
            'type' => 'Task', 'status' => 'Open', 'priority' => 'low', 'reporter_id' => $user->id,
        ]);
        Comment::create(['issue_id' => $issue->id, 'user_id' => $user->id, 'body' => 'hi']);

        $this->actingAs($user)
            ->get(route('issues.show', $issue))
            ->assertOk()
            ->assertSee('Activity')
            ->assertSee('hi');
    }

    public function test_index_sorts_by_query_and_renders_due_date_column(): void
    {
        [$manager, $project, $user] = $this->seedAndProject();
        Issue::create(['project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'A', 'type' => 'Task', 'status' => 'Open', 'priority' => 'low', 'reporter_id' => $user->id, 'due_date' => '2026-01-01']);
        Issue::create(['project_id' => $project->id, 'code' => 'HEL-2', 'title' => 'B', 'type' => 'Task', 'status' => 'Open', 'priority' => 'low', 'reporter_id' => $user->id, 'due_date' => '2026-02-01']);

        $this->actingAs($user)
            ->get(route('issues.index', ['project_id' => $project->id, 'sort' => 'due_date', 'dir' => 'desc']))
            ->assertOk()
            ->assertSee('2026-02-01')   // newest first
            ->assertSee('bi-caret-down-fill'); // active sort indicator
    }

    public function test_index_filter_bar_renders_reset_when_filtered(): void
    {
        [$manager, $project, $user] = $this->seedAndProject();
        Issue::create(['project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'A', 'type' => 'Task', 'status' => 'Open', 'priority' => 'low', 'reporter_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('issues.index', ['project_id' => $project->id, 'status' => 'Open']))
            ->assertOk()
            ->assertSee('form-select')      // filter selects present
            ->assertSee('btn-outline-secondary'); // reset button shown
    }

    public function test_non_member_cannot_view_board(): void
    {
        $this->seed();
        $outsider = User::factory()->create();
        $outsider->givePermissionTo('issue.view');
        $manager = User::factory()->create();
        $manager->givePermissionTo('project.manage');
        $project = Project::create(['key' => 'HEL', 'name' => 'X', 'owner_id' => $manager->id]);

        $this->actingAs($outsider)->get(route('issues.board', ['project_id' => $project->id]))
            ->assertForbidden();
    }

    public function test_rich_text_description_is_sanitized(): void
    {
        [$manager, $project, $user] = $this->seedAndProject();
        $this->actingAs($user)->post(route('issues.store'), [
            'project_id' => $project->id, 'title' => 'X', 'type' => 'Task',
            'status' => 'Open', 'priority' => 'low',
            'description' => '<script>alert(1)</script><p>ok <strong>bold</strong></p>',
        ]);

        $issue = Issue::where('code', 'HEL-1')->first();
        $this->assertStringNotContainsString('<script>', $issue->description);
        $this->assertStringContainsString('<strong>bold</strong>', $issue->description);
    }

    public function test_store_succeeds_without_status_field(): void
    {
        [$manager, $project, $user] = $this->seedAndProject();
        $this->actingAs($user)->post(route('issues.store'), [
            'project_id' => $project->id, 'title' => 'No status', 'type' => 'Task', 'priority' => 'low',
        ])->assertRedirect(route('issues.index', ['project_id' => $project->id]));

        $issue = Issue::where('code', 'HEL-1')->first();
        $this->assertNotNull($issue);
        $this->assertSame('Open', $issue->status);
    }

    public function test_image_upload_is_scoped_to_issue_folder_and_returns_url(): void
    {
        Storage::fake('public');
        [$manager, $project, $user] = $this->seedAndProject();
        $issue = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'T',
            'type' => 'Task', 'status' => 'Open', 'priority' => 'low', 'reporter_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->post(route('issues.image.upload', $issue), [
                'file' => UploadedFile::fake()->image('pic.png', 10, 10),
            ]);

        $response->assertOk()->assertJsonStructure(['location']);
        Storage::disk('public')->assertExists(
            'projects/'.$project->folder().'/issues/'.$issue->code.'/description/'.$this->filenameFrom($response)
        );
    }

    public function test_image_upload_blocked_when_quota_exceeded(): void
    {
        Storage::fake('public');
        [$manager, $project, $user] = $this->seedAndProject(ProjectMember::ROLE_LEAD);
        $issue = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'T',
            'type' => 'Task', 'status' => 'Open', 'priority' => 'low', 'reporter_id' => $user->id,
        ]);

        $free = \App\Models\Plan::where('slug', 'free')->first();
        $limits = array_merge($free->limits ?? [], ['max_storage_mb' => 1]); // 1 MB cap
        \App\Models\Plan::where('slug', 'free')->update(['limits' => $limits]);

        // Pre-fill the issue folder with ~1.5 MB so a new upload exceeds the 1 MB cap.
        Storage::disk('public')->put(
            'projects/'.$project->folder().'/issues/'.$issue->code.'/description/seed.bin',
            str_repeat('x', 1536 * 1024)
        );

        $this->actingAs($user)
            ->post(route('issues.image.upload', $issue), ['file' => UploadedFile::fake()->image('pic.png', 10, 10)])
            ->assertInvalid('file');
    }

    public function test_member_can_patch_assignee_and_due_date(): void
    {
        [$manager, $project, $user] = $this->seedAndProject();
        $issue = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'T',
            'type' => 'Task', 'status' => 'Open', 'priority' => 'low', 'reporter_id' => $user->id,
        ]);

        // member (no issue.edit permission) patches only meta fields
        $user->revokePermissionTo('issue.edit');
        $this->assertFalse($user->can('issue.edit'));

        $this->actingAs($user)
            ->put(route('issues.update', $issue), ['assignee_id' => $manager->id, 'due_date' => '2026-09-15'])
            ->assertRedirect(route('issues.show', $issue));

        $issue->refresh();
        $this->assertEquals($manager->id, $issue->assignee_id);
        $this->assertEquals('2026-09-15', $issue->due_date->format('Y-m-d'));
    }

    private function filenameFrom($response): string
    {
        return basename(json_decode($response->getContent())->location);
    }
}
