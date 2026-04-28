<?php
return [
    'name' => 'TitanChatbot',
    'route_prefix' => 'titan-chatbot',
    'api_prefix' => 'api/titan-chatbot',
    'source_archives_path' => base_path('Modules/TitanChatbot/Upgrade/Sources'),
    'pwa' => [
        'enabled' => true,
        'manifest' => '/titan-chatbot/manifest.json',
        'service_worker' => '/titan-chatbot/sw.js',
    ],
];
