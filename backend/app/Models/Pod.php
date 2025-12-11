<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pod extends Model
{
    use HasFactory;

    protected $fillable = [
        'load_id',
        'driver_id',
        'signer_name',
        'signer_title',
        'signed_at',
        'photo_path',
        'location',
    ];

    protected $casts = [
        'location' => 'array',
        'signed_at' => 'datetime',
    ];

    // Use non-conflicting relation name; Model already defines load() helper
    public function loadRelation()
    {
        return $this->belongsTo(Load::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
