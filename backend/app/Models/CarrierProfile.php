<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarrierProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'carrier_info',
        'equipment_info',
        'operation_info',
        'factoring_info',
        'insurance_info',
        'status',
    ];

    protected $casts = [
        'carrier_info' => 'array',
        'equipment_info' => 'array',
        'operation_info' => 'array',
        'factoring_info' => 'array',
        'insurance_info' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
