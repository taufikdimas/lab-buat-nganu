<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentShareLink;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkHubPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_open_core_workspace_pages(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['name' => 'Alpha Workspace', 'description' => 'A test project.', 'owner_id' => $user->id, 'status' => 'active']);
        ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 'owner', 'status' => 'active', 'joined_at' => now()]);
        $document = Document::create(['project_id' => $project->id, 'owner_id' => $user->id, 'name' => 'Project Brief', 'file_path' => 'documents/test.txt', 'original_filename' => 'test.txt', 'mime_type' => 'text/plain', 'size_bytes' => 42, 'visibility' => 'project']);

        $this->actingAs($user)->get('/projects')->assertOk()->assertSee('Alpha Workspace');
        $this->get("/projects/{$project->id}")->assertOk()->assertSee('Project Brief');
        $this->get("/projects/{$project->id}/documents/{$document->id}")->assertOk()->assertSee('Discussion');
        $this->get('/search?q=Alpha')->assertOk()->assertSee('Alpha Workspace');
        $this->get('/notifications')->assertOk();
        $this->get('/profile')->assertOk();
    }

    public function test_admin_can_open_administration_pages(): void
    {
        $admin = User::factory()->create(['system_role' => 'admin']);

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->get('/admin/users')->assertOk();
        $this->get('/admin/projects')->assertOk();
        $this->get('/admin/documents')->assertOk();
        $this->get('/admin/audit-logs')->assertOk();
        $this->get('/admin/settings')->assertOk();
    }

    public function test_valid_public_share_link_renders_document(): void
    {
        $owner = User::factory()->create();
        $project = Project::create(['name' => 'Shared Workspace', 'owner_id' => $owner->id, 'status' => 'active']);
        $document = Document::create(['project_id' => $project->id, 'owner_id' => $owner->id, 'name' => 'Shared Brief', 'file_path' => 'documents/shared.txt', 'original_filename' => 'shared.txt', 'mime_type' => 'text/plain', 'size_bytes' => 42, 'visibility' => 'project']);
        $link = DocumentShareLink::create(['document_id' => $document->id, 'token' => str_repeat('a', 32), 'created_by' => $owner->id]);

        $this->get('/share/'.$link->token)->assertOk()->assertSee('Shared Brief');
    }
}
