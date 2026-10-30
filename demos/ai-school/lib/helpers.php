<?php

declare(strict_types=1);

/**
 * AI School Intelligence — view helpers.
 * Shared escaping/logging helpers (e(), tb_log()) come from the TB-Solution core.
 */

/**
 * Read a module config value using dot notation, e.g. ais_config('school.name').
 */
function ais_config(?string $key = null, mixed $default = null): mixed
{
    static $config = null;

    if ($config === null) {
        tb_log('AiSchool', 'ais_config', 'START load');
        $config = require AIS_ROOT . '/config/app.php';
        tb_log('AiSchool', 'ais_config', 'END load', ['keys' => array_keys($config)]);
    }

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

/**
 * Module-relative asset URL with a cache-busting version.
 */
function ais_asset(string $path): string
{
    $path = ltrim($path, '/');
    $file = AIS_ROOT . '/' . $path;
    return is_file($file) ? $path . '?v=' . filemtime($file) : $path;
}

/**
 * Active period key from ?period=, then the cookie, then the default.
 */
function ais_period(): string
{
    static $period = null;
    if ($period !== null) {
        return $period;
    }

    $periods = ais_config('periods', []);
    $candidate = (string) ($_GET['period'] ?? $_COOKIE['ais_period'] ?? '');
    $period = isset($periods[$candidate]) ? $candidate : (string) ais_config('default_period', '6m');

    tb_log('AiSchool', 'ais_period', 'resolved', ['period' => $period]);
    return $period;
}

/**
 * Link to a dashboard page. The period lives in a cookie, so links stay short.
 */
function ais_url(string $page, array $params = []): string
{
    $query = http_build_query(['page' => $page] + $params);
    return './?' . $query;
}

/**
 * Lucide icon placeholder (rendered by lucide.createIcons()).
 */
function ais_icon(string $name, string $class = ''): string
{
    return '<i data-lucide="' . e($name) . '"' . ($class !== '' ? ' class="' . e($class) . '"' : '') . ' aria-hidden="true"></i>';
}

function ais_num(float|int|null $value, int $decimals = 0): string
{
    return $value === null ? '–' : number_format((float) $value, $decimals);
}

/**
 * Short Thai month label, e.g. "2026-05" => "พ.ค. 69".
 */
function ais_month(string $month, bool $withYear = true): string
{
    static $names = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    [$y, $m] = array_map('intval', explode('-', $month));
    return $names[$m - 1] . ($withYear ? ' ' . (($y + 543) % 100) : '');
}

/**
 * Thai date for display, e.g. "30 ต.ค. 2569".
 */
function ais_thai_date(string $date): string
{
    $ts = strtotime($date);
    if ($ts === false) {
        return $date;
    }
    return (int) date('j', $ts) . ' ' . ais_month(date('Y-m', $ts), false) . ' ' . ((int) date('Y', $ts) + 543);
}

/**
 * Change between two values for KPI badges.
 *
 * @return array{diff: float, text: string, dir: string, tone: string}
 */
function ais_delta(?float $current, ?float $previous, int $decimals = 1, string $unit = '', bool $higherIsBetter = true): array
{
    if ($current === null || $previous === null) {
        return ['diff' => 0.0, 'text' => '–', 'dir' => 'flat', 'tone' => 'neutral'];
    }

    $diff = round($current - $previous, $decimals);
    $dir = $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'flat');
    $good = $dir === 'flat' ? null : (($dir === 'up') === $higherIsBetter);

    return [
        'diff' => $diff,
        'text' => ($diff > 0 ? '+' : '') . number_format($diff, $decimals) . $unit,
        'dir'  => $dir,
        'tone' => $good === null ? 'neutral' : ($good ? 'good' : 'bad'),
    ];
}

/**
 * JSON for embedding inside <script type="application/json">.
 */
function ais_json(mixed $data): string
{
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PRESERVE_ZERO_FRACTION) ?: '{}';
}

/**
 * Render a component from components/ with local variables.
 */
function ais_component(string $name, array $vars = []): void
{
    $file = AIS_ROOT . '/components/' . basename($name) . '.php';
    if (!is_file($file)) {
        tb_log('AiSchool', 'ais_component', 'ERROR component not found', ['name' => $name], true);
        return;
    }

    (static function (string $__file, array $__vars): void {
        extract($__vars, EXTR_SKIP);
        require $__file;
    })($file, $vars);
}

/**
 * Embed a chart spec for assets/js/charts.js and return the canvas markup.
 */
function ais_chart(string $id, array $spec, int $height = 280, string $label = ''): string
{
    return '<div class="chart-box" style="--chart-h:' . $height . 'px">'
        . '<canvas id="' . e($id) . '" data-chart role="img" aria-label="' . e($label) . '"></canvas>'
        . '<script type="application/json" data-chart-spec="' . e($id) . '">' . ais_json($spec) . '</script>'
        . '</div>';
}

/** Human labels and tones for risk levels and priorities. */
function ais_risk_label(string $risk): string
{
    return ['high' => 'ต้องดูแลใกล้ชิด', 'medium' => 'ควรติดตาม', 'low' => 'ปกติ'][$risk] ?? $risk;
}

function ais_priority_label(string $priority): string
{
    return ['high' => 'สำคัญมาก', 'medium' => 'ควรดำเนินการ', 'low' => 'เฝ้าติดตาม'][$priority] ?? $priority;
}

function ais_band(float $score): array
{
    return match (true) {
        $score >= 84 => ['label' => 'โดดเด่น', 'tone' => 'good'],
        $score >= 80 => ['label' => 'ดีมาก', 'tone' => 'primary'],
        $score >= 77 => ['label' => 'ดี', 'tone' => 'neutral'],
        default      => ['label' => 'ควรได้รับการสนับสนุน', 'tone' => 'warn'],
    };
}
