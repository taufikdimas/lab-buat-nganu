<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Project;
use Livewire\Component;
use Livewire\WithPagination;

class ProjectTable extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = 'all';

    public function delete(int $id): void
    {
        $project = Project::findOrFail($id);
        $project->delete();
        AuditLog::create(['actor_id' => auth()->id(), 'action' => 'content.deleted', 'target_type' => 'Project', 'target_id' => $project->id, 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()]);
    }

    public function render()
    {
        $projects = Project::with('owner')->withCount(['memberships', 'documents'])->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))->latest()->paginate(15);

        return view('livewire.admin.project-table', compact('projects'));
    }
}
