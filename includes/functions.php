<?php

declare(strict_types=1);

/**
 * TB-Solution — shared helpers.
 *
 * Debug logging is enabled with the APP_DEBUG=1 environment variable and is
 * written to the PHP error log (stderr when using `php -S`).
 */

define('TB_ROOT', dirname(__DIR__));
define('TB_DEBUG', filter_var(getenv('APP_DEBUG') ?: '0', FILTER_VALIDATE_BOOLEAN));

/**
 * Write a developer log line: [Module][Function] message {context}.
 */
function tb_log(string $module, string $function, string $message, array $context = [], bool $isError = false): void
{
    if (!TB_DEBUG && !$isError) {
        return;
    }

    $line = sprintf('[%s][%s] %s', $module, $function, $message);
    if ($context !== []) {
        $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
    }
    error_log($line);
}

/**
 * Read a config value using dot notation, e.g. tb_config('brand.name').
 * Loads config/site.php, config/content.php and config/demos.php once per request.
 */
function tb_config(?string $key = null, mixed $default = null): mixed
{
    static $config = null;

    if ($config === null) {
        tb_log('Config', 'tb_config', 'START load');
        $config = require TB_ROOT . '/config/site.php';
        $config['content'] = require TB_ROOT . '/config/content.php';
        $config['demos'] = require TB_ROOT . '/config/demos.php';
        tb_log('Config', 'tb_config', 'END load', ['keys' => array_keys($config), 'demos' => count($config['demos'])]);
    }

    if ($key === null) {
        return $config;
    }

    $value = $config;
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            tb_log('Config', 'tb_config', 'missing key, using default', ['key' => $key]);
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

/**
 * Escape any value for safe HTML output (XSS protection).
 * Intentionally not logged: called for every printed value.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

/**
 * Return a relative asset path with a cache-busting version query.
 */
function asset(string $path): string
{
    $path = ltrim($path, '/');
    $file = TB_ROOT . '/' . $path;
    $url = is_file($file) ? $path . '?v=' . filemtime($file) : $path;

    tb_log('View', 'asset', 'resolved', ['path' => $path, 'url' => $url]);
    return $url;
}

/**
 * Allow only safe URL schemes (http/https/mailto/tel), anchors and relative paths.
 * Returns an empty string for anything else (e.g. javascript:).
 */
function tb_safe_url(?string $url): string
{
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }

    if (preg_match('/^[a-z][a-z0-9+.-]*:/i', $url) === 1 && preg_match('#^(https?://|mailto:|tel:)#i', $url) !== 1) {
        tb_log('Security', 'tb_safe_url', 'rejected unsafe url', ['url' => $url], true);
        return '';
    }

    return $url;
}

/**
 * Build the contact link for a CTA button.
 *
 * @param 'contact'|'consult' $type
 * @return array{href: string, pending: bool}
 */
function tb_contact_link(string $type): array
{
    tb_log('Contact', 'tb_contact_link', 'START', ['type' => $type]);

    $contact = tb_config('contact', []);
    $url = tb_safe_url($contact[$type . '_url'] ?? '');

    if ($url === '' && $type === 'contact' && !empty($contact['email'])) {
        $url = 'mailto:' . $contact['email'];
    }
    if ($url === '' && $type === 'consult' && !empty($contact['line_url'])) {
        $url = tb_safe_url($contact['line_url']);
    }

    $result = ['href' => $url !== '' ? $url : '#contact', 'pending' => $url === ''];
    tb_log('Contact', 'tb_contact_link', 'END', $result);
    return $result;
}

/**
 * Contact channels that have a value, ready for display.
 *
 * @return list<array{icon: string, label: string, value: string, href: string}>
 */
function tb_contact_channels(): array
{
    tb_log('Contact', 'tb_contact_channels', 'START');

    $c = tb_config('contact', []);
    $channels = [];

    if (!empty($c['email'])) {
        $channels[] = ['icon' => 'mail', 'label' => 'อีเมล', 'value' => $c['email'], 'href' => 'mailto:' . $c['email']];
    }
    if (!empty($c['phone'])) {
        $channels[] = ['icon' => 'phone', 'label' => 'โทรศัพท์', 'value' => $c['phone'], 'href' => 'tel:' . preg_replace('/[^0-9+]/', '', $c['phone'])];
    }
    if (!empty($c['line_id'])) {
        $channels[] = ['icon' => 'message', 'label' => 'LINE', 'value' => $c['line_id'], 'href' => tb_safe_url($c['line_url'] ?? '')];
    }
    if (!empty($c['address'])) {
        $channels[] = ['icon' => 'map-pin', 'label' => 'ที่อยู่', 'value' => $c['address'], 'href' => ''];
    }

    tb_log('Contact', 'tb_contact_channels', 'END', ['count' => count($channels)]);
    return $channels;
}

/**
 * Normalise demo entries from config/demos.php and drop invalid ones.
 *
 * @return list<array<string, mixed>>
 */
