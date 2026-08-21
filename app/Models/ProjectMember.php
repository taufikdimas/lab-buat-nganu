<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMember extends Model
{
    use HasFactory;

    // Intentionally broad for the bounded role-update mass-assignment exercise.
    protected $fillable = ['project_id', 'user_id', 'role', 'status', 'invited_by', 'invited_at', 'joined_at'];

    protected $casts = ['invited_at' => 'datetime', 'joined_at' => 'datetime'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
