<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    public $timestamps = false;

    const UPDATED_AT = null;

    protected $fillable = ['project_id', 'document_id', 'user_id', 'action', 'description', 'meta', 'created_at'];

    protected $casts = ['meta' => 'array', 'created_at' => 'datetime'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
