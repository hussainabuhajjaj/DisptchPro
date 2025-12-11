<?php

namespace App\Models;

use App\Models\DriverApiToken;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Driver extends Authenticatable implements FilamentUser
{
    use HasFactory, LogsActivity, Notifiable;

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
        'api_token',
        'api_token_expires_at',
        'eld_device_id',
        'hos_provider',
        'hos_last_import_at',
        'hazmat_endorsement',
        'tracking_opt_in',
    ];

    protected $casts = [
        'license_expiry' => 'date',
        'endorsements' => 'array',
        'availability' => 'boolean',
        'api_token_expires_at' => 'datetime',
        'hos_last_import_at' => 'datetime',
        'hazmat_endorsement' => 'boolean',
        'tracking_opt_in' => 'boolean',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    protected static function booted(): void
    {
        static::creating(function (Driver $driver) {
            if (empty($driver->api_token)) {
                $driver->issueApiToken();
            }
        });
    }

    /**
     * Issue a new API token (plaintext stored on the model, expiry 30 days).
     */
    public function issueApiToken(int $days = 30): void
    {
        $token = \Illuminate\Support\Str::random(60);
        $this->api_token = $token;
        $this->api_token_expires_at = now()->addDays($days);
    }

    /**
     * Ensure the token exists and is not expired; returns the plaintext token.
     */
    public function ensureFreshToken(int $days = 30): string
    {
        $needsNew = !$this->api_token || ($this->api_token_expires_at && $this->api_token_expires_at->isPast());
        if ($needsNew) {
            $this->issueApiToken($days);
            $this->saveQuietly();
        }
        return $this->api_token;
    }

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

    public function hasValidToken(?string $token): bool
    {
        if (!$token || !$this->api_token || $this->api_token !== $token) {
            return false;
        }
        if ($this->api_token_expires_at && $this->api_token_expires_at->isPast()) {
            return false;
        }
        return true;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function apiTokens(): HasMany
    {
        return $this->hasMany(DriverApiToken::class);
    }

    public function issueBearerToken(?string $name = null, int $daysValid = 30): string
    {
        $expiresAt = now()->addDays($daysValid);
        return DriverApiToken::issueForDriver($this, $name, $expiresAt);
    }

    public function setPasswordAttribute($value): void
    {
        if ($value) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'driver';
    }
}
