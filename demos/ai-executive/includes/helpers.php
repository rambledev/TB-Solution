<?php

declare(strict_types=1);

/**
 * AI Executive Dashboard — helpers (self-contained).
 * Debug logs are written to the PHP error log when APP_DEBUG=1.
 */

define('EXE_ROOT', dirname(__DIR__));
define('EXE_DEBUG', filter_var(getenv('APP_DEBUG') ?: '0', FILTER_VALIDATE_BOOLEAN));

/**
 * Developer log line: [Module][Function] message {context}.
 */
function exe_log(string $module, string $function, string $message, array $context = [], bool $isError = false): void
{
    if (!EXE_DEBUG && !$isError) {
        return;
    }
    $line = sprintf('[%s][%s] %s', $module, $function, $message);
    if ($context !== []) {
        $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
    }
    error_log($line);
}

/**
 * Config value by dot notation, e.g. exe_config('school.name').
 */
function exe_config(?string $key = null, mixed $default = null): mixed
{
    static $config = null;
    $config ??= require EXE_ROOT . '/config/config.php';
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

function exe_e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

function exe_asset(string $path): string
{
    $file = EXE_ROOT . '/' . ltrim($path, '/');
    return ltrim($path, '/') . (is_file($file) ? '?v=' . filemtime($file) : '');
}

function exe_icon(string $name, string $class = ''): string
{
    return '<i data-lucide="' . exe_e($name) . '"' . ($class !== '' ? ' class="' . exe_e($class) . '"' : '') . ' aria-hidden="true"></i>';
}

function exe_json(mixed $data): string
{
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PRESERVE_ZERO_FRACTION) ?: '{}';
}

/** TB-Solution logo mark (inline SVG). */
function exe_logo(string $id = 'logo'): string
{
    $g = 'exg-' . preg_replace('/[^a-z0-9-]/i', '', $id);
    return <<<SVG
<svg class="brand__mark" viewBox="0 0 64 64" aria-hidden="true" focusable="false">
  <defs><linearGradient id="{$g}" x1="6" y1="4" x2="58" y2="60" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#60A5FA"/><stop offset=".55" stop-color="#2563EB"/><stop offset="1" stop-color="#1E40AF"/></linearGradient></defs>
  <rect width="64" height="64" rx="18" fill="url(#{$g})"/>
  <path d="M12 19h29a6.5 6.5 0 0 1 0 13h-7M34 32h9a7 7 0 0 1 0 14h-9V19" fill="none" stroke="#fff" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M23 19v27" fill="none" stroke="#fff" stroke-width="6" stroke-linecap="round"/>
  <path d="M52 5.5Q52 11.5 58 11.5Q52 11.5 52 17.5Q52 11.5 46 11.5Q52 11.5 52 5.5Z" fill="#C4B5FD"/>
</svg>
SVG;
}
