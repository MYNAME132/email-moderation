<?php

namespace App\Models;

use App\Models\Email;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Link extends Model
{
    use HasUuids;

    protected $table = 'links';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'email_id',
        'url'
    ];

    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }
}
