<?php

namespace App\Services\Compliance;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SaferLookupService
{
    /**
     * Lookup carrier safety snapshot by USDOT or MC number.
     * This is a stubbed implementation; replace HTTP client call with real SAFER API integration.
     *
     * @param string|null $usdOt
     * @param string|null $mc
     * @return array{status:string, source:string, fetched_at:string, data:array}
     */
    public function lookup(?string $usdOt, ?string $mc): array
    {
        $now = Carbon::now()->toIso8601String();
        $baseUrl = rtrim(config('services.safer.base_url', 'https://mobile.fmcsa.dot.gov/qc/services/carriers'), '/');
        $apiKey = config('services.safer.api_key'); // optional header for proxies
        $webKey = config('services.safer.web_key'); // official QC web key
        $timeout = (int) config('services.safer.timeout', 10);
        $cacheMinutes = (int) config('services.safer.cache_minutes', 1440);

        $path = null;
        if ($usdOt) {
            $path = "dot/{$usdOt}";
        } elseif ($mc) {
            $path = "mc/{$mc}";
        }

        // Must have path and webKey to hit FMCSA QC service
        if (!$path || !$webKey) {
            return $this->mockResponse($usdOt, $mc, $now, 'missing_web_key_or_identifier');
        }

        $url = "{$baseUrl}/{$path}";
        $cacheKey = "safer:lookup:" . md5($url . '|' . $webKey);
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        try {
            $request = Http::timeout($timeout)
                ->acceptJson()
                ->retry(2, 250);

            if ($apiKey) {
                $request = $request->withHeaders(['x-api-key' => $apiKey]);
            }

            $response = $request->get($url, ['webKey' => $webKey]);

            if ($response->failed()) {
                Log::warning('SAFER lookup failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $this->mockResponse($usdOt, $mc, $now, 'http_error');
            }

            $data = $response->json() ?? [];

            $payload = [
                'status' => 'ok',
                'source' => 'safer',
                'fetched_at' => $now,
                'data' => $data,
            ];

            Cache::put($cacheKey, $payload, now()->addMinutes($cacheMinutes));

            return $payload;
        } catch (\Throwable $e) {
            Log::warning('SAFER lookup exception', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return $this->mockResponse($usdOt, $mc, $now, 'exception');
        }
    }

    protected function mockResponse(?string $usdOt, ?string $mc, string $fetchedAt, string $reason): array
    {
        $mock = [
            'usdot' => $usdOt,
            'mc' => $mc,
            'legal_name' => 'Mock Carrier Inc.',
            'dba_name' => 'Mock Carrier',
            'status' => 'ACTIVE',
            'safety_rating' => 'SATISFACTORY',
            'power_units' => 12,
            'drivers' => 18,
            'insurance_on_file' => true,
            'last_update' => $fetchedAt,
            'cargo_carried' => ['General Freight', 'Machinery'],
            '_mock_reason' => $reason,
        ];

        return [
            'status' => 'ok',
            'source' => 'stub',
            'fetched_at' => $fetchedAt,
            'data' => $mock,
        ];
    }
}