function tb_demos(): array
{
    tb_log('Demo', 'tb_demos', 'START');

    $demos = [];
    foreach (tb_config('demos', []) as $index => $raw) {
        if (!is_array($raw) || empty($raw['slug']) || empty($raw['name'])) {
            tb_log('Demo', 'tb_demos', 'skipped invalid entry (slug and name are required)', ['index' => $index], true);
            continue;
        }

        $demos[] = [
            'slug'        => preg_replace('/[^a-z0-9-]/', '', strtolower((string) $raw['slug'])),
            'name'        => (string) $raw['name'],
            'category'    => (string) ($raw['category'] ?? 'ทั่วไป'),
            'description' => (string) ($raw['description'] ?? ''),
            'image'       => tb_safe_url($raw['image'] ?? ''),
            'tags'        => array_values(array_filter((array) ($raw['tags'] ?? []), 'is_string')),
            'detail_url'  => tb_safe_url($raw['detail_url'] ?? ''),
            'demo_url'    => tb_safe_url($raw['demo_url'] ?? ''),
            'ready'       => ($raw['status'] ?? 'ready') === 'ready',
            'name_en'     => (string) ($raw['name_en'] ?? ''),
            'badge'       => (string) ($raw['badge'] ?? ''),
            'featured'    => !empty($raw['featured']),
        ];
    }

    tb_log('Demo', 'tb_demos', 'END', ['count' => count($demos)]);
    return $demos;
}

/**
 * Render a component from includes/components with local variables.
 */
function tb_component(string $name, array $vars = []): void
{
    tb_log('View', 'tb_component', 'START', ['name' => $name]);

    $file = TB_ROOT . '/includes/components/' . basename($name) . '.php';
    if (!is_file($file)) {
        tb_log('View', 'tb_component', 'ERROR component not found', ['name' => $name, 'file' => $file], true);
        return;
    }

    (static function (string $__file, array $__vars): void {
        extract($__vars, EXTR_SKIP);
        require $__file;
    })($file, $vars);

    tb_log('View', 'tb_component', 'END', ['name' => $name]);
}

/**
 * Render a page section from sections/. A failing section is skipped with a
 * polite message instead of breaking the whole page.
 */
function tb_section(string $name): void
{
    tb_log('View', 'tb_section', 'START', ['name' => $name]);

    $file = TB_ROOT . '/sections/' . basename($name) . '.php';
    ob_start();

    try {
        if (!is_file($file)) {
            throw new RuntimeException('Section file not found: ' . $file);
        }
        (static function (string $__file): void {
            require $__file;
        })($file);
        ob_end_flush();
        tb_log('View', 'tb_section', 'END', ['name' => $name]);
    } catch (Throwable $error) {
        ob_end_clean();
        tb_log('View', 'tb_section', 'ERROR', ['name' => $name, 'error' => $error->getMessage()], true);
        echo '<div class="container section-fallback" role="status">ขออภัย ไม่สามารถแสดงข้อมูลส่วนนี้ได้ในขณะนี้ กรุณาลองใหม่อีกครั้ง</div>';
    }
}

/**
 * Inline SVG of the TB-Solution logo mark. $id keeps gradient ids unique per instance.
 */
function tb_logo_mark(string $id = 'logo', string $class = 'brand__mark'): string
{
    $g = 'tbg-' . preg_replace('/[^a-z0-9-]/i', '', $id);

    return <<<SVG
<svg class="{$class}" viewBox="0 0 64 64" aria-hidden="true" focusable="false">
  <defs>
    <linearGradient id="{$g}" x1="6" y1="4" x2="58" y2="60" gradientUnits="userSpaceOnUse">
      <stop offset="0" stop-color="#4C97F7"/>
      <stop offset=".55" stop-color="#2878E8"/>
      <stop offset="1" stop-color="#173B75"/>
    </linearGradient>
  </defs>
  <rect width="64" height="64" rx="18" fill="url(#{$g})"/>
  <path d="M12 19h29a6.5 6.5 0 0 1 0 13h-7M34 32h9a7 7 0 0 1 0 14h-9V19" fill="none" stroke="#fff" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M23 19v27" fill="none" stroke="#fff" stroke-width="6" stroke-linecap="round"/>
  <path d="M52 5.5Q52 11.5 58 11.5Q52 11.5 52 17.5Q52 11.5 46 11.5Q52 11.5 52 5.5Z" fill="#FFE9A8"/>
</svg>
SVG;
}

/**
 * Inline Lucide-style icon (stroke based, 24x24). Unknown names render nothing.
 * Intentionally not logged: called many times per page.
 */
