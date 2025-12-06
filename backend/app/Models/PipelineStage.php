<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PipelineStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'position',
        'is_default',
        'position_x',
        'position_y',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function outgoingTransitions()
    {
        return $this->hasMany(PipelineTransition::class, 'from_stage_id');
    }

    public function incomingTransitions()
    {
        return $this->hasMany(PipelineTransition::class, 'to_stage_id');
    }
}
