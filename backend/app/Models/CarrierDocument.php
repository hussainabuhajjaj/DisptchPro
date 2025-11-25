<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarrierDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'draft_id',
        'type',
        'path',
        'status',
        'reviewer_note',
        'file_name',
    ];

    protected $casts = [
        'reviewer_note' => 'string',
    ];

    public function draft()
    {
        return $this->belongsTo(CarrierDraft::class, 'draft_id');
    }
}
