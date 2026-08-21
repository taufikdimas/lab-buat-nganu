<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentShareLink extends Model
{
    protected $fillable = ['document_id', 'token', 'created_by', 'expires_at', 'revoked_at', 'access_count', 'last_accessed_at'];

    protected $casts = ['expires_at' => 'datetime', 'revoked_at' => 'datetime', 'last_accessed_at' => 'datetime'];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
