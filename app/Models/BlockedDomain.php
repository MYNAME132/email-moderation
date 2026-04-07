<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BlockedDomain extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['domain'];

    protected static function boot(): void
    {
        parent::boot();

        $clearCache = fn() => Cache::forget('classification.blocked_domains');

        static::created($clearCache);
        static::updated($clearCache);
        static::deleted($clearCache);
    }
}
