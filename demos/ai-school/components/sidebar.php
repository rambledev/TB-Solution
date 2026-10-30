<?php

declare(strict_types=1);

/**
 * Sidebar navigation (collapsible on desktop, drawer on mobile).
 *
 * @var string $page
 * @var string $role
 */
$school = ais_config('school');
?>
<aside class="sidebar" id="sidebar" aria-label="เมนูหลัก">
    <div class="sidebar__brand">
        <a class="brand" href="<?= e(ais_url('overview')) ?>" aria-label="AI School Intelligence หน้าแรก">
            <?= tb_logo_mark('ais-side', 'brand__mark') ?>
            <span class="brand__text">
                <strong>TB-Solution</strong>
                <small>AI School Intelligence</small>
            </span>
        </a>
        <button type="button" class="btn-icon sidebar__close" data-sidebar-close aria-label="ปิดเมนู"><?= ais_icon('x') ?></button>
    </div>

    <div class="sidebar__school">
        <span class="sidebar__school-icon"><?= ais_icon('school') ?></span>
        <span class="sidebar__school-text">
            <strong><?= e($school['name']) ?></strong>
            <small>ปีการศึกษา <?= e($school['academic_year']) ?> · ภาคเรียน <?= e($school['current_term']) ?></small>
        </span>
    </div>

    <nav class="sidebar__nav">
<?php foreach (ais_config('nav', []) as $group): ?>
        <p class="sidebar__group"><?= e($group['group']) ?></p>
        <ul>
<?php foreach ($group['items'] as $item):
    $active = $item['page'] === $page && $role === 'executive'; ?>
            <li>
                <a class="nav-link<?= $active ? ' is-active' : '' ?>" href="<?= e(ais_url($item['page'])) ?>"<?= $active ? ' aria-current="page"' : '' ?> data-tooltip="<?= e($item['label']) ?>">
                    <?= ais_icon($item['icon'], 'nav-link__icon') ?>
                    <span class="nav-link__text"><?= e($item['label']) ?><small><?= e($item['th']) ?></small></span>
<?php if (!empty($item['badge'])): ?>
                    <span class="nav-link__badge"><?= e($item['badge']) ?></span>
<?php endif; ?>
                </a>
            </li>
<?php endforeach; ?>
        </ul>
<?php endforeach; ?>
    </nav>

    <div class="sidebar__foot">
        <div class="demo-pill"><?= ais_icon('flask-conical') ?><span>DEMO DATA · ข้อมูลจำลอง</span></div>
        <button type="button" class="nav-link nav-link--btn" data-reset-demo data-tooltip="Reset Demo Data">
            <?= ais_icon('rotate-ccw', 'nav-link__icon') ?><span class="nav-link__text">Reset Demo Data</span>
        </button>
        <a class="nav-link" href="../../" data-tooltip="กลับหน้า TB-Solution">
            <?= ais_icon('arrow-left', 'nav-link__icon') ?><span class="nav-link__text">กลับหน้า TB-Solution</span>
        </a>
        <button type="button" class="nav-link nav-link--btn sidebar__collapse" data-sidebar-collapse aria-label="ย่อ/ขยายเมนู" data-tooltip="ขยายเมนู">
            <?= ais_icon('panel-left-close', 'nav-link__icon') ?><span class="nav-link__text">ย่อเมนู</span>
        </button>
    </div>
</aside>
<div class="sidebar-backdrop" data-sidebar-close hidden></div>
