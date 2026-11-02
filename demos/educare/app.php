<?php

declare(strict_types=1);

/**
 * EduCare Demo — role dashboard shell: app.php?role=teacher|parent|executive
 * No login: the role comes from the URL (validated against a whitelist).
 * Views are rendered by assets/js/<role>.js from the shared LocalStorage store.
 */
require __DIR__ . '/includes/bootstrap.php';
require EDU_ROOT . '/data/seed.php';

$role = edu_role($_GET['role'] ?? null);
educare_log('Page', 'app', 'START', ['role' => $_GET['role'] ?? null, 'valid' => $role !== null]);

if ($role === null) {
    header('Location: ./#roles', true, 302);
    exit;
}
$_SESSION['educare_role'] = $role;

$roleConfig = educare_config('roles.' . $role);
$today = date('Y-m-d');
try {
    $seed = educare_seed($today);
} catch (Throwable $error) {
    educare_log('Page', 'app', 'ERROR seed failed', ['error' => $error->getMessage(), 'role' => $role], true);
    $seed = null;
}

$pageTitle = $roleConfig['title'] . ' · EduCare Demo';
$clientConfig = [
    'debug' => EDU_DEBUG,
    'page'  => 'app',
    'role'  => $role,
    'today' => $today,
    'menu'  => $roleConfig['menu'],
    'roleLabel' => $roleConfig['label'],
];

require EDU_ROOT . '/includes/head.php';
?>
<body class="app role-<?= h($role) ?>">
<a class="visually-hidden-focusable skip" href="#view">ข้ามไปยังเนื้อหาหลัก</a>
<div class="app-shell">
<?php require EDU_ROOT . '/includes/sidebar.php'; ?>
    <div class="app-main">
<?php require EDU_ROOT . '/includes/topbar.php'; ?>
        <main class="app-content" id="view" tabindex="-1" aria-live="polite">
            <div class="state-box"><div class="spinner-border text-primary" role="status"></div><p>กำลังโหลดข้อมูล Demo…</p></div>
        </main>
<?php require EDU_ROOT . '/includes/brand-footer.php'; ?>
    </div>
</div>
<?php require EDU_ROOT . '/includes/bottom-nav.php'; ?>
<script type="application/json" id="edu-seed"><?= edu_json($seed) ?></script>
<?php require EDU_ROOT . '/includes/scripts.php'; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="<?= h(edu_asset('assets/js/' . $role . '.js')) ?>"></script>
</body>
</html>
<?php
educare_log('Page', 'app', 'END', ['role' => $role]);
