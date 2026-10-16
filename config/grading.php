<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Grading Categories & Donut Chart Legend Definition
    |--------------------------------------------------------------------------
    |
    | Defines the standard 6 groups displayed on the donut chart and
    | summary distributions as specified in docs/data-canon.md.
    |
    */
    'categories' => [
        'A' => [
            'label' => 'A (Sangat Baik)',
            'letters' => ['A', 'A-'],
            'color' => '#2563EB', // Blue Primary
            'bg_class' => 'bg-primary',
        ],
        'B' => [
            'label' => 'B (Baik)',
            'letters' => ['B+', 'B'],
            'color' => '#06B6D4', // Cyan
            'bg_class' => 'bg-cyan-500',
        ],
        'B-' => [
            'label' => 'B- (Cukup Baik)',
            'letters' => ['B-'],
            'color' => '#F59E0B', // Amber
            'bg_class' => 'bg-amber-500',
        ],
        'C' => [
            'label' => 'C (Cukup)',
            'letters' => ['C+', 'C'],
            'color' => '#F97316', // Orange
            'bg_class' => 'bg-orange-500',
        ],
        'D' => [
            'label' => 'D (Kurang)',
            'letters' => ['D'],
            'color' => '#EC4899', // Pink
            'bg_class' => 'bg-pink-500',
        ],
        'E' => [
            'label' => 'E (Gagal)',
            'letters' => ['E'],
            'color' => '#EF4444', // Red
            'bg_class' => 'bg-rose-500',
        ],
    ],
];
