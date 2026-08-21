<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            ['Website Redesign', 'Modernisasi portal perusahaan dan design system.', 'arif@workhub.test', 'active'],
            ['Mobile Banking 2.0', 'Discovery dan delivery aplikasi mobile generasi baru.', 'nadia@workhub.test', 'active'],
            ['Q4 Campaign', 'Kampanye pemasaran terpadu untuk kuartal keempat.', 'bima@workhub.test', 'active'],
            ['Security Readiness', 'Persiapan audit keamanan dan remediasi temuan.', 'sari@workhub.test', 'active'],
            ['Data Migration', 'Migrasi data legacy ke platform analytics baru.', 'dewa@workhub.test', 'active'],
            ['Legacy Intranet', 'Arsip proyek intranet lama.', 'maya@workhub.test', 'archived'],
        ];
        $emails = User::where('system_role', 'user')->where('status', 'active')->pluck('id', 'email');
        $memberEmails = $emails->keys()->values();
        foreach ($definitions as $index => [$name, $description, $ownerEmail, $status]) {
            $ownerId = $emails[$ownerEmail];
            $project = Project::updateOrCreate(['name' => $name], ['description' => $description, 'owner_id' => $ownerId, 'status' => $status]);
            ProjectMember::updateOrCreate(['project_id' => $project->id, 'user_id' => $ownerId], ['role' => 'owner', 'status' => 'active', 'joined_at' => now()->subMonths(3)]);
            for ($offset = 1; $offset <= 4; $offset++) {
                $email = $memberEmails[($index + $offset) % $memberEmails->count()];
                if ($email === $ownerEmail) {
                    $email = $memberEmails[($index + $offset + 1) % $memberEmails->count()];
                }
                $pending = $index === 0 && $offset === 4;
                ProjectMember::updateOrCreate(['project_id' => $project->id, 'user_id' => $emails[$email]], ['role' => $offset <= 2 ? 'editor' : 'viewer', 'status' => $pending ? 'pending' : 'active', 'invited_by' => $ownerId, 'invited_at' => now()->subDays(30 - $offset), 'joined_at' => $pending ? null : now()->subDays(25 - $offset)]);
            }
            Activity::create(['project_id' => $project->id, 'user_id' => $ownerId, 'action' => 'project.created', 'description' => User::find($ownerId)->name.' created '.$project->name, 'created_at' => now()->subMonths(3)]);
        }
    }
}
