<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class ProjectTable extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = 'all';

    public function delete(int $id): void
    {
        $project = Project::withTrashed()->findOrFail($id);
        abort_if($project->trashed(), 422, 'This project is already deleted.');
        $project->delete();
        $this->audit('content.deleted', $project->id);
    }

    public function restore(int $id): void
    {
        $project = Project::onlyTrashed()->findOrFail($id);
        $project->restore();
        $this->audit('content.restored', $project->id);
    }

    public function forceDelete(int $id): void
    {
        $project = Project::onlyTrashed()->findOrFail($id);
        $documentPaths = $project->documents()->withTrashed()->pluck('file_path')->all();
        $project->forceDelete();
        Storage::delete($documentPaths);
        $this->audit('content.force_deleted', $id);
    }

    public function render()
    {
        $projects = Project::withTrashed()
            ->with('owner:id,name')
            ->withCount([
                'memberships',
                'documents' => fn ($query) => $query->withTrashed(),
            ])
            ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->when($this->status !== 'all', fn ($query) => $query->where('status', $this->status))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.project-table', compact('projects'));
    }

    private function audit(string $action, int $projectId): void
    {
        AuditLog::create(['actor_id' => auth()->id(), 'action' => $action, 'target_type' => 'Project', 'target_id' => $projectId, 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()]);
    }
}
