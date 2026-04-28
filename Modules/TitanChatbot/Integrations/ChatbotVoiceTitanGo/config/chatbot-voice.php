<?php

return [
    'version' => 1.0,
    'avatars' => [
        'avatar-1.png',
    ],

    // Titan Go (Voice IO + Session Layer) — additive config
    'titan_go' => [
            'pro_url' => '/dashboard',
            'beep' => false,
            'hold_to_talk' => false,
            'streaming' => [
                'enabled' => false,
            ],
            // If true, buttons are restricted until Titan Zero provides validity hints.
            'restrict_buttons_until_hint' => true,
            // Pro mode uses shorter silence timers (override to office values)
            'pro_mode_uses_office_timers' => true,
        // Silence timers in milliseconds
        'silence' => [
            'office' => [
                'prompt_ms' => 120000, // 2 minutes
                'end_ms'    => 240000, // 4 minutes
            ],
            'jobsite' => [
                'prompt_ms' => 180000, // 3 minutes
                'end_ms'    => 360000, // 6 minutes
            ],
            'driving' => [
                'prompt_ms' => 240000, // 4 minutes
                'end_ms'    => 480000, // 8 minutes
            ],
            'unknown' => [
                'prompt_ms' => 120000,
                'end_ms'    => 240000,
            ],
        ],
    ],
];
