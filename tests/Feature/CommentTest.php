<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Issue;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    private function setupProject(string $role = ProjectMember::ROLE_MEMBER): array
    {
        $this->seed();
        $manager = User::factory()->create();
        $manager->givePermissionTo(['project.manage', 'issue.view', 'comment.create', 'comment.edit', 'comment.delete']);
        $project = Project::create(['key' => 'HEL', 'name' => 'X', 'owner_id' => $manager->id]);
        $this->member($manager, $project, ProjectMember::ROLE_LEAD);

        $user = User::factory()->create();
        $user->givePermissionTo(['issue.view', 'comment.create', 'comment.edit', 'comment.delete']);
        $this->member($user, $project, $role);

        $issue = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'T',
            'type' => 'task', 'status' => 'open', 'priority' => 'low', 'reporter_id' => $user->id,
        ]);

        return [$manager, $project, $user, $issue];
    }

    private function member(User $user, Project $project, string $role): void
    {
        ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => $role]);
    }

    public function test_member_can_post_comment(): void
    {
        [$manager, $project, $user, $issue] = $this->setupProject();

        $this->actingAs($user)->post(route('issues.comments.store', $issue), [
            'body' => '<p>nice <strong>work</strong></p>',
        ])->assertRedirect();

        $this->assertDatabaseHas('comments', ['issue_id' => $issue->id]);
        $this->assertEquals('<p>nice <strong>work</strong></p>', Comment::first()->body);
    }

    public function test_comment_body_sanitized(): void
    {
        [$manager, $project, $user, $issue] = $this->setupProject();

        $this->actingAs($user)->post(route('issues.comments.store', $issue), [
            'body' => '<script>alert(1)</script><p>ok</p>',
        ]);

        $this->assertStringNotContainsString('<script>', Comment::first()->body);
    }

    public function test_owner_can_edit_and_delete(): void
    {
        [$manager, $project, $user, $issue] = $this->setupProject();
        $comment = Comment::create(['issue_id' => $issue->id, 'user_id' => $user->id, 'body' => '<p>old</p>']);

        $this->actingAs($user)->put(route('issues.comments.update', $comment), ['body' => '<p>new</p>'])
            ->assertRedirect();
        $this->assertEquals('<p>new</p>', $comment->fresh()->body);

        $this->actingAs($user)->delete(route('issues.comments.destroy', $comment))
            ->assertRedirect();
        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_other_member_cannot_edit(): void
    {
        [$manager, $project, $user, $issue] = $this->setupProject();
        $other = User::factory()->create();
        $other->givePermissionTo(['issue.view', 'comment.create', 'comment.edit', 'comment.delete']);
        $this->member($other, $project, ProjectMember::ROLE_MEMBER);
        $comment = Comment::create(['issue_id' => $issue->id, 'user_id' => $user->id, 'body' => '<p>old</p>']);

        $this->actingAs($other)->put(route('issues.comments.update', $comment), ['body' => '<p>hack</p>'])
            ->assertForbidden();
    }

    public function test_image_attachment_stored_on_public_disk(): void
    {
        Storage::fake('public');
        [$manager, $project, $user, $issue] = $this->setupProject();

        $this->actingAs($user)->post(route('issues.attachments.store', $issue), [
            'file' => UploadedFile::fake()->image('shot.png', 50, 50),
        ])->assertRedirect();

        $this->assertDatabaseHas('attachments', ['issue_id' => $issue->id]);
        Storage::disk('public')->assertExists(Attachment::first()->path);
    }
}
