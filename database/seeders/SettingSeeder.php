<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'site_name',
                'value' => 'NextGenBeing',
                'type' => 'string',
                'description' => 'Site name displayed in header and meta tags',
                'group' => 'general',
                'is_public' => true,
            ],
            [
                'key' => 'site_description',
                'value' => 'A professional blog platform for tech enthusiasts and developers',
                'type' => 'string',
                'description' => 'Site description for SEO and meta tags',
                'group' => 'general',
                'is_public' => true,
            ],
            [
                'key' => 'site_logo',
                'value' => '/uploads/logo.png',
                'type' => 'string',
                'description' => 'Site logo URL',
                'group' => 'general',
                'is_public' => true,
            ],

            // Content Settings
            [
                'key' => 'posts_per_page',
                'value' => '12',
                'type' => 'integer',
                'description' => 'Number of posts to display per page',
                'group' => 'content',
                'is_public' => true,
            ],
            [
                'key' => 'auto_approve_comments',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Automatically approve comments from trusted users',
                'group' => 'content',
                'is_public' => false,
            ],
            [
                'key' => 'enable_comment_moderation',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable comment moderation',
                'group' => 'content',
                'is_public' => false,
            ],

            // Subscription Settings
            [
                'key' => 'enable_subscriptions',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable subscription functionality',
                'group' => 'subscription',
                'is_public' => true,
            ],
            [
                'key' => 'trial_period_days',
                'value' => '7',
                'type' => 'integer',
                'description' => 'Free trial period in days',
                'group' => 'subscription',
                'is_public' => true,
            ],

            // AI Settings
            [
                'key' => 'enable_ai_suggestions',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable AI content suggestions',
                'group' => 'ai',
                'is_public' => false,
            ],
            [
                'key' => 'ai_suggestion_frequency',
                'value' => 'daily',
                'type' => 'string',
                'description' => 'How often to generate AI suggestions (daily, weekly, monthly)',
                'group' => 'ai',
                'is_public' => false,
            ],

            // SEO Settings
            [
                'key' => 'default_meta_title',
                'value' => 'TechBlog Pro - Latest Tech News and Tutorials',
                'type' => 'string',
                'description' => 'Default meta title for pages',
                'group' => 'seo',
                'is_public' => true,
            ],
            [
                'key' => 'default_meta_description',
                'value' => 'Stay updated with the latest technology trends, tutorials, and industry insights.',
                'type' => 'string',
                'description' => 'Default meta description for pages',
                'group' => 'seo',
                'is_public' => true,
            ],

            // Social Media
            [
                'key' => 'social_links',
                'value' => json_encode([
                    'twitter' => 'https://twitter.com/nextgenbeing',
                    'linkedin' => 'https://linkedin.com/company/nextgenbeing',
                    'github' => 'https://github.com/nextgenbeing',
                    'youtube' => 'https://youtube.com/nextgenbeing',
                ]),
                'type' => 'json',
                'description' => 'Social media links',
                'group' => 'social',
                'is_public' => true,
            ],

            // Analytics
            [
                'key' => 'google_analytics_id',
                'value' => '',
                'type' => 'string',
                'description' => 'Google Analytics tracking ID',
                'group' => 'analytics',
                'is_public' => false,
            ],
            [
                'key' => 'enable_analytics',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable analytics tracking',
                'group' => 'analytics',
                'is_public' => false,
            ],
            [
                'key' => 'default_meta_keywords',
                'value' => 'AI workflows, productivity systems, NextGenBeing, startup operating systems',
                'type' => 'string',
                'description' => 'Default meta keywords for pages',
                'group' => 'seo',
                'is_public' => true,
            ],
            [
                'key' => 'default_meta_image',
                'value' => '/uploads/meta-default.png',
                'type' => 'string',
                'description' => 'Fallback image used for Open Graph and Twitter cards',
                'group' => 'seo',
                'is_public' => true,
            ],
            [
                'key' => 'company_name',
                'value' => 'NextGenBeing',
                'type' => 'string',
                'description' => 'Legal name of the company or founder behind the publication',
                'group' => 'general',
                'is_public' => true,
            ],
            [
                'key' => 'support_email',
                'value' => 'support@nextgenbeing.com',
                'type' => 'string',
                'description' => 'Primary contact email shown in policies and structured data',
                'group' => 'general',
                'is_public' => true,
            ],
            [
                'key' => 'social_twitter_handle',
                'value' => '@nextgenbeing',
                'type' => 'string',
                'description' => 'Twitter / X handle used for cards and organization schema',
                'group' => 'social',
                'is_public' => true,
            ],
            [
                'key' => 'seo_custom_robots',
                'value' => '',
                'type' => 'string',
                'description' => 'Additional directives appended to robots.txt (one per line)',
                'group' => 'seo',
                'is_public' => false,
            ],
        ];

        foreach ($settings as $settingData) {
            Setting::updateOrCreate(
                ['key' => $settingData['key']],
                $settingData
            );
        }
    }
}
