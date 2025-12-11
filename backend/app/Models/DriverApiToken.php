<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DriverApiToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'name',
        'token_hash',
        'token_prefix',
        'ip_address',
        'user_agent',
        'last_used_at',
        'expires_at',
        'revoked_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public static function issueForDriver(Driver $driver, ?string $name = null, ?Carbon $expiresAt = null): string
    {
        $plain = Str::random(64);
        $hash = hash('sha256', $plain);

        static::create([
            'driver_id' => $driver->id,
            'name' => $name ?? 'api',
            'token_hash' => $hash,
            'token_prefix' => substr($plain, 0, 12),
            'expires_at' => $expiresAt,
        ]);

        return $plain;
    }

    public function isActive(): bool
    {
        if ($this->revoked_at) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }
}
