<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Document;
use App\Models\User;

class CommentPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function create(User $user, Document $document): bool
    {
        return $document->project->status === 'active' && $user->can('view', $document);
    }

    public function update(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id && $comment->document->project->status === 'active';
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id;
    }
}
