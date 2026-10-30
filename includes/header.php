<?php

declare(strict_types=1);

$seo = tb_config('seo', []);
$brand = tb_config('brand', []);
$baseUrl = rtrim((string) ($seo['base_url'] ?? ''), '/');
$ogImage = !empty($seo['og_image']) && is_file(TB_ROOT . '/' . $seo['og_image']) ? $seo['og_image'] : '';
$ogImageUrl = $ogImage !== '' ? ($baseUrl !== '' ? $baseUrl . '/' . $ogImage : $ogImage) : '';

$jsonLd = [
    '@context'    => 'https://schema.org',
    '@type'       => 'Organization',
    'name'        => $brand['name'] ?? '',
    'slogan'      => $brand['tagline'] ?? '',
    'description' => $seo['description'] ?? '',
];
if ($baseUrl !== '') {
    $jsonLd['url'] = $baseUrl . '/';
    $jsonLd['logo'] = $baseUrl . '/assets/images/logo.svg';
}

$clientConfig = ['debug' => TB_DEBUG];
?>
<!doctype html>
<html lang="th" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($seo['title'] ?? '') ?></title>
    <meta name="description" content="<?= e($seo['description'] ?? '') ?>">
    <meta name="keywords" content="<?= e($seo['keywords'] ?? '') ?>">
    <meta name="author" content="<?= e($brand['name'] ?? '') ?>">
    <meta name="theme-color" content="<?= e($seo['theme_color'] ?? '#2878E8') ?>">
    <meta name="robots" content="index, follow">
<?php if ($baseUrl !== ''): ?>
    <link rel="canonical" href="<?= e($baseUrl) ?>/">
<?php endif; ?>

    <!-- Open Graph / Twitter -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e($brand['name'] ?? '') ?>">
    <meta property="og:title" content="<?= e($seo['title'] ?? '') ?>">
    <meta property="og:description" content="<?= e($seo['description'] ?? '') ?>">
    <meta property="og:locale" content="<?= e($seo['locale'] ?? 'th_TH') ?>">
<?php if ($baseUrl !== ''): ?>
    <meta property="og:url" content="<?= e($baseUrl) ?>/">
<?php endif; ?>
<?php if ($ogImageUrl !== ''): ?>
    <meta property="og:image" content="<?= e($ogImageUrl) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:image" content="<?= e($ogImageUrl) ?>">
<?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($seo['title'] ?? '') ?>">
    <meta name="twitter:description" content="<?= e($seo['description'] ?? '') ?>">

    <link rel="icon" type="image/svg+xml" href="<?= e(asset('assets/images/favicon.svg')) ?>">
    <link rel="apple-touch-icon" href="<?= e(asset('assets/images/logo-icon.svg')) ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap">

    <link rel="stylesheet" href="<?= e(asset('assets/css/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/responsive.css')) ?>">

    <script>
        document.documentElement.classList.replace('no-js', 'js');
        window.TB_CONFIG = <?= json_encode($clientConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    </script>
    <script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
</head>
<body>
<a class="skip-link" href="#main">ข้ามไปยังเนื้อหาหลัก</a>
