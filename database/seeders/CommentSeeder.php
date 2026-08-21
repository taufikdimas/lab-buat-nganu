<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Comment;
use App\Models\Document;
use App\Models\Notification;
use App\Models\User;
use App\Services\MarkdownLiteService;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $renderer = app(MarkdownLiteService::class);
        foreach (Document::with('project.memberships.user')->take(10)->get() as $index => $document) {
            $author = $document->project->memberships->where('status', 'active')->values()->get(1)?->user ?? $document->owner;
            $body = $index === 0 ? 'Looks **ready** to review. See *notes* and [guidance](https://example.com).' : 'I reviewed this document and left the latest feedback here.';
            Comment::create(['document_id' => $document->id, 'user_id' => $author->id, 'body_raw' => $body, 'body_rendered' => $renderer->render($body)]);
        }
        $admin = User::where('email', 'admin@workhub.test')->firstOrFail();
        foreach (User::where('status', 'active')->where('system_role', 'user')->take(5)->get() as $index => $user) {
            Notification::create(['user_id' => $user->id, 'type' => 'project.invited', 'data' => ['message' => 'You have a new project invitation.'], 'read_at' => $index % 2 ? now() : null, 'created_at' => now()->subHours($index + 2)]);
        }
        AuditLog::create(['actor_id' => $admin->id, 'action' => 'settings.updated', 'target_type' => 'SystemSetting', 'meta' => ['key' => 'max_upload_size_mb'], 'ip_address' => '127.0.0.1', 'user_agent' => 'WorkHub Seeder']);
        AuditLog::create(['actor_id' => $admin->id, 'action' => 'user.reviewed', 'target_type' => 'User', 'target_id' => User::where('email', 'suspended@workhub.test')->value('id'), 'ip_address' => '127.0.0.1', 'user_agent' => 'WorkHub Seeder']);
    }
}
