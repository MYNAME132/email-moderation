<?php

namespace App\Models;

use App\Enums\ResponseDecisionEnum;
use App\Enums\StatusEnum;
use App\Models\Document;
use App\Models\Link;
use App\Models\SuggestedResponse;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Email extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'emails';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'sender',
        'receiver',
        'subject',
        'response',
        'status',
        'response_status',
        'response_decision',
    ];

    protected $casts = [
        'response' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'status' => StatusEnum::class,
        'response_status' => StatusEnum::class,
        'response_decision' => ResponseDecisionEnum::class,
    ];

    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    public function document(): HasOne
    {
        return $this->hasOne(Document::class);
    }

    public function suggestedResponses(): HasMany
    {
        return $this->hasMany(SuggestedResponse::class);
    }

    public function selectedResponse(): BelongsTo
    {
        return $this->belongsTo(SuggestedResponse::class, 'selected_response_id');
    }
}
