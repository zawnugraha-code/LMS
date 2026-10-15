<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Arunika Application Configuration
    |--------------------------------------------------------------------------
    |
    | General configuration for the Arunika LMS platform.
    |
    */

    'name' => env('APP_NAME', 'Arunika'),

    /*
    |--------------------------------------------------------------------------
    | Default Timezone for Display
    |--------------------------------------------------------------------------
    |
    | All timestamps are stored in UTC. This is the default display timezone
    | used when a user hasn't set their preference.
    |
    */

    'default_timezone' => env('ARUNIKA_TIMEZONE', 'Asia/Jakarta'),

    /*
    |--------------------------------------------------------------------------
    | Grading Configuration
    |--------------------------------------------------------------------------
    */

    'grading' => [
        // Default letter grade conversion table
        'scale' => [
            'A' => ['min' => 85, 'max' => 100, 'point' => 4.00],
            'A-' => ['min' => 80, 'max' => 84.99, 'point' => 3.75],
            'B+' => ['min' => 75, 'max' => 79.99, 'point' => 3.50],
            'B' => ['min' => 70, 'max' => 74.99, 'point' => 3.00],
            'B-' => ['min' => 65, 'max' => 69.99, 'point' => 2.75],
            'C+' => ['min' => 60, 'max' => 64.99, 'point' => 2.50],
            'C' => ['min' => 55, 'max' => 59.99, 'point' => 2.00],
            'D' => ['min' => 40, 'max' => 54.99, 'point' => 1.00],
            'E' => ['min' => 0, 'max' => 39.99, 'point' => 0.00],
        ],

        // Minimum passing grade
        'passing_grade' => 'C',
    ],

    /*
    |--------------------------------------------------------------------------
    | Gamification Defaults
    |--------------------------------------------------------------------------
    */

    'gamification' => [
        'enabled' => true,
        'xp_per_assignment_submit' => 10,
        'xp_per_quiz_complete' => 15,
        'xp_per_forum_post' => 5,
        'xp_per_attendance' => 5,
        'xp_per_material_view' => 2,
        'streak_bonus_multiplier' => 1.5,
        'leaderboard_opt_in_default' => false,
        'season_duration_weeks' => 8,
    ],

    /*
    |--------------------------------------------------------------------------
    | Risk Scoring (Early Warning)
    |--------------------------------------------------------------------------
    */

    'risk' => [
        'weights' => [
            'attendance' => 0.25,
            'assignment_completion' => 0.25,
            'quiz_performance' => 0.20,
            'activity' => 0.15,
            'trend' => 0.15,
        ],
        'thresholds' => [
            'low' => 30,
            'medium' => 60,
            'high' => 80,
        ],
        'nudge_enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Attendance
    |--------------------------------------------------------------------------
    */

    'attendance' => [
        'qr_refresh_seconds' => 15,
        'geofence_enabled' => false,
        'geofence_radius_meters' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Limits
    |--------------------------------------------------------------------------
    */

    'uploads' => [
        'max_file_size_mb' => 50,
        'allowed_document_types' => ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'md'],
        'allowed_image_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
        'allowed_video_types' => ['mp4', 'webm', 'ogg'],
        'allowed_archive_types' => ['zip', 'rar', '7z'],
    ],

    /*
    |--------------------------------------------------------------------------
    | OBE (Outcome Based Education)
    |--------------------------------------------------------------------------
    */

    'obe' => [
        'min_cpl_score' => 55.00,
        'calculation_method' => 'weighted_average', // weighted_average, simple_average
    ],
];
