<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettlementItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'settlement_id',
        'description',
        'quantity',
        'rate',
        'amount',
    ];

    protected $casts = [
        'quantity' => 'int',
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function settlement()
    {
        return $this->belongsTo(Settlement::class);
    }
}
