<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BolTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'body',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];
}
