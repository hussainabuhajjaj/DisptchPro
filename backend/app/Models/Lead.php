<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PipelineStage;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'whatsapp',
        'preferred_contact',
        'timezone',
        'company_name',
        'mc_number',
        'dot_number',
        'years_in_business',
        'website',
        'equipment',
        'trucks_count',
        'currently_running',
        'working_with_dispatcher',
        'preferred_lanes',
        'preferred_load_types',
        'min_rate_per_mile',
        'max_deadhead_miles',
        'runs_weekends',
        'home_time',
        'expectation_rate',
        'current_weekly_gross',
        'objections',
        'notes',
        'last_contact_at',
        'next_follow_up_at',
        'pipeline_stage_id',
        'lead_source_id',
        'owner_id',
        'status',
        'assigned_to',
        // legacy/simple capture
        'origin',
        'destination',
        'freight_details',
        'source',
    ];

    protected $casts = [
        'equipment' => 'array',
        'preferred_lanes' => 'array',
        'preferred_load_types' => 'array',
        'currently_running' => 'boolean',
        'working_with_dispatcher' => 'boolean',
        'runs_weekends' => 'boolean',
        'last_contact_at' => 'datetime',
        'next_follow_up_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Lead $lead) {
            if (empty($lead->pipeline_stage_id)) {
                $lead->pipeline_stage_id = static::defaultStageId();
            }
        });
    }

    public static function defaultStageId(): ?int
    {
        return PipelineStage::query()
            ->orderByDesc('is_default')
            ->orderBy('position')
            ->value('id');
    }

    public function pipelineStage()
    {
        return $this->belongsTo(PipelineStage::class);
    }

    public function source()
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function tags()
    {
        return $this->belongsToMany(LeadTag::class, 'lead_lead_tag');
    }

    public function tasks()
    {
        return $this->hasMany(LeadTask::class);
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class);
    }
}