function tb_icon(string $name, string $class = 'icon'): string
{
    static $icons = [
        'sparkles'           => '<path d="M9.94 15.5a2 2 0 0 0-1.44-1.44l-6.13-1.58a.5.5 0 0 1 0-.96L8.5 9.94A2 2 0 0 0 9.94 8.5l1.58-6.14a.5.5 0 0 1 .96 0l1.58 6.14a2 2 0 0 0 1.44 1.44l6.14 1.58a.5.5 0 0 1 0 .96l-6.14 1.58a2 2 0 0 0-1.44 1.44l-1.58 6.13a.5.5 0 0 1-.96 0z"/><path d="M20 3v4"/><path d="M22 5h-4"/><path d="M4 17v2"/><path d="M5 18H3"/>',
        'monitor-smartphone' => '<path d="M18 8V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h8"/><path d="M10 19v-4"/><path d="M7 19h5"/><rect width="6" height="10" x="16" y="12" rx="2"/>',
        'sliders'            => '<path d="M21 4h-7"/><path d="M10 4H3"/><path d="M21 12h-9"/><path d="M8 12H3"/><path d="M21 20h-5"/><path d="M12 20H3"/><path d="M14 2v4"/><path d="M8 10v4"/><path d="M16 18v4"/>',
        'pointer'            => '<path d="M14 4.1 12 6"/><path d="m5.1 8-2.9-.8"/><path d="m6 12-1.9 2"/><path d="M7.2 2.2 8 5.1"/><path d="M9.04 9.69a.5.5 0 0 1 .65-.65l11 4.5a.5.5 0 0 1-.07.95l-4.35 1.04a1 1 0 0 0-.74.74l-1.04 4.35a.5.5 0 0 1-.95.07z"/>',
        'search'             => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'target'             => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>',
        'trending-up'        => '<path d="m22 7-8.5 8.5-5-5L2 17"/><path d="M16 7h6v6"/>',
        'clipboard'          => '<rect width="8" height="4" x="8" y="2" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>',
        'pen-tool'           => '<path d="M15.7 21.3a1 1 0 0 1-1.4 0l-1.6-1.6a1 1 0 0 1 0-1.4l5.6-5.6a1 1 0 0 1 1.4 0l1.6 1.6a1 1 0 0 1 0 1.4z"/><path d="m18 13-1.38-6.87a1 1 0 0 0-.74-.78L3.24 2.03a1 1 0 0 0-1.21 1.2l3.32 12.65a1 1 0 0 0 .78.74L13 18"/><path d="m2.3 2.3 7.29 7.29"/><circle cx="11" cy="11" r="2"/>',
        'cpu'                => '<rect width="16" height="16" x="4" y="4" rx="2"/><rect width="6" height="6" x="9" y="9" rx="1"/><path d="M15 2v2"/><path d="M15 20v2"/><path d="M2 15h2"/><path d="M2 9h2"/><path d="M20 15h2"/><path d="M20 9h2"/><path d="M9 2v2"/><path d="M9 20v2"/>',
        'rocket'             => '<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>',
        'life-buoy'          => '<circle cx="12" cy="12" r="10"/><path d="m4.93 4.93 4.24 4.24"/><path d="m14.83 9.17 4.24-4.24"/><path d="m14.83 14.83 4.24 4.24"/><path d="m9.17 14.83-4.24 4.24"/><circle cx="12" cy="12" r="4"/>',
        'arrow-right'        => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
        'arrow-up-right'     => '<path d="M7 7h10v10"/><path d="M7 17 17 7"/>',
        'play'               => '<path d="M6 3.5v17a.5.5 0 0 0 .77.42l13.2-8.5a.5.5 0 0 0 0-.84L6.77 3.08A.5.5 0 0 0 6 3.5z"/>',
        'eye'                => '<path d="M2.06 12.35a1 1 0 0 1 0-.7 10.75 10.75 0 0 1 19.88 0 1 1 0 0 1 0 .7 10.75 10.75 0 0 1-19.88 0"/><circle cx="12" cy="12" r="3"/>',
        'mail'               => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
        'phone'              => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/>',
        'message'            => '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/>',
        'map-pin'            => '<path d="M20 10c0 5-5.54 10.19-7.4 11.8a1 1 0 0 1-1.2 0C9.54 20.19 4 15 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>',
        'menu'               => '<path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/>',
        'x'                  => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
        'check'              => '<path d="M20 6 9 17l-5-5"/>',
        'clock'              => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'layout-grid'        => '<rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/>',
        'monitor'            => '<rect width="20" height="14" x="2" y="3" rx="2"/><path d="M8 21h8"/><path d="M12 17v4"/>',
        'tablet'             => '<rect width="16" height="20" x="4" y="2" rx="2"/><path d="M12 18h.01"/>',
        'smartphone'         => '<rect width="14" height="20" x="5" y="2" rx="2"/><path d="M12 18h.01"/>',
        'shield'             => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>',
        'zap'                =>'<path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"/>',
        'bar-chart'          => '<path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/>',
        'users'              => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'file-text'          => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/>',
        'settings'           => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
    ];

    if (!isset($icons[$name])) {
        return '';
    }

    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $icons[$name] . '</svg>';
}
