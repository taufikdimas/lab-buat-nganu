<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    const UPDATED_AT = null;

    protected $fillable = ['actor_id', 'action', 'target_type', 'target_id', 'meta', 'ip_address', 'user_agent', 'created_at'];

    protected $casts = ['meta' => 'array', 'created_at' => 'datetime'];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
