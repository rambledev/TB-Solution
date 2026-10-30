<?php

declare(strict_types=1);

$navItems = tb_config('nav', []);
$demoEntry = tb_config('demo_entry', []);
$demoEntryUrl = tb_safe_url($demoEntry['url'] ?? '') ?: '#demos';
?>
<header class="navbar" id="navbar">
    <div class="container navbar__inner">
        <a class="brand" href="#home" aria-label="<?= e(tb_config('brand.name')) ?> — หน้าแรก">
            <?= tb_logo_mark('nav') ?>
            <span class="brand__name">TB<span>-Solution</span></span>
        </a>

        <nav class="navbar__menu" id="primary-menu" aria-label="เมนูหลัก">
            <ul class="navbar__links">
<?php foreach ($navItems as $item): ?>
                <li><a class="navbar__link" href="<?= e(tb_safe_url($item['href'])) ?>"><?= e($item['label']) ?></a></li>
<?php endforeach; ?>
            </ul>
            <a class="btn btn--primary btn--sm navbar__cta navbar__cta--mobile" href="<?= e($demoEntryUrl) ?>">
                <?= tb_icon('play', 'icon icon--sm') ?><?= e($demoEntry['label'] ?? '') ?>
            </a>
        </nav>

        <div class="navbar__actions">
            <a class="btn btn--primary btn--sm navbar__cta" href="<?= e($demoEntryUrl) ?>">
                <?= tb_icon('play', 'icon icon--sm') ?><?= e($demoEntry['label'] ?? '') ?>
            </a>
            <button class="navbar__toggle" type="button" aria-controls="primary-menu" aria-expanded="false" aria-label="เปิดเมนู">
                <?= tb_icon('menu', 'icon navbar__toggle-open') ?>
                <?= tb_icon('x', 'icon navbar__toggle-close') ?>
            </button>
        </div>
    </div>
</header>
