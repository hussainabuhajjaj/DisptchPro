<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Carrier;
use App\Models\Driver;

class Settlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'load_id',
        'settlement_type',
        'entity_id',
        'issue_date',
        'total',
        'balance',
        'status',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'total' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function loadRelation()
    {
        return $this->belongsTo(Load::class, 'load_id');
    }

    public function items()
    {
        return $this->hasMany(SettlementItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SettlementPayment::class);
    }

    public function entity()
    {
        return $this->settlement_type === 'driver'
            ? $this->belongsTo(Driver::class, 'entity_id')
            : $this->belongsTo(Carrier::class, 'entity_id');
    }

    public function getEntityNameAttribute(): ?string
    {
        if ($this->settlement_type === 'driver' && $this->relationLoaded('entity')) {
            return optional($this->entity)->name;
        }

        if ($this->settlement_type === 'carrier' && $this->relationLoaded('entity')) {
            return optional($this->entity)->name;
        }

        return null;
    }

    public function refreshTotals(): void
    {
        $total = $this->items()->sum('amount');
        $paid = $this->payments()->sum('amount');
        $this->total = $total;
        $this->balance = max($total - $paid, 0);
        $this->status = $this->balance <= 0 ? 'paid' : ($this->balance < $total ? 'partial' : $this->status);
        $this->saveQuietly();
    }
}
