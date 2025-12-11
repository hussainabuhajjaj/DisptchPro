<?php

namespace Tests\Feature;

use App\Services\Compliance\SaferLookupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SaferLookupServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_safer_lookup_uses_cache(): void
    {
        Cache::flush();
        Config::set('services.safer.web_key', 'test-key');
        Config::set('services.safer.base_url', 'https://mobile.fmcsa.dot.gov/qc/services/carriers');
        Config::set('services.safer.cache_minutes', 1440);

        Http::fake([
            'mobile.fmcsa.dot.gov/*' => Http::response(['content' => 'from_api'], 200),
        ]);

        $service = app(SaferLookupService::class);

        $first = $service->lookup('123456', null);
        $this->assertEquals('from_api', $first['data']['content'] ?? null);

        // Second call should return cached value even if HTTP would fail
        Http::fake([
            'mobile.fmcsa.dot.gov/*' => Http::response(null, 500),
        ]);

        $second = $service->lookup('123456', null);
        $this->assertEquals('from_api', $second['data']['content'] ?? null);
    }

    public function test_safer_lookup_falls_back_without_web_key(): void
    {
        Cache::flush();
        Config::set('services.safer.web_key', null);

        $service = app(SaferLookupService::class);
        $resp = $service->lookup('123456', null);

        $this->assertEquals('ok', $resp['status']);
        $this->assertEquals('stub', $resp['source']);
    }
}
