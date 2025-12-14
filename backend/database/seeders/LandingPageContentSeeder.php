<?php

namespace Database\Seeders;

use App\Models\LandingSection;
use App\Models\Media;
use App\Settings\FooterSettings;
use App\Settings\GeneralSettings;
use App\Settings\SeoSettings;
use Illuminate\Database\Seeder;

class LandingPageContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $this->seedMedia();
        $this->seedSections();
    }

    private function seedSettings(): void
    {
        /** @var GeneralSettings $general */
        $general = app(GeneralSettings::class);
        $general->site_name = 'H&A Dispatch';
        $general->site_title = 'Full-Service Dispatch, Live Tracking, and CRM for Carriers';
        $general->site_tagline = 'Smart dispatch + live tracking built for U.S. carriers.';
        $general->site_description = 'Modern TMS + CRM with live driver tracking, automated check-calls, and tender-to-POD workflows.';
        $general->site_timezone = 'America/New_York';
        $general->site_locale = 'en';
        $general->logo_url = 'https://hadispatch.com/assets/logo.svg';
        $general->logo_dark_url = 'https://hadispatch.com/assets/logo-dark.svg';
        $general->favicon_url = 'https://hadispatch.com/favicon.ico';
        $general->canonical_url = 'https://hadispatch.com';
        $general->contact_email = 'info@hadispatch.com';
        $general->contact_phone = '+1 (201) 555-1212';
        $general->contact_address = '123 Logistics Way';
        $general->contact_city = 'Newark';
        $general->contact_state = 'NJ';
        $general->contact_country = 'USA';
        $general->support_text = 'U.S. dispatch + live tracking, 24/7 coverage';
        $general->topbar_text = '24/7 Dispatch Support • Live Tracking • POD Fast';
        $general->primary_cta_label = 'Book a Demo';
        $general->primary_cta_url = 'https://hadispatch.com/#book';
        $general->secondary_cta_label = 'View Pricing';
        $general->secondary_cta_url = 'https://hadispatch.com/#pricing';
        $general->book_a_call_url = 'https://cal.com/hadispatch/demo';
        $general->header_links = [
            ['label' => 'Services', 'href' => '/#services'],
            ['label' => 'Pricing', 'href' => '/#pricing'],
            ['label' => 'Why Us', 'href' => '/#why-us'],
            ['label' => 'Resources', 'href' => '/#lead-magnet'],
            ['label' => 'Book a Call', 'href' => '/#book'],
        ];
        $general->trust_logos = [
            ['label' => 'FMCSA Ready', 'logo_url' => 'https://hadispatch.com/assets/trust/fmcsa.svg', 'href' => null],
            ['label' => 'ELD Aware', 'logo_url' => 'https://hadispatch.com/assets/trust/eld.svg', 'href' => null],
            ['label' => 'COI Monitoring', 'logo_url' => 'https://hadispatch.com/assets/trust/insurance.svg', 'href' => null],
        ];
        $general->theme_default_mode = 'system';
        $general->theme_allow_mode_toggle = true;
        $general->newsletter_enabled = true;
        $general->newsletter_form_action = 'https://api.hadispatch.com/api/newsletter';
        $general->newsletter_consent_text = 'By subscribing you consent to receive dispatch and product updates from H&A Dispatch.';
        $general->cookie_banner_enabled = true;
        $general->cookie_message = 'We use cookies to personalize content and analyze traffic. See our Privacy Policy for details.';
        $general->cookie_cta_text = 'Accept';
        $general->cookie_policy_url = '/privacy';
        $general->theme_primary_color = '#f59e0b';
        $general->theme_secondary_color = '#0f172a';
        $general->theme_accent_color = '#2563eb';
        $general->theme_text_color = '#0f172a';
        $general->save();

        /** @var SeoSettings $seo */
        $seo = app(SeoSettings::class);
        $seo->meta_title = 'H&A Dispatch | Live TMS, Driver App, and CRM';
        $seo->meta_description = 'End-to-end dispatch, live GPS tracking, driver app, CRM pipelines, and tender-to-POD automation for carriers and brokers.';
        $seo->og_image = 'https://hadispatch.com/assets/social-card.png';
        $seo->twitter_handle = '@hadispatch';
        $seo->facebook_page = 'https://www.facebook.com/hadispatch';
        $seo->save();

        /** @var FooterSettings $footer */
        $footer = app(FooterSettings::class);
        $footer->footer_text = 'H&A Dispatch — modern TMS + CRM for U.S. carriers. Live tracking, automated check-calls, and fast POD.';
        $footer->links = [
            ['label' => 'Privacy Policy', 'href' => '/privacy'],
            ['label' => 'Terms of Service', 'href' => '/terms'],
            ['label' => 'Support', 'href' => 'mailto:info@hadispatch.com'],
            ['label' => 'Docs', 'href' => '/api/docs'],
        ];
        $footer->save();
    }

    private function seedMedia(): void
    {
        $media = Media::firstOrCreate([]);
        $media->fill([
            'hero_image_url' => 'https://images.unsplash.com/photo-1504198453319-5ce911bafcde?auto=format&fit=crop&w=1600&q=80',
            'why_choose_us_image_url' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1400&q=80',
            'for_shippers_image_url' => 'https://images.unsplash.com/photo-1501700493788-fa1a4fc9fe62?auto=format&fit=crop&w=1400&q=80',
            'for_brokers_image_url' => 'https://images.unsplash.com/photo-1501696461400-5aedb0f3b219?auto=format&fit=crop&w=1400&q=80',
            'testimonial_avatar_1_url' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=200&q=80',
            'testimonial_avatar_2_url' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=200&q=80',
            'testimonial_avatar_3_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=200&q=80',
            'lead_magnet_url' => 'https://hadispatch.com/assets/dispatch-playbook.pdf',
        ]);
        $media->save();
    }

    private function seedSections(): void
    {
        $sections = [
            [
                'slug' => 'hero',
                'title' => 'Live tracking + dispatch automation in one place',
                'subtitle' => 'Driver app, TMS, CRM, and POD capture built for U.S. fleets.',
                'position' => 1,
                'content' => [
                    'cta_primary' => 'Book a Demo',
                    'cta_secondary' => 'View Pricing',
                    'bullets' => [
                        'Real-time GPS with auto check-calls',
                        'Tender-to-POD workflows with accessorials',
                        'Driver app with offline queue + auto-pings',
                    ],
                ],
            ],
            [
                'slug' => 'services',
                'title' => 'Dispatch services tailored to your fleet',
                'subtitle' => 'From tender to delivery, we handle the ops so you can scale.',
                'position' => 2,
                'content' => [
                    'items' => [
                        ['title' => 'Load sourcing', 'description' => 'Smart tendering, carrier acceptance, and broker-ready docs.'],
                        ['title' => 'Live tracking', 'description' => 'Auto-ETA, geofencing alerts, and exception badges.'],
                        ['title' => 'Driver success', 'description' => 'One-tap check-calls, POD capture, and offline mode.'],
                    ],
                ],
            ],
            [
                'slug' => 'kpis',
                'title' => 'Operational KPIs that matter',
                'subtitle' => 'See on-time %, ETA variance, detention hours, and fleet online/offline in one view.',
                'position' => 3,
                'content' => [
                    'metrics' => [
                        ['label' => 'On-time pickup', 'value' => '96%'],
                        ['label' => 'Avg. detention', 'value' => '14 min'],
                        ['label' => 'Fleet online', 'value' => '18/20'],
                    ],
                ],
            ],
            [
                'slug' => 'why-us',
                'title' => 'Built for U.S. compliance',
                'subtitle' => 'USDOT/MC tracking, COI monitoring, BOL/POD, and accessorials baked in.',
                'position' => 4,
                'content' => [
                    'points' => [
                        'Carrier onboarding with USDOT/MC & COI alerts',
                        'Accessorials: detention, TONU, lumper, layover',
                        'BOL/POD templates with photo + signature capture',
                    ],
                ],
            ],
            [
                'slug' => 'pricing',
                'title' => 'Simple, transparent pricing',
                'subtitle' => 'Choose a plan that fits your fleet size.',
                'position' => 5,
                'content' => [
                    'plans' => [
                        ['name' => 'Starter', 'price' => '$299/mo', 'features' => ['Up to 5 drivers', 'Live tracking + alerts', 'Basic CRM']],
                        ['name' => 'Growth', 'price' => '$699/mo', 'features' => ['Up to 20 drivers', 'Automations + webhooks', 'Accessorial workflows']],
                        ['name' => 'Scale', 'price' => 'Custom', 'features' => ['Unlimited drivers', 'EDI/ELD integrations', 'Dedicated support']],
                    ],
                ],
            ],
            [
                'slug' => 'lead-magnet',
                'title' => 'Download the Dispatch Playbook',
                'subtitle' => 'A step-by-step guide to faster check-calls, fewer fines, and happier drivers.',
                'position' => 6,
                'content' => [
                    'cta' => 'Get the PDF',
                    'description' => 'Includes SOPs, accessorial checklists, and SLA templates ready for your team.',
                ],
            ],
            [
                'slug' => 'faq',
                'title' => 'Frequently asked questions',
                'subtitle' => 'We cover onboarding, pricing, and integrations.',
                'position' => 7,
                'content' => [
                    'faqs' => [
                        ['q' => 'Do you support driver tokens and auto-pings?', 'a' => 'Yes. Drivers get tokens on registration; location pings can be auto-throttled.'],
                        ['q' => 'Can I capture POD with signature?', 'a' => 'Yes. Drivers can snap photos and capture signatures; dispatchers review in Filament.'],
                        ['q' => 'Do you handle accessorials?', 'a' => 'Detention, TONU, lumper, and layover are supported with approvals.'],
                    ],
                ],
            ],
        ];

        foreach ($sections as $section) {
            LandingSection::updateOrCreate(
                ['slug' => $section['slug']],
                [
                    'title' => $section['title'],
                    'subtitle' => $section['subtitle'],
                    'content' => $section['content'],
                    'position' => $section['position'],
                    'is_active' => true,
                ]
            );
        }
    }
}
