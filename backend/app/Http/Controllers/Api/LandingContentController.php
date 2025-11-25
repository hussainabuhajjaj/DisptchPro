<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LandingSection;
use App\Settings\GeneralSettings;
use App\Settings\SeoSettings;
use App\Settings\FooterSettings;
use Illuminate\Http\Request;

class LandingContentController extends Controller
{
    public function index(Request $request)
    {
        $sections = LandingSection::where('is_active', true)
            ->orderBy('position')
            ->get(['slug', 'title', 'subtitle', 'content']);

        $settings = array_merge(
            app(GeneralSettings::class)->toArray(),
            app(SeoSettings::class)->toArray(),
            app(FooterSettings::class)->toArray(),
        );

        return response()->json([
            'sections' => $sections,
            'settings' => $settings,
        ]);
    }
}
