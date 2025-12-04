<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettlementPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'settlement_id',
        'paid_at',
        'amount',
        'method',
        'reference',
    ];

    protected $casts = [
        'paid_at' => 'date',
        'amount' => 'decimal:2',
    ];

    public function settlement()
    {
        return $this->belongsTo(Settlement::class);
    }
}
