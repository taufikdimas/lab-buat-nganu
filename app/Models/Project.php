<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description', 'owner_id', 'status'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function memberships()
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_members')->withPivot(['role', 'status', 'invited_at', 'joined_at'])->withTimestamps();
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class)->latest();
    }

    public function membershipFor(User $user): ?ProjectMember
    {
        return $this->memberships->firstWhere('user_id', $user->id)
            ?? $this->memberships()->where('user_id', $user->id)->first();
    }
}
