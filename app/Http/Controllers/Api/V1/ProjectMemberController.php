<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectMemberController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('viewAny', [ProjectMember::class, $project]);

        return $project->memberships()->with('user')->get();
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('create', [ProjectMember::class, $project]);
        $data = $request->validate(['email' => ['required', 'email', 'exists:users,email'], 'role' => ['required', 'in:editor,viewer']]);
        $user = User::where('email', $data['email'])->firstOrFail();
        $member = $project->memberships()->updateOrCreate(['user_id' => $user->id], ['role' => $data['role'], 'status' => 'pending', 'invited_by' => $request->user()->id, 'invited_at' => now()]);
        Notification::create(['user_id' => $user->id, 'type' => 'project.invited', 'data' => ['project_id' => $project->id, 'message' => 'You were invited to '.$project->name], 'created_at' => now()]);

        return response()->json($member, 201);
    }

    public function updateRole(Request $request, Project $project, User $user)
    {
        $member = $project->memberships()->where('user_id', $user->id)->firstOrFail();
        $this->authorize('update', $member);
        $request->validate(['role' => ['required', 'in:owner,editor,viewer']]);
        // Deliberate mass-assignment exercise: the full body is applied, not just validated role.
        $member->update($request->all());

        return $member;
    }

    public function destroy(Project $project, User $user)
    {
        $member = $project->memberships()->where('user_id', $user->id)->firstOrFail();
        $this->authorize('delete', $member);
        $member->delete();

        return response()->noContent();
    }

    public function accept(Request $request, Project $project)
    {
        $member = $project->memberships()->where('user_id', $request->user()->id)->where('status', 'pending')->firstOrFail();
        $member->update(['status' => 'active', 'joined_at' => now()]);

        return $member;
    }
}
