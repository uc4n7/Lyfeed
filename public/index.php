<?php
/**
 * Front controller. Every request enters here.
 */
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

// Secure session settings
$isHttps = !empty($_SERVER['HTTPS'])
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly'  => true,
    'samesite' => 'Lax',
    'secure'   => $isHttps,
]);
session_start();

// Basic security headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net 'unsafe-inline'; style-src 'self' https://cdn.jsdelivr.net https://fonts.googleapis.com 'unsafe-inline'; font-src 'self' https://cdn.jsdelivr.net https://fonts.gstatic.com; img-src 'self' data: blob:; frame-ancestors 'self';");

require_once BASE_PATH . '/app/core/helpers.php';
date_default_timezone_set(config('app.timezone', 'Asia/Manila'));
require_once BASE_PATH . '/app/core/Database.php';
require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/core/Router.php';

foreach (['UserModel', 'PostModel', 'CommentModel', 'LikeModel'] as $model) {
    require_once BASE_PATH . "/app/models/{$model}.php";
}

(new Router())->dispatch();
