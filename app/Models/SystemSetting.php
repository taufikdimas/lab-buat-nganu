<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    public $timestamps = false;

    const CREATED_AT = null;

    protected $fillable = ['key', 'value', 'updated_by', 'updated_at'];

    protected $casts = ['updated_at' => 'datetime'];

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function value(string $key, mixed $default = null): mixed
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }
}
