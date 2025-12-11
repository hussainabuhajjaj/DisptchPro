<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accessorial extends Model
{
    use HasFactory;

    protected $fillable = [
        'load_id',
        'type',
        'amount',
        'status',
        'note',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    // Use non-conflicting relation name; Model already defines load() helper
    public function loadRelation()
    {
        return $this->belongsTo(Load::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
