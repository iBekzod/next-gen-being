<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'site_name',
                'value' => 'NextGenBeing',
                'group' => 'general',
                'description' => 'Brand name shown across the product',
            ],
            [
                'key' => 'site_description',
                'value' => 'Weekly intelligence for founders, operators, and creators who ship with AI.',
                'group' => 'general',
            ],
            [
                'key' => 'default_meta_title',
                'value' => 'NextGenBeing вЂ” AI operating playbooks',
                'group' => 'seo',
            ],
            [
                'key' => 'default_meta_description',
                'value' => 'High-signal workflows, tooling breakdowns, and systems for ambitious builders.',
                'group' => 'seo',
            ],
            [
                'key' => 'default_meta_keywords',
                'value' => 'NextGenBeing, AI playbooks, startup operating system, productivity, automations',
                'group' => 'seo',
            ],
            [
                'key' => 'default_meta_image',
                'value' => '/uploads/meta-default.png',
                'group' => 'seo',
                'description' => 'Fallback Open Graph image when a page does not supply one',
            ],
            [
                'key' => 'support_email',
                'value' => 'support@nextgenbeing.com',
                'group' => 'general',
                'description' => 'Displayed in policies, structured data, and contact surfaces',
            ],
            [
                'key' => 'company_name',
                'value' => 'NextGenBeing',
                'group' => 'general',
            ],
            [
                'key' => 'social_links',
                'value' => json_encode([
                    'twitter' => 'https://twitter.com/nextgenbeing',
                    'linkedin' => 'https://www.linkedin.com/company/nextgenbeing',
                    'youtube' => 'https://youtube.com/@nextgenbeing',
                    'github' => 'https://github.com/nextgenbeing',
                ]),
                'group' => 'social',
                'type' => 'json',
            ],
            [
                'key' => 'social_twitter_handle',
                'value' => '@nextgenbeing',
                'group' => 'social',
            ],
            [
                'key' => 'seo_custom_robots',
                'value' => "User-agent: PerplexityBot\nAllow: /\n",
                'group' => 'seo',
                'is_public' => false,
            ],
        ];

        foreach ($settings as $row) {
            Setting::updateOrCreate(
                ['key' => $row['key']],
                [
                    'value' => $row['value'],
                    'type' => $row['type'] ?? 'string',
                    'description' => $row['description'] ?? null,
                    'group' => $row['group'],
                    'is_public' => $row['is_public'] ?? true,
                ]
            );
        }
    }
}