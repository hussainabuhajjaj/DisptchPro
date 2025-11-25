<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarrierDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'data',
        'status',
        'consent',
    ];

    protected $casts = [
        'data' => 'array',
        'consent' => 'array',
    ];

    public function documents()
    {
        return $this->hasMany(CarrierDocument::class, 'draft_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
