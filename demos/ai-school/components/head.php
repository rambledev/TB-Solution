<?php

declare(strict_types=1);

/**
 * Document head.
 *
 * @var string $page
 * @var string $title
 */
$clientConfig = [
    'debug'  => TB_DEBUG,
    'page'   => $page,
    'period' => ais_period(),
    'api'    => ['chat' => 'api/chat.php'],
    'ai'     => AiAssistant::mode(),
];
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($title) ?></title>
    <meta name="description" content="<?= e(ais_config('product.full_name')) ?> — Demo ระบบบริหารจัดการและวิเคราะห์ข้อมูลโรงเรียนด้วย AI โดย TB-Solution (ข้อมูลจำลอง)">
    <meta name="robots" content="noindex">
    <meta name="theme-color" content="#2878E8">
    <link rel="icon" type="image/svg+xml" href="../../assets/images/favicon.svg">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="<?= e(ais_asset('assets/css/app.css')) ?>">

    <script>
        window.AIS_CONFIG = <?= ais_json($clientConfig) ?>;
        try {
            if (localStorage.getItem('ais.sidebar') === 'collapsed') document.documentElement.classList.add('sidebar-collapsed');
        } catch (e) { /* storage unavailable */ }
    </script>
</head>
<body>
<a class="skip-link" href="#main">ข้ามไปยังเนื้อหาหลัก</a>
