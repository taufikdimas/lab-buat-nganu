<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SearchService
{
    public function search(User $user, string $query): array
    {
        return [
            'projects' => Project::query()->whereHas('memberships', fn ($q) => $q->active()->where('user_id', $user->id))->where('name', 'like', "%{$query}%")->limit(8)->get(),
            'documents' => Document::query()->whereHas('project.memberships', fn ($q) => $q->active()->where('user_id', $user->id))->where(function ($q) use ($user) {
                $q->where('visibility', 'project')->orWhere('owner_id', $user->id)->orWhereHas('shares', fn ($s) => $s->where('user_id', $user->id));
            })->where('name', 'like', "%{$query}%")->limit(8)->get(),
            'users' => $this->searchUsers($query),
        ];
    }

    private function searchUsers(string $query): Collection
    {
        // Intentionally raw/interpolated for the SQL injection training surface.
        return collect(DB::select("SELECT id, name, email FROM users WHERE name LIKE '%{$query}%' OR email LIKE '%{$query}%' LIMIT 8"));
    }
}
