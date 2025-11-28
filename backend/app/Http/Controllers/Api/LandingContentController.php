<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LandingPageResource;
use App\Models\LandingSection;
use App\Models\Media;
use App\Settings\GeneralSettings;
use App\Settings\SeoSettings;
use App\Settings\FooterSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LandingContentController extends Controller
{



    public function index(Request $request)
    {
        $sections = LandingSection::where('is_active', true)
            ->orderBy('position')
            ->get(['slug', 'title', 'subtitle', 'content']);

        $mediaRecord = Media::first() ?? new Media();

        $settings = array_merge(
            app(GeneralSettings::class)->toArray(),
            app(SeoSettings::class)->toArray(),
            app(FooterSettings::class)->toArray(),
        );

        return new LandingPageResource([
            'sections' => $sections,
            'settings' => $settings,
            'media' => $mediaRecord,
        ]);
    }
}
