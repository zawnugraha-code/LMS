<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Provider Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the AI provider used by Arunika's AI Copilot features.
    | All AI calls go through a queue with rate limiting and usage logging.
    |
    */

    'provider' => env('AI_PROVIDER', 'gemini'),

    'providers' => [

        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
            'embedding_model' => env('GEMINI_EMBEDDING_MODEL', 'text-embedding-004'),
            'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
            'timeout' => 30,
        ],

        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'timeout' => 30,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */

    'rate_limit' => [
        'per_minute' => (int) env('AI_RATE_LIMIT_PER_MINUTE', 30),
        'per_day' => (int) env('AI_RATE_LIMIT_PER_DAY', 1000),
        'per_user_per_hour' => (int) env('AI_RATE_LIMIT_PER_USER_HOUR', 20),
    ],

    /*
    |--------------------------------------------------------------------------
    | Vector Store
    |--------------------------------------------------------------------------
    */

    'vector_store' => env('VECTOR_STORE_DRIVER', 'mysql'),

    'vector_stores' => [

        'mysql' => [
            'table' => 'ai_chunks',
            'embedding_dimensions' => 768,
            'similarity_threshold' => 0.7,
        ],

        'qdrant' => [
            'host' => env('QDRANT_HOST', 'http://localhost:6333'),
            'api_key' => env('QDRANT_API_KEY'),
            'collection' => env('QDRANT_COLLECTION', 'arunika'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Chunking Configuration
    |--------------------------------------------------------------------------
    */

    'chunking' => [
        'max_chunk_size' => 1000,
        'chunk_overlap' => 200,
    ],

    /*
    |--------------------------------------------------------------------------
    | Guardrails
    |--------------------------------------------------------------------------
    */

    'guardrails' => [
        'block_exam_answers' => true,
        'require_source_citation' => true,
        'max_context_chunks' => 10,
        'socratic_mode_for_assignments' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Toggles
    |--------------------------------------------------------------------------
    */

    'features' => [
        'copilot_enabled' => env('AI_COPILOT_ENABLED', true),
        'auto_flashcard' => env('AI_AUTO_FLASHCARD', true),
        'draft_quiz_generation' => env('AI_DRAFT_QUIZ', true),
        'forum_summary' => env('AI_FORUM_SUMMARY', true),
    ],
];
