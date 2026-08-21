<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Document;
use App\Models\Project;
use App\Models\User;
use Livewire\Component;

class AdminDashboard extends Component
{
    public function render()
    {
        return view('livewire.admin.admin-dashboard', ['counts' => ['users' => User::count(), 'projects' => Project::withTrashed()->count(), 'documents' => Document::withTrashed()->count(), 'suspended' => User::where('status', 'suspended')->count()], 'logs' => AuditLog::with('actor')->latest()->take(8)->get()]);
    }
}
