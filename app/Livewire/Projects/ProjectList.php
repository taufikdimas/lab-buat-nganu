<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Livewire\Component;
use Livewire\WithPagination;

class ProjectList extends Component
{
    use WithPagination;

    public string $status = 'all';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();
        $query = Project::query()
            ->select(['id', 'name', 'description', 'owner_id', 'status', 'created_at'])
            ->with('owner:id,name')
            ->withCount('memberships');

        if (! $user->isAdmin()) {
            $query
                ->whereHas('memberships', fn ($q) => $q->active()->where('user_id', $user->id))
                ->with(['memberships' => fn ($q) => $q->select(['id', 'project_id', 'user_id', 'role', 'status'])->where('user_id', $user->id)]);
        }

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return view('livewire.projects.project-list', ['projects' => $query->latest()->paginate(12)]);
    }
}
