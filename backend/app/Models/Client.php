<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Client extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'type',
        'contact_person',
        'phone',
        'email',
        'billing_address',
        'city',
        'state',
        'zip',
        'country',
        'payment_terms',
        'credit_limit',
        'auto_apply_credit',
        'credit_expiry_days',
        'tax_id',
        'notes',
        'status',
    ];

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function loads()
    {
        return $this->hasMany(Load::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function credits()
    {
        return $this->hasMany(\App\Models\CreditBalance::class, 'entity_id')->where('entity_type', 'client');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
