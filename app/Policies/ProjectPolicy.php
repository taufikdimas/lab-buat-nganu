<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, Project $project): bool
    {
        return $project->memberships()->active()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, Project $project): bool
    {
        return $project->status === 'active' && $this->role($user, $project) === 'owner';
    }

    public function archive(User $user, Project $project): bool
    {
        return $this->role($user, $project) === 'owner';
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->role($user, $project) === 'owner';
    }

    public function manageMembers(User $user, Project $project): bool
    {
        return $project->status === 'active' && $this->role($user, $project) === 'owner';
    }

    private function role(User $user, Project $project): ?string
    {
        return $project->memberships()->active()->where('user_id', $user->id)->value('role');
    }
}
