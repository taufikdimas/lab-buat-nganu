<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public $timestamps = false;

    const UPDATED_AT = null;

    protected $fillable = ['user_id', 'type', 'data', 'read_at', 'created_at'];

    protected $casts = ['data' => 'array', 'read_at' => 'datetime', 'created_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
