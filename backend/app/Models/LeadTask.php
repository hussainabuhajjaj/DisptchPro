<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'assigned_to',
        'title',
        'notes',
        'status',
        'priority',
        'due_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
