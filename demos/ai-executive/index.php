<?php

declare(strict_types=1);

/**
 * AI Executive Dashboard — single-page entry.
 * Views are rendered client-side from one mock dataset (LocalStorage, falling back
 * to the embedded default dataset), so every module always shows the same data.
 */
require __DIR__ . '/includes/helpers.php';
require EXE_ROOT . '/data/generator.php';

exe_log('Page', 'index', 'START', ['uri' => $_SERVER['REQUEST_URI'] ?? '']);

try {
    $defaultDataset = exe_generate_dataset(exe_config('default_scenario'), (int) exe_config('default_seed'));
} catch (Throwable $error) {
    exe_log('Page', 'index', 'ERROR default dataset failed', ['error' => $error->getMessage()], true);
    $defaultDataset = null;
}

$views = array_column(exe_config('menu'), 'id');

require EXE_ROOT . '/includes/header.php';
?>
<div class="app" id="app">
<?php require EXE_ROOT . '/includes/sidebar.php'; ?>
    <div class="main">
<?php require EXE_ROOT . '/includes/topbar.php'; ?>
<?php require EXE_ROOT . '/includes/filters.php'; ?>
        <main class="content" id="content" tabindex="-1">
<?php foreach ($views as $view): ?>
            <section class="view" id="view-<?= exe_e($view) ?>" data-view="<?= exe_e($view) ?>" hidden>
<?php require EXE_ROOT . '/views/' . $view . '.php'; ?>
            </section>
<?php endforeach; ?>
            <div class="state state--error" id="app-error" hidden>
                <?= exe_icon('cloud-off') ?>
                <h2>ไม่สามารถแสดงข้อมูลได้ในขณะนี้</h2>
                <p>กรุณาลองใหม่อีกครั้ง หรือรีเซ็ตกลับไปใช้ชุดข้อมูลเริ่มต้น</p>
                <button type="button" class="btn btn--primary" data-action="reset">รีเซ็ตข้อมูล</button>
            </div>
        </main>
        <footer class="app-footer">
            <span><?= exe_icon('flask-conical') ?> ข้อมูลทั้งหมดเป็นข้อมูลจำลอง (Mock Data) · การวิเคราะห์เป็น AI Simulation / Demo Analysis</span>
            <span><?= exe_e(exe_config('app.name')) ?> · <?= exe_e(exe_config('app.version')) ?> · by TB-Solution</span>
        </footer>
    </div>
</div>
<script type="application/json" id="default-dataset"><?= exe_json($defaultDataset) ?></script>
<?php
require EXE_ROOT . '/includes/footer.php';
exe_log('Page', 'index', 'END');
