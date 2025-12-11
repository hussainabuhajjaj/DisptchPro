<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoadLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'load_id',
        'driver_id',
        'lat',
        'lng',
        'speed',
        'heading',
        'accuracy_m',
        'source',
        'is_valid',
        'track_id',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'is_valid' => 'boolean',
        'lat' => 'float',
        'lng' => 'float',
        'speed' => 'float',
        'heading' => 'float',
        'accuracy_m' => 'float',
    ];

    // Use a non-conflicting relation name (Model already has load() helper)
    public function loadRelation()
    {
        return $this->belongsTo(Load::class, 'load_id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
