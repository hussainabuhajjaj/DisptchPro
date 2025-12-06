<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
    ];

    public function leads()
    {
        return $this->belongsToMany(Lead::class, 'lead_lead_tag');
    }
}
