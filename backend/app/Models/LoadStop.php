<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

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
        'contact_person',
        'contact_phone',
        'instructions',
    ];

    protected $casts = [
        'date_from' => 'datetime',
        'date_to' => 'datetime',
    ];

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function loadRelation()
    {
        return $this->belongsTo(Load::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
