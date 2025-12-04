<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Trailer extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'trailer_number',
        'plate_number',
        'VIN',
        'type',
        'length',
        'max_weight',
        'reefer_settings',
        'status',
        'next_service_date',
        'mileage',
        'notes',
    ];

    protected $casts = [
        'reefer_settings' => 'array',
        'next_service_date' => 'date',
    ];

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function loads()
    {
        return $this->hasMany(Load::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
