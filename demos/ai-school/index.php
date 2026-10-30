<?php

declare(strict_types=1);

/**
 * AI School Intelligence — demo entry point and page router.
 * Open without login: /demos/ai-school/  (?page=<page>&period=3m|6m|12m)
 */
require __DIR__ . '/bootstrap.php';

tb_log('AiSchool', 'index', 'START', ['uri' => $_SERVER['REQUEST_URI'] ?? '']);

$navItems = [];
foreach (ais_config('nav', []) as $group) {
    foreach ($group['items'] as $item) {
        $navItems[$item['page']] = $item;
    }
}
$navItems['role'] = ['page' => 'role', 'label' => 'Role Preview', 'th' => 'มุมมองตามบทบาท', 'icon' => 'user-round'];

$page = (string) ($_GET['page'] ?? 'overview');
if (!isset($navItems[$page]) || !is_file(__DIR__ . '/pages/' . $page . '.php')) {
    tb_log('AiSchool', 'index', 'unknown page, using overview', ['page' => $page]);
    $page = 'overview';
}

if (isset($_GET['period'])) {
    setcookie('ais_period', ais_period(), ['expires' => time() + 60 * 60 * 24 * 30, 'path' => '/', 'samesite' => 'Lax']);
}

$roles = ais_config('roles', []);
$role = $page === 'role' && isset($roles[$_GET['as'] ?? '']) ? (string) $_GET['as'] : 'executive';
$current = $navItems[$page];
$analytics = SchoolAnalytics::instance();

// Render the page first so a failing page shows a polite message inside the layout.
ob_start();
try {
    (static function (string $__file, SchoolAnalytics $a, string $role): void {
        require $__file;
    })(__DIR__ . '/pages/' . $page . '.php', $analytics, $role);
    $content = (string) ob_get_clean();
} catch (Throwable $error) {
    ob_end_clean();
    tb_log('AiSchool', 'index', 'ERROR page render failed', ['page' => $page, 'error' => $error->getMessage(), 'line' => $error->getFile() . ':' . $error->getLine()], true);
    $content = '<div class="empty-state card">' . ais_icon('circle-alert') . '<h2>ขออภัย ไม่สามารถแสดงหน้านี้ได้ในขณะนี้</h2><p>กรุณาลองใหม่อีกครั้ง หรือกลับไปที่หน้าภาพรวม</p><a class="btn btn--primary" href="' . e(ais_url('overview')) . '">กลับหน้าภาพรวม</a></div>';
}

ais_component('head', ['page' => $page, 'title' => $current['label'] . ' · ' . ais_config('product.name')]);
?>
<div class="app" id="app">
<?php ais_component('sidebar', ['page' => $page, 'role' => $role]); ?>
    <div class="app__main">
<?php ais_component('topbar', ['current' => $current, 'role' => $role, 'analytics' => $analytics]); ?>
        <main class="page" id="main" data-page="<?= e($page) ?>">
<?= $content ?>
        </main>
        <footer class="app-footer">
            <span><?= ais_icon('flask-conical') ?> ข้อมูลทั้งหมดในระบบนี้เป็นข้อมูลจำลองเพื่อการสาธิต ไม่ใช่ข้อมูลจริงของโรงเรียนหรือบุคคลใด</span>
            <span><?= e(ais_config('product.full_name')) ?> · <?= e(ais_config('product.version')) ?> · by TB-Solution</span>
        </footer>
    </div>
</div>
<?php
ais_component('foot', ['page' => $page]);
tb_log('AiSchool', 'index', 'END', ['page' => $page, 'role' => $role]);
