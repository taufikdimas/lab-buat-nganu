<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;

class ProjectMemberPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user, Project $project): bool
    {
        return $user->can('view', $project);
    }

    public function create(User $user, Project $project): bool
    {
        return $user->can('manageMembers', $project);
    }

    public function update(User $user, ProjectMember $member): bool
    {
        return $user->can('manageMembers', $member->project);
    }

    public function delete(User $user, ProjectMember $member): bool
    {
        return $user->can('manageMembers', $member->project) && $member->role !== 'owner';
    }
}
