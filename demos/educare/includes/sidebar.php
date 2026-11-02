<?php

declare(strict_types=1);

/**
 * @var string $role
 * @var array  $roleConfig
 */
?>
<aside class="sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="sidebar" aria-labelledby="sidebar-title">
    <div class="sidebar__head">
        <a class="brand" href="./" aria-label="EduCare หน้าเลือกบทบาท">
            <?= edu_logo() ?>
            <span class="brand__text"><strong id="sidebar-title">EduCare</strong><small>by TB-Solution</small></span>
        </a>
        <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="ปิดเมนู"></button>
    </div>
    <div class="sidebar__role role-pill role-pill--<?= h($roleConfig['color']) ?>">
        <i class="bi <?= h($roleConfig['icon']) ?>"></i>
        <div><small>บทบาทปัจจุบัน</small><strong><?= h($roleConfig['title']) ?></strong></div>
    </div>
    <nav class="sidebar__nav" aria-label="เมนู<?= h($roleConfig['label']) ?>">
<?php foreach ($roleConfig['menu'] as $item): ?>
        <a class="side-link" href="#<?= h($item['id']) ?>" data-nav="<?= h($item['id']) ?>"><i class="bi <?= h($item['icon']) ?>"></i><span><?= h($item['label']) ?></span><span class="side-link__badge" data-badge="<?= h($item['id']) ?>"></span></a>
<?php endforeach; ?>
    </nav>
    <div class="sidebar__foot">
        <a class="btn btn-primary w-100 rounded-pill mb-2" href="./#roles"><i class="bi bi-arrow-left-right me-1"></i>เปลี่ยนบทบาท</a>
        <div class="d-flex gap-2">
            <a class="btn btn-light btn-sm flex-fill" href="../../"><i class="bi bi-house"></i> หน้าแรก</a>
            <button type="button" class="btn btn-light btn-sm flex-fill text-danger" data-action="reset-demo"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
        </div>
    </div>
</aside>
