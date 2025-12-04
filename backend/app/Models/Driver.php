<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Driver extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'carrier_id',
        'name',
        'phone',
        'email',
        'license_number',
        'license_state',
        'license_expiry',
        'CDL_type',
        'endorsements',
        'address',
        'emergency_contact',
        'status',
        'availability',
        'notes',
    ];

    protected $casts = [
        'license_expiry' => 'date',
        'endorsements' => 'array',
        'availability' => 'boolean',
    ];

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
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
