<?php

declare(strict_types=1);

/**
 * EduCare Demo — bootstrap & helpers (self-contained module).
 * Developer logs go to the PHP error log when APP_DEBUG=1.
 */
date_default_timezone_set('Asia/Bangkok');

define('EDU_ROOT', dirname(__DIR__));
define('EDU_DEBUG', filter_var(getenv('APP_DEBUG') ?: '0', FILTER_VALIDATE_BOOLEAN));

if (session_status() === PHP_SESSION_NONE && PHP_SAPI !== 'cli') {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

function educare_log(string $module, string $function, string $message, array $context = [], bool $isError = false): void
{
    if (!EDU_DEBUG && !$isError) {
        return;
    }
    $line = sprintf('[%s][%s] %s', $module, $function, $message);
    if ($context !== []) {
        $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
    }
    error_log($line);
}

function educare_config(?string $key = null, mixed $default = null): mixed
{
    static $config = null;
    $config ??= require EDU_ROOT . '/config/app.php';
    if ($key === null) {
        return $config;
    }
    $value = $config;
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }
    return $value;
}

/** Escape for HTML output (XSS protection). */
function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

function edu_asset(string $path): string
{
    $file = EDU_ROOT . '/' . ltrim($path, '/');
    return ltrim($path, '/') . (is_file($file) ? '?v=' . filemtime($file) : '');
}

function edu_json(mixed $data): string
{
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?: '{}';
}

/** Validate a role from user input against the configured whitelist. */
function edu_role(mixed $value): ?string
{
    $role = is_string($value) ? strtolower(trim($value)) : '';
    return array_key_exists($role, educare_config('roles')) ? $role : null;
}

/** EduCare logo: a book that opens into a heart-shaped sprout. */
function edu_logo(string $class = 'logo-mark'): string
{
    return '<svg class="' . h($class) . '" viewBox="0 0 64 64" aria-hidden="true" focusable="false">'
        . '<defs><linearGradient id="edu-g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#0EA5E9"/><stop offset="1" stop-color="#2563EB"/></linearGradient></defs>'
        . '<rect width="64" height="64" rx="18" fill="url(#edu-g)"/>'
        . '<path d="M14 42c6-3 12-3 18 1 6-4 12-4 18-1V22c-6-3-12-3-18 1-6-4-12-4-18-1z" fill="#fff" opacity=".95"/>'
        . '<path d="M32 23v20" stroke="#2563EB" stroke-width="2.4" stroke-linecap="round"/>'
        . '<path d="M32 18c-1.8-3.6-7-3-6.4 1 .5 3 6.4 6.6 6.4 6.6s5.9-3.6 6.4-6.6c.6-4-4.6-4.6-6.4-1z" fill="#FDE68A"/>'
        . '</svg>';
}
