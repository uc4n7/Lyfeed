<?php
/**
 * Global helper functions: output escaping, URLs, sessions, flash messages, CSRF, uploads.
 */

/** Escape output (XSS guard). Use on EVERY dynamic value printed in a view. */
function e($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Sanitize text input: trim, strip NUL/control chars. Output is still escaped with e(). */
function clean(?string $value): string
{
    $value = trim((string)$value);
    return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';
}

function config(string $key, $default = null)
{
    static $cfg = null;
    $cfg ??= require BASE_PATH . '/config/database.php';
    $value = $cfg;
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

// Set timezone from configuration
date_default_timezone_set(config('app.timezone', 'Asia/Manila'));

/** Base URL of the public/ folder, works in sub-directories (e.g. /social_app/public). */
function base_url(): string
{
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    return $dir;
}

/** Build a route URL: url('post/create') or url('profile/show', ['id' => 3]) */
function url(string $route = '', array $params = []): string
{
    $query = $route === '' ? [] : ['route' => $route];
    $query = array_merge($query, $params);
    return base_url() . '/index.php' . ($query ? '?' . http_build_query($query) : '');
}

function asset(string $path): string
{
    return base_url() . '/assets/' . ltrim($path, '/');
}

function upload_url(?string $file): string
{
    return base_url() . '/assets/uploads/' . rawurlencode((string)$file);
}

function avatar_url(?string $file): string
{
    return $file ? upload_url($file) : asset('img/default-avatar.svg');
}

function cover_url(?string $file): ?string
{
    return $file ? upload_url($file) : null;
}

function redirect(string $route, array $params = []): void
{
    header('Location: ' . url($route, $params));
    exit;
}

/* ---------- Session / Auth ---------- */

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function current_user_id(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

/* ---------- Flash messages ---------- */

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flash(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/* ---------- Old input for forms ---------- */

function old(string $key, string $default = ''): string
{
    return e($_SESSION['old'][$key] ?? $default);
}

function remember_old(array $data): void
{
    $_SESSION['old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['old']);
}

/* ---------- CSRF ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** Rotate the CSRF token (call after login/logout to prevent fixation). */
function csrf_regenerate(): void
{
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $token = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return is_string($token) && hash_equals($_SESSION['csrf'] ?? '', $token);
}



/**
 * Simple session-based rate limiter.
 * Returns true if the action is allowed, false if rate-limited.
 */
function rate_limit(string $key, int $maxAttempts = 5, int $windowSeconds = 300): bool
{
    $sessionKey = "rate_limit_{$key}";
    $now = time();
    $attempts = $_SESSION[$sessionKey] ?? [];
    $attempts = array_filter($attempts, function ($t) use ($now, $windowSeconds) {
        return $t > ($now - $windowSeconds);
    });
    if (count($attempts) >= $maxAttempts) {
        $_SESSION[$sessionKey] = $attempts;
        return false; // rate limited
    }
    $attempts[] = $now;
    $_SESSION[$sessionKey] = $attempts;
    return true;
}

/*  Password validation  */

/** Enforce password strength: at least 8 characters, max 128. */
function validate_password(string $password): array
{
    $errors = [];
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if (strlen($password) > 128) {
        $errors[] = 'Password must be 128 characters or fewer.';
    }
    return $errors;
}

/*  Time  */

function time_ago(string $datetime): string
{
    $timestamp = strtotime($datetime);
    if ($timestamp === false) {
        return $datetime;
    }
    $diff = time() - $timestamp;
    if ($diff < 0) {
        $diff = 0;
    }
    if ($diff < 45)     return 'Just now';
    if ($diff < 90)     return '1 min ago';
    if ($diff < 3600)   return floor($diff / 60) . ' mins ago';
    if ($diff < 7200)   return '1 hr ago';
    if ($diff < 86400)  return floor($diff / 3600) . ' hrs ago';
    if ($diff < 172800) return 'Yesterday';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M j, Y', $timestamp);
}

/*  Image upload  */

/**
 * Validate and store an uploaded image.
 * Returns [filename|null, error|null]. No file uploaded => [null, null].
 */
function handle_image_upload(string $field): array
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }

    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Image upload failed. Please try again.'];
    }
    if ($file['size'] > config('app.upload_max_size')) {
        return [null, 'Image must be 2 MB or smaller.'];
    }

    // Check the real MIME type from file contents, not the client-provided one.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $ext   = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'][$mime] ?? null;

    if (!$ext || !in_array($mime, config('app.allowed_types'), true) || @getimagesize($file['tmp_name']) === false) {
        return [null, 'Only JPG, PNG, GIF, or WEBP images are allowed.'];
    }

    $name = bin2hex(random_bytes(16)) . '.' . $ext;     // random name, never trust the original
    $dest = BASE_PATH . '/public/assets/uploads/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return [null, 'Could not save the uploaded image.'];
    }
    return [$name, null];
}

function delete_upload(?string $file): void
{
    if ($file) {
        $path = BASE_PATH . '/public/assets/uploads/' . basename($file);
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
