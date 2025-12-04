<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiService
{
    public function suggestRates(array $loadData): array
    {
        if (!env('AI_API_KEY')) {
            return ['rate_to_client' => 2500, 'rate_to_carrier' => 2000];
        }

        $resp = Http::withToken(env('AI_API_KEY'))->post('https://api.example.com/rates', $loadData)->json();
        return [
            'rate_to_client' => $resp['client'] ?? 0,
            'rate_to_carrier' => $resp['carrier'] ?? 0,
        ];
    }

    public function summarizeText(string $text): string
    {
        if (!env('AI_API_KEY')) {
            return mb_strimwidth($text, 0, 240, '...');
        }

        return Http::withToken(env('AI_API_KEY'))
            ->post('https://api.example.com/summarize', ['text' => $text])
            ->json('summary', '');
    }

    public function generateLoadDescription(array $loadData): string
    {
        $origin = $loadData['origin'] ?? 'Origin';
        $destination = $loadData['destination'] ?? 'Destination';
        $distance = $loadData['distance'] ?? 'N/A';
        $equipment = $loadData['equipment'] ?? 'Equipment';

        return "{$origin} → {$destination}, {$distance} miles, {$equipment}";
    }
}
