<?php

return [
    'tutorial_topics' => [
        // Beginner (40%)
        'beginner' => [
            'ChatGPT Basics: Getting Started with AI Conversations',
            'Prompt Engineering 101: Writing Effective Prompts',
            'Claude vs ChatGPT: Which AI Should You Use?',
            'Midjourney for Beginners: Creating AI Art',
            'AI Content Writing: Blog Posts in Minutes',
            'Using AI for Research and Summarization',
            'AI Productivity Hacks for Busy Professionals',
            'Setting Up Your First AI Workflow',
            'Understanding AI: Machine Learning Basics',
            'Free AI Tools Every Beginner Should Know',
            'Introduction to Natural Language Processing',
            'Getting Started with AI Image Generation',
        ],

        // Intermediate (40%)
        'intermediate' => [
            'Advanced ChatGPT Techniques: Custom Instructions and GPTs',
            'Building AI Automation with Make.com and Zapier',
            'Prompt Chaining: Multi-Step AI Workflows',
            'Using Claude for Code Generation and Debugging',
            'AI-Powered Content Strategy and SEO',
            'Creating AI Agents with Custom Tools',
            'Fine-Tuning Prompts for Specific Industries',
            'Integrating AI APIs into Your Applications',
            'AI Image Generation: Stable Diffusion vs Midjourney',
            'Voice AI: ElevenLabs and Text-to-Speech Workflows',
            'Building Chatbots with AI for Customer Support',
            'Advanced Prompt Engineering Techniques',
            'Using AI for Data Analysis and Insights',
            'Creating AI-Powered SaaS Tools',
        ],

        // Advanced (20%)
        'advanced' => [
            'Building Production AI Applications with LangChain',
            'AI Agent Architecture: Tools, Memory, and Planning',
            'Retrieval-Augmented Generation (RAG) Implementation',
            'Fine-Tuning Open Source LLMs for Custom Tasks',
            'AI Security and Prompt Injection Prevention',
            'Scaling AI Workflows: Cost Optimization and Performance',
            'Building Multi-Agent Systems for Complex Tasks',
            'AI Model Comparison: Benchmarking and Selection',
            'Implementing Vector Databases for AI Applications',
            'Building Enterprise AI Solutions',
            'Advanced RAG Techniques for Complex QA Systems',
            'Deploying AI Models to Production Safely',
        ],
    ],

    'weekly_schedule' => [
        'monday' => ['type' => 'beginner', 'frequency' => 'weekly'],
        'wednesday' => ['type' => 'intermediate', 'frequency' => 'weekly'],
        'friday' => ['type' => 'advanced', 'frequency' => 'weekly'],
    ],

    'content_mix' => [
        'tutorials' => 60,        // Step-by-step tutorials
        'comparisons' => 20,      // Tool comparisons
        'case_studies' => 10,     // Real-world examples
        'prompt_libraries' => 10, // Downloadable prompts
    ],

    'enabled' => env('AI_LEARNING_ENABLED', true),
    'tutorial_generation_enabled' => env('TUTORIAL_GENERATION_ENABLED', true),
    'prompt_library_enabled' => env('PROMPT_LIBRARY_ENABLED', true),
];
