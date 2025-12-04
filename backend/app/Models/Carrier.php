<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Carrier extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'MC_number',
        'DOT_number',
        'phone',
        'email',
        'dispatcher_contact',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'insurance_company',
        'insurance_policy_number',
        'insurance_expiry',
        'payment_terms',
        'auto_apply_credit',
        'credit_expiry_days',
        'factoring_company',
        'factoring_email',
        'onboarding_status',
        'notes',
    ];

    protected $casts = [
        'insurance_expiry' => 'date',
    ];

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }

    public function loads()
    {
        return $this->hasMany(Load::class);
    }

    public function credits()
    {
        return $this->hasMany(\App\Models\CreditBalance::class, 'entity_id')->where('entity_type', 'carrier');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
