<?php

declare(strict_types=1);

/**
 * Shared <head>.
 *
 * @var string $pageTitle
 * @var array  $clientConfig
 */
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= h($pageTitle) ?></title>
    <meta name="description" content="EduCare by TB-Solution — <?= h(educare_config('brand.summary')) ?> (Demo ทดลองใช้งานได้ทันทีโดยไม่ต้อง Login)">
    <meta name="robots" content="noindex">
    <meta name="theme-color" content="#2563EB">
    <link rel="icon" type="image/svg+xml" href="<?= h(edu_asset('assets/images/logo.svg')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sarabun:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= h(edu_asset('assets/css/educare.css')) ?>">
    <script>window.EDU_CONFIG = <?= edu_json($clientConfig) ?>;</script>
</head>
