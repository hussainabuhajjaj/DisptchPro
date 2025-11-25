<?php

namespace Database\Seeders;

use App\Models\LandingSection;
use Illuminate\Database\Seeder;

class LandingSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'slug' => 'hero',
                'title' => 'Keep your trucks loaded. Stay profitable.',
                'subtitle' => 'DispatchPro handles load hunting, compliance, and paperwork so you can focus on driving.',
                'position' => 1,
                'is_active' => true,
                'content' => [
                    'cta_primary' => 'Start with us',
                    'cta_secondary' => 'See load board preview',
                    'badge' => '24/7 dispatch',
                ],
            ],
            [
                'slug' => 'features',
                'title' => 'Why carriers choose DispatchPro',
                'subtitle' => 'Real humans, proactive planning, and transparent communication.',
                'position' => 2,
                'is_active' => true,
                'content' => [
                    'items' => [
                        ['title' => 'Dedicated dispatcher', 'description' => 'Single point of contact who knows your lanes and preferences.'],
                        ['title' => 'Paperwork & compliance', 'description' => 'COI/W-9 handling, rate con review, carrier packet setup.'],
                        ['title' => '24/7 support', 'description' => 'Night, weekend, and breakdown support to keep you rolling.'],
                    ],
                ],
            ],
            [
                'slug' => 'kpis',
                'title' => 'Operational KPIs',
                'subtitle' => 'We track the numbers that matter to your bottom line.',
                'position' => 3,
                'is_active' => true,
                'content' => [
                    'metrics' => [
                        ['label' => 'Avg rate per mile', 'value' => '$2.85'],
                        ['label' => 'Loads this month', 'value' => '38'],
                        ['label' => 'On-time delivery', 'value' => '98%'],
                    ],
                ],
            ],
            [
                'slug' => 'load-board',
                'title' => 'Load board preview',
                'subtitle' => 'See a snapshot of live loads we’re booking today.',
                'position' => 4,
                'is_active' => true,
                'content' => [
                    'filters' => ['lanes', 'equipment', 'rate'],
                    'loads' => [
                        ['origin' => 'Dallas, TX', 'destination' => 'Atlanta, GA', 'equipment' => 'Dry Van', 'rate' => '$2.70/mi'],
                        ['origin' => 'Chicago, IL', 'destination' => 'Columbus, OH', 'equipment' => 'Reefer', 'rate' => '$3.10/mi'],
                        ['origin' => 'Houston, TX', 'destination' => 'Chicago, IL', 'equipment' => 'Dry Van', 'rate' => '$2.45/mi'],
                    ],
                ],
            ],
            [
                'slug' => 'testimonials',
                'title' => 'Carriers love us',
                'subtitle' => 'What our owner-operators say.',
                'position' => 5,
                'is_active' => true,
                'content' => [
                    'quotes' => [
                        ['name' => 'Marcus T.', 'text' => 'Consistent loads and clear communication. My RPM improved in 6 weeks.'],
                        ['name' => 'Jenny L.', 'text' => 'They handle paperwork fast and keep me rolling even on weekends.'],
                    ],
                ],
            ],
            [
                'slug' => 'cta',
                'title' => 'Book a call and start moving better loads',
                'subtitle' => 'Our team will map your preferred lanes and get you rolling within 24 hours.',
                'position' => 6,
                'is_active' => true,
                'content' => [
                    'cta_primary' => 'Book a call',
                    'cta_secondary' => 'See our process',
                ],
            ],
            [
                'slug' => 'why-us',
                'title' => 'Why DispatchPro',
                'subtitle' => 'We reduce your downtime and maximize RPM with proactive planning.',
                'position' => 7,
                'is_active' => true,
                'content' => [
                    'items' => [
                        ['title' => 'Proactive planning', 'description' => 'We plan reloads before you empty to minimize deadhead.'],
                        ['title' => 'Transparent pricing', 'description' => 'Clear fees and transparent rate con review.'],
                        ['title' => 'Carrier-first', 'description' => 'Your lane/equipment preferences drive every booking.'],
                    ],
                ],
            ],
            [
                'slug' => 'for-shippers',
                'title' => 'Shippers trust us',
                'subtitle' => 'Reliable capacity, clean compliance, and proactive updates.',
                'position' => 8,
                'is_active' => true,
                'content' => [
                    'bullets' => [
                        'Active GPS tracking and status updates.',
                        'Insured, vetted carriers with safety checks.',
                        'Fast POD sharing and billing.',
                    ],
                ],
            ],
            [
                'slug' => 'for-brokers',
                'title' => 'Brokers get coverage fast',
                'subtitle' => 'We cover your lanes with compliant carriers and tight communication.',
                'position' => 9,
                'is_active' => true,
                'content' => [
                    'bullets' => [
                        '24/7 availability for tenders and updates.',
                        'Accurate ETAs and quick issue escalation.',
                        'Paperwork handled: W-9, COI, carrier packets.',
                    ],
                ],
            ],
            [
                'slug' => 'faq',
                'title' => 'Frequently Asked Questions',
                'subtitle' => 'Common questions from carriers we onboard.',
                'position' => 10,
                'is_active' => true,
                'content' => [
                    'faqs' => [
                        [
                            'question' => 'What types of carriers do you work with?',
                            'answer' => 'Owner-operators and small to mid-sized fleets; we tailor lanes and loads to your equipment.',
                        ],
                        [
                            'question' => 'Can I decline a load you offer?',
                            'answer' => 'Yes. You always have final say—no forced dispatch.',
                        ],
                        [
                            'question' => 'What are your dispatch fees?',
                            'answer' => 'Transparent percentage of gross; schedule a call for a fit-based quote.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'resources',
                'title' => 'Resources for Carriers',
                'subtitle' => 'Short, actionable guides to help you run profitably.',
                'position' => 11,
                'is_active' => true,
                'content' => [
                    'resources' => [
                        [
                            'title' => 'How to avoid deadhead and still keep your favorite lanes',
                            'description' => 'Practical tips to minimize empty miles without losing your preferred routes.',
                            'href' => '#',
                        ],
                        [
                            'title' => 'Reefer vs Dry Van: which is better for your current situation?',
                            'description' => 'A quick comparison to choose the equipment strategy that fits your business.',
                            'href' => '#',
                        ],
                        [
                            'title' => 'How to know if a dispatcher is good or just wasting your time',
                            'description' => 'Red flags, green flags, and the questions to ask before you commit.',
                            'href' => '#',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($sections as $section) {
            LandingSection::updateOrCreate(
                ['slug' => $section['slug']],
                $section
            );
        }
    }
}
