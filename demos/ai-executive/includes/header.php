<?php

declare(strict_types=1);

$clientConfig = [
    'debug'     => EXE_DEBUG,
    'api'       => ['mockData' => 'api/mock-data.php'],
    'scenarios' => array_map(static fn ($s) => ['label' => $s['label'], 'th' => $s['th'], 'icon' => $s['icon'], 'desc' => $s['desc']], exe_config('scenarios')),
    'terms'     => exe_config('terms'),
    'months'    => exe_config('months'),
    'menu'      => exe_config('menu'),
    'defaultScenario' => exe_config('default_scenario'),
    'defaultSeed'     => exe_config('default_seed'),
];
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>AI Executive Dashboard · TB-Solution</title>
    <meta name="description" content="AI Executive Dashboard สำหรับผู้บริหารโรงเรียน — ติดตาม วิเคราะห์ และประเมินภาพรวมการดำเนินงานด้วย AI (ข้อมูลจำลอง)">
    <meta name="robots" content="noindex">
    <meta name="theme-color" content="#2563EB">
    <link rel="icon" type="image/svg+xml" href="../../assets/images/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&family=Sarabun:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="<?= exe_e(exe_asset('assets/css/app.css')) ?>">
    <script>
        window.EXD_CONFIG = <?= exe_json($clientConfig) ?>;
        (function () {
            try {
                var theme = localStorage.getItem('exd.theme');
                if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.setAttribute('data-theme', 'dark');
                if (localStorage.getItem('exd.sidebar') === 'collapsed') document.documentElement.classList.add('sb-collapsed');
            } catch (e) { /* storage unavailable */ }
        })();
    </script>
</head>
<body>
<a class="skip-link" href="#content">ข้ามไปยังเนื้อหาหลัก</a>
<div class="loader" id="loader" role="status" aria-live="polite">
    <div class="loader__orb"><?= exe_logo('loader') ?></div>
    <p>กำลังเตรียม AI Executive Dashboard…</p>
    <div class="loader__bar"><span></span></div>
</div>
