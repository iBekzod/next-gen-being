<?php

return [
    /**
     * Daily Publication Configuration
     * Controls the strategic 3-article daily publication mix
     */
    'daily_publication' => [
        'enabled' => env('DAILY_PUBLICATION_ENABLED', true),
        'total_posts' => 3,
        'original_posts' => 1,        // Pure AI-generated without references
        'aggregated_posts' => 2,      // Curated from multiple sources with citations
        'free_percentage' => 70,      // 70% free (2 free, 1 premium per day)
    ],

    /**
     * Featured Posts Configuration
     * Auto-feature trending posts based on engagement
     */
    'featured_posts' => [
        'auto_feature_enabled' => env('AUTO_FEATURE_ENABLED', true),
        'trending_period' => '7days', // '3days', '7days', '14days', '30days'
        'max_featured' => 5,          // Max posts to feature at once
        'featured_duration_days' => 30, // How long to keep post featured
        'refresh_time' => '18:00',    // When to run featured post update (6 PM)
    ],

    /**
     * AI Content Generation Configuration
     */
    'ai_generation' => [
        'default_provider' => env('AI_PROVIDER', 'groq'),
        'original_content_model' => 'llama-3.3-70b-versatile', // Groq model for original posts
        'original_content_model_openai' => 'gpt-4-turbo-preview', // OpenAI fallback
        'aggregated_content_model' => 'claude-3-5-sonnet-20241022', // Claude for aggregations
    ],

    /**
     * Content Aggregation Configuration
     */
    'aggregation' => [
        'min_confidence_score' => 0.75, // Only aggregate if 75%+ confident
        'min_sources' => 2,              // Minimum sources to aggregate
        'max_sources' => 5,              // Maximum sources to aggregate
        'fact_preservation_threshold' => 0.60, // 60% word match for fact validation
    ],

    /**
     * Trending Calculation Weights
     * Used by TrendingService to calculate trending score
     */
    'trending_weights' => [
        'likes' => 5.0,      // Highest weight - quality engagement
        'comments' => 3.0,   // Medium weight - discussion
        'views' => 0.5,      // Lower weight - just exposure
    ],

    /**
     * Content Generation Scheduling
     */
    'schedule' => [
        'scrape' => '06:00',           // Scrape sources
        'deduplicate' => '08:00',      // Find & group duplicates
        'paraphrase' => '10:00',       // Paraphrase 3 aggregations
        'prepare_review' => '12:00',   // Prepare admin notifications
        'publish' => '14:00',          // Publish daily content (NEW)
        'translate' => '11:00',        // Translate curated posts
        'update_featured' => '18:00',  // Update featured posts (NEW)
    ],

    /**
     * Paraphrase/Translation Configuration
     */
    'paraphrase' => [
        'timeout_seconds' => 180,
        'max_tokens' => 8000,
        'daily_limit' => 3,
        'retry_attempts' => 3,
    ],

    /**
     * Translation Configuration
     */
    'translation' => [
        'enabled' => true,
        'default_languages' => ['es', 'fr', 'de'], // Spanish, French, German
        'daily_post_limit' => 5,
        'languages_per_post' => 4,
    ],

    /**
     * Publishing Premium Split
     * Controls free vs premium content publication ratio
     */
    'publishing' => [
        'daily_limit' => 10,           // Max posts to publish per day (legacy config)
        'premium_percent_default' => 30, // Default premium split (legacy)
        'daily_premium_percent' => 30,   // Used by DailyContentPublicationCommand
        'schedule_premium_probability' => true, // Use rolling 10-day average
    ],

    /**
     * Content Types
     */
    'content_types' => [
        'original' => 'original',
        'aggregated' => 'aggregated',
        'curated' => 'curated',
    ],

    /**
     * Source Trust Levels
     * Minimum trust score required to include in aggregation
     */
    'source_trust_levels' => [
        'minimum_trust' => 80,  // 0-100 scale
        'high_trust' => 90,     // For priority inclusion
    ],
];
