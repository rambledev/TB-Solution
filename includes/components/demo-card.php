<?php

declare(strict_types=1);

/**
 * Demo system card. Featured demos render as a wide card spanning two columns.
 *
 * @var array<string, mixed> $demo  normalised entry from tb_demos()
 */
?>
<article class="demo-card<?= $demo['featured'] ? ' demo-card--featured' : '' ?>" data-category="<?= e($demo['category']) ?>" id="demo-<?= e($demo['slug']) ?>" data-reveal>
    <div class="demo-card__preview">
<?php if ($demo['image'] !== ''): ?>
        <img src="<?= e($demo['image']) ?>" alt="ภาพตัวอย่างหน้าจอ <?= e($demo['name']) ?>" width="640" height="400" loading="lazy" decoding="async">
<?php else: ?>
        <div class="demo-card__art" aria-hidden="true"><?= tb_icon('layout-grid', 'icon') ?></div>
<?php endif; ?>
        <span class="demo-card__category"><?= e($demo['category']) ?></span>
<?php if (!$demo['ready']): ?>
        <span class="demo-card__status"><?= tb_icon('clock', 'icon icon--xs') ?>เร็ว ๆ นี้</span>
<?php elseif ($demo['badge'] !== ''): ?>
        <span class="demo-card__status demo-card__status--badge"><?= tb_icon('sparkles', 'icon icon--xs') ?><?= e($demo['badge']) ?></span>
<?php endif; ?>
    </div>

    <div class="demo-card__body">
<?php if ($demo['featured']): ?>
        <p class="demo-card__eyebrow"><?= tb_icon('zap', 'icon icon--xs') ?>Featured Demo · ทดลองใช้ได้ทันที ไม่ต้อง Login</p>
<?php endif; ?>
        <h3 class="demo-card__title"><?= e($demo['name']) ?></h3>
<?php if ($demo['name_en'] !== ''): ?>
        <p class="demo-card__subtitle"><?= e($demo['name_en']) ?></p>
<?php endif; ?>
        <p class="demo-card__desc"><?= e($demo['description']) ?></p>
<?php if ($demo['tags'] !== []): ?>
        <ul class="demo-card__tags">
<?php foreach ($demo['tags'] as $tag): ?>
            <li><?= e($tag) ?></li>
<?php endforeach; ?>
        </ul>
<?php endif; ?>

        <div class="demo-card__actions">
            <a class="btn btn--ghost btn--sm" href="<?= e($demo['detail_url'] ?: '#demos') ?>"<?= $demo['detail_url'] === '' ? ' data-pending' : '' ?>>
                <?= tb_icon('eye', 'icon icon--sm') ?>ดูรายละเอียด
            </a>
            <a class="btn btn--primary btn--sm" href="<?= e($demo['ready'] && $demo['demo_url'] !== '' ? $demo['demo_url'] : '#demos') ?>"<?= !$demo['ready'] || $demo['demo_url'] === '' ? ' data-pending' : '' ?>>
                <?= tb_icon('play', 'icon icon--sm') ?>ทดลองใช้งาน
            </a>
        </div>
    </div>
</article>
