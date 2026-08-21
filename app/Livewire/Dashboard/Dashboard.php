<?php

namespace App\Livewire\Dashboard;

use App\Models\Activity;
use App\Models\Document;
use App\Models\Project;
use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return view('livewire.dashboard.dashboard', ['stats' => [
                ['label' => 'Users', 'value' => User::count(), 'icon' => 'users'],
                ['label' => 'Projects', 'value' => Project::withTrashed()->count(), 'icon' => 'folder'],
                ['label' => 'Documents', 'value' => Document::withTrashed()->count(), 'icon' => 'document-text'],
                ['label' => 'Suspended', 'value' => User::where('status', 'suspended')->count(), 'icon' => 'no-symbol'],
            ], 'projects' => Project::with('owner')->latest()->take(5)->get(), 'activities' => Activity::with('user')->latest()->take(6)->get()]);
        }
        $projectIds = $user->memberships()->active()->pluck('project_id');

        return view('livewire.dashboard.dashboard', ['stats' => [
            ['label' => 'My projects', 'value' => $projectIds->count(), 'icon' => 'folder'],
            ['label' => 'Pending invites', 'value' => $user->memberships()->where('status', 'pending')->count(), 'icon' => 'user-plus'],
            ['label' => 'Documents', 'value' => Document::whereIn('project_id', $projectIds)->count(), 'icon' => 'document-text'],
            ['label' => 'Unread', 'value' => $user->workNotifications()->whereNull('read_at')->count(), 'icon' => 'bell'],
        ], 'projects' => Project::whereIn('id', $projectIds)->with('owner')->latest()->take(5)->get(), 'activities' => Activity::whereIn('project_id', $projectIds)->with('user')->latest()->take(6)->get()]);
    }
}
