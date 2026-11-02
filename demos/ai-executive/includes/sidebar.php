<?php

declare(strict_types=1);
?>
<aside class="sidebar" id="sidebar" aria-label="เมนูหลัก">
    <div class="sidebar__brand">
        <a class="brand" href="#overview" aria-label="AI Executive Dashboard หน้าแรก">
            <?= exe_logo('side') ?>
            <span class="brand__text"><strong>AI Executive</strong><small>Dashboard · TB-Solution</small></span>
        </a>
        <button type="button" class="icon-btn sidebar__close" data-action="sidebar-close" aria-label="ปิดเมนู"><?= exe_icon('x') ?></button>
    </div>

    <div class="sidebar__school">
        <span class="sidebar__school-icon"><?= exe_icon('school') ?></span>
        <span class="sidebar__school-text"><strong data-bind="school"><?= exe_e(exe_config('school.name')) ?></strong><small>ปีการศึกษา <?= exe_e(exe_config('school.academic_year')) ?> · <span data-bind="scenario">Default</span></small></span>
    </div>

    <nav class="sidebar__nav">
        <ul>
<?php foreach (exe_config('menu') as $item): ?>
            <li>
                <a class="nav-link" href="#<?= exe_e($item['id']) ?>" data-nav="<?= exe_e($item['id']) ?>" data-tip="<?= exe_e($item['label']) ?>">
                    <?= exe_icon($item['icon'], 'nav-link__icon') ?>
                    <span class="nav-link__text"><?= exe_e($item['label']) ?><small><?= exe_e($item['th']) ?></small></span>
<?php if (!empty($item['ai'])): ?>
                    <span class="nav-link__ai">AI</span>
<?php endif; ?>
                </a>
            </li>
<?php endforeach; ?>
        </ul>
    </nav>

    <div class="sidebar__foot">
        <div class="sim-pill"><?= exe_icon('sparkles') ?><span>AI Simulation · Demo Analysis</span></div>
        <a class="nav-link" href="../../" data-tip="กลับหน้า TB-Solution"><?= exe_icon('arrow-left', 'nav-link__icon') ?><span class="nav-link__text">กลับหน้า TB-Solution</span></a>
        <button type="button" class="nav-link sidebar__collapse" data-action="sidebar-collapse" data-tip="ย่อ/ขยายเมนู" aria-label="ย่อหรือขยายเมนู"><?= exe_icon('panel-left-close', 'nav-link__icon') ?><span class="nav-link__text">ย่อเมนู</span></button>
    </div>
</aside>
<div class="backdrop" data-action="sidebar-close" hidden></div>
