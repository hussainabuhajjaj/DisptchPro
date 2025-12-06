<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PipelineTransition extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_stage_id',
        'to_stage_id',
        'label',
        'actions',
    ];

    protected $casts = [
        'actions' => 'array',
    ];

    public function fromStage()
    {
        return $this->belongsTo(PipelineStage::class, 'from_stage_id');
    }

    public function toStage()
    {
        return $this->belongsTo(PipelineStage::class, 'to_stage_id');
    }
}
