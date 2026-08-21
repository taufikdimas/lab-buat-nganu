<?php

namespace App\Livewire\Projects;

use App\Models\Activity;
use App\Models\Project;
use Livewire\Component;

class ProjectForm extends Component
{
    public string $name = '';

    public string $description = '';

    public function save()
    {
        $this->authorize('create', Project::class);
        $data = $this->validate(['name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string', 'max:5000']]);
        $project = Project::create($data + ['owner_id' => auth()->id(), 'status' => 'active']);
        $project->memberships()->create(['user_id' => auth()->id(), 'role' => 'owner', 'status' => 'active', 'joined_at' => now()]);
        Activity::create(['project_id' => $project->id, 'user_id' => auth()->id(), 'action' => 'project.created', 'description' => auth()->user()->name.' created '.$project->name]);

        return $this->redirectRoute('projects.show', $project, navigate: true);
    }

    public function render()
    {
        return view('livewire.projects.project-form');
    }
}
