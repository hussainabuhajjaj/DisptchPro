<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'source_type',
        'source_id',
        'amount',
        'remaining',
        'reason',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'remaining' => 'decimal:2',
        'expires_at' => 'date',
    ];
}
