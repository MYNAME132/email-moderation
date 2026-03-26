<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Email;

class Document extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'email_id',
        'body'
    ];

    protected $casts = [
        'body' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function email()
    {
        return $this->belongsTo(Email::class);
    }
}
