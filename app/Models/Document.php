<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['project_id', 'owner_id', 'name', 'description', 'file_path', 'original_filename', 'mime_type', 'size_bytes', 'visibility'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function shares()
    {
        return $this->hasMany(DocumentShare::class);
    }

    public function sharedUsers()
    {
        return $this->belongsToMany(User::class, 'document_shares')->withPivot('permission')->withTimestamps();
    }

    public function shareLinks()
    {
        return $this->hasMany(DocumentShareLink::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function activities()
    {
        return $this->hasMany(Activity::class)->latest();
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = max(0, $this->size_bytes);
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return number_format($bytes / 1048576, 1).' MB';
    }
}
