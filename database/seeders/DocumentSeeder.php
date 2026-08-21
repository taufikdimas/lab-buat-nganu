<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Document;
use App\Models\DocumentShare;
use App\Models\DocumentShareLink;
use App\Models\Notification;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Project::with(['owner', 'memberships.user'])->get() as $project) {
            $active = $project->memberships->where('status', 'active')->values();
            foreach (['Project brief', 'Delivery roadmap', 'Meeting notes', 'Risk register'] as $index => $label) {
                $owner = $index === 3 && $active->count() > 1 ? $active[1]->user : $project->owner;
                $visibility = $index === 3 ? 'private' : 'project';
                $filename = str($project->name.'-'.$label)->slug().'.txt';
                $path = 'documents/'.$project->id.'/'.$filename;
                $content = $label."\n\nDemo file for {$project->name}.\nThis environment contains fictional training data only.";
                Storage::put($path, $content);
                $document = Document::updateOrCreate(['project_id' => $project->id, 'name' => $label], ['owner_id' => $owner->id, 'description' => 'Working document for '.$project->name, 'file_path' => $path, 'original_filename' => $filename, 'mime_type' => 'text/plain', 'size_bytes' => strlen($content), 'visibility' => $visibility]);
                Activity::create(['project_id' => $project->id, 'document_id' => $document->id, 'user_id' => $owner->id, 'action' => 'document.uploaded', 'description' => $owner->name.' uploaded '.$document->name, 'created_at' => now()->subDays(12 - $index)]);
            }
        }
        $shared = Document::where('visibility', 'private')->skip(1)->firstOrFail();
        $viewer = $shared->project->memberships()->active()->where('role', 'viewer')->with('user')->first()?->user ?? User::where('email', 'citra@workhub.test')->firstOrFail();
        DocumentShare::updateOrCreate(['document_id' => $shared->id, 'user_id' => $viewer->id], ['permission' => 'viewer', 'shared_by' => $shared->project->owner_id]);
        Notification::create(['user_id' => $viewer->id, 'type' => 'document.shared', 'data' => ['document_id' => $shared->id, 'message' => 'A private document was shared with you.'], 'created_at' => now()->subDay()]);
        $docs = Document::take(3)->get();
        DocumentShareLink::create(['document_id' => $docs[0]->id, 'token' => md5($docs[0]->id.'valid-demo'), 'created_by' => $docs[0]->project->owner_id]);
        DocumentShareLink::create(['document_id' => $docs[1]->id, 'token' => md5($docs[1]->id.'expired-demo'), 'created_by' => $docs[1]->project->owner_id, 'expires_at' => now()->subWeek()]);
        DocumentShareLink::create(['document_id' => $docs[2]->id, 'token' => md5($docs[2]->id.'revoked-demo'), 'created_by' => $docs[2]->project->owner_id, 'revoked_at' => now()->subDay()]);
    }
}
