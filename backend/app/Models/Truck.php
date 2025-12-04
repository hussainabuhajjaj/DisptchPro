<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Truck extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'unit_number',
        'plate_number',
        'VIN',
        'type',
        'make',
        'model',
        'year',
        'status',
        'current_load_id',
        'next_service_date',
        'mileage',
        'notes',
    ];

    protected $casts = [
        'next_service_date' => 'date',
    ];

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function currentLoad()
    {
        return $this->belongsTo(Load::class, 'current_load_id');
    }

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
