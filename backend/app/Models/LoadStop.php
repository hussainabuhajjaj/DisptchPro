<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Document;

class LoadStop extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'load_id',
        'sequence',
        'type',
        'facility_name',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'lat',
        'lng',
        'date_from',
        'date_to',
        'appointment_time',
        'timezone',
        'window_start',
        'window_end',
        'geofence_radius_m',
        'is_appointment_required',
        'contact_person',
        'contact_phone',
        'instructions',
    ];

    protected $casts = [
        'date_from' => 'datetime',
        'date_to' => 'datetime',
        'window_start' => 'datetime',
        'window_end' => 'datetime',
        'is_appointment_required' => 'boolean',
        'geofence_radius_m' => 'float',
    ];

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function loadRelation()
    {
        // Explicitly set the FK so Laravel doesn't assume `load_relation_id`.
        return $this->belongsTo(Load::class, 'load_id');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
