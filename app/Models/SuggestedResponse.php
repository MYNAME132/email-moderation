<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuggestedResponse extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'suggested_responses';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'email_id',
        'content',
        'is_selected',
    ];

    protected $casts = [
        'is_selected' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }
}
