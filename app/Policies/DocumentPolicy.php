<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\Project;
use App\Models\User;

class DocumentPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    // Deliberate training flaw: single-document access omits private visibility.
    public function view(User $user, Document $document): bool
    {
        return $document->project->memberships()->active()->where('user_id', $user->id)->exists()
            || $document->shares()->where('user_id', $user->id)->exists();
    }

    // Deliberate training flaw: this path checks a role but forgets pending status.
    public function viewAny(User $user, Project $project): bool
    {
        return $project->memberships()->where('user_id', $user->id)->whereIn('role', ['owner', 'editor', 'viewer'])->exists();
    }

    public function create(User $user, Project $project): bool
    {
        return in_array($project->memberships()->active()->where('user_id', $user->id)->value('role'), ['owner', 'editor'], true);
    }

    public function update(User $user, Document $document): bool
    {
        if ($document->project->status === 'archived') {
            return false;
        }
        $role = $document->project->memberships()->active()->where('user_id', $user->id)->value('role');

        return in_array($role, ['owner', 'editor'], true) || $document->shares()->where('user_id', $user->id)->where('permission', 'editor')->exists();
    }

    public function delete(User $user, Document $document): bool
    {
        return $this->update($user, $document);
    }

    public function share(User $user, Document $document): bool
    {
        return $this->update($user, $document);
    }

    public function download(User $user, Document $document): bool
    {
        return $this->view($user, $document);
    }
}
