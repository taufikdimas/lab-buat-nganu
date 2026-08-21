<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Project::class);
        $query = Project::query();
        if (! $request->user()->isAdmin()) {
            $query->whereHas('memberships', fn ($q) => $q->active()->where('user_id', $request->user()->id));
        }

        return $query->withCount('memberships')->paginate();
    }

    public function store(Request $request)
    {
        $this->authorize('create', Project::class);
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string', 'max:5000']]);
        $project = Project::create($data + ['owner_id' => $request->user()->id]);
        $project->memberships()->create(['user_id' => $request->user()->id, 'role' => 'owner', 'status' => 'active', 'joined_at' => now()]);
        Activity::create(['project_id' => $project->id, 'user_id' => $request->user()->id, 'action' => 'project.created', 'description' => $request->user()->name.' created '.$project->name]);

        return response()->json($project, 201);
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        return $project->load(['owner', 'memberships.user']);
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        $project->update($request->validate(['name' => ['sometimes', 'string', 'max:255'], 'description' => ['nullable', 'string', 'max:5000'], 'status' => ['sometimes', 'in:active,archived']]));

        return $project;
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();

        return response()->noContent();
    }
}
