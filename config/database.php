<?php
/**
 * Database + application configuration.
 * Edit these values to match your local MySQL setup (XAMPP / WAMP / Laragon defaults shown).
 */
if (!defined('BASE_PATH')) {
    http_response_code(403);
    exit('Direct access denied.');
}

return [
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'social_app',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name'            => 'LyfeFeed',
        'timezone'        => 'Asia/Manila',
        'upload_max_size' => 2 * 1024 * 1024,           // 2 MB
        'allowed_types'   => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'per_page'        => 10,                          // posts per feed page
    ],
];
