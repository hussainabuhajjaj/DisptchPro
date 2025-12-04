<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'event_type',
        'secret',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];
}
