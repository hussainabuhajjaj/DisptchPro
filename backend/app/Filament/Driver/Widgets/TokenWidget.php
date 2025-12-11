<?php

namespace App\Filament\Driver\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TokenWidget extends Widget
{
    protected string $view = 'filament.driver.widgets.token-widget';

    public ?string $token = null;
    public ?string $expiresAt = null;

    public function mount(): void
    {
        $driver = Auth::guard('driver')->user();
        if (!$driver) {
            return;
        }
        $this->token = $driver->api_token;
        $this->expiresAt = optional($driver->api_token_expires_at)?->toDateTimeString();
    }

    public function maskedToken(): string
    {
        if (!$this->token) {
            return '';
        }
        $len = Str::length($this->token);
        if ($len <= 8) {
            return '***';
        }
        return Str::substr($this->token, 0, 4) . str_repeat('*', max(0, $len - 8)) . Str::substr($this->token, -4);
    }
}
