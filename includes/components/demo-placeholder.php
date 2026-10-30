<?php

declare(strict_types=1);

/**
 * "Coming soon" card shown while demo systems are being prepared.
 *
 * @var array<string, string> $placeholder  content.demos.placeholder
 * @var int                   $index        position, used to vary the preview art
 */
$variant = ($index % 3) + 1;
?>
<article class="demo-card demo-card--placeholder" data-reveal aria-label="<?= e($placeholder['title']) ?>">
    <div class="demo-card__preview demo-card__preview--v<?= e($variant) ?>" aria-hidden="true">
        <div class="mock-ui">
            <div class="mock-ui__top"><span></span><span></span><span></span></div>
            <div class="mock-ui__body">
                <div class="mock-ui__side"><i></i><i></i><i></i><i></i></div>
                <div class="mock-ui__main">
                    <div class="mock-ui__tiles"><b></b><b></b><b></b></div>
                    <div class="mock-ui__chart"><em></em><em></em><em></em><em></em><em></em><em></em><em></em></div>
                </div>
            </div>
        </div>
        <span class="demo-card__category"><?= e($placeholder['category']) ?></span>
        <span class="demo-card__status"><?= tb_icon('clock', 'icon icon--xs') ?>Coming Soon</span>
    </div>

    <div class="demo-card__body">
        <h3 class="demo-card__title"><?= e($placeholder['title']) ?></h3>
        <p class="demo-card__desc"><?= e($placeholder['description']) ?></p>
        <div class="demo-card__actions">
            <span class="btn btn--ghost btn--sm is-disabled" aria-disabled="true"><?= tb_icon('eye', 'icon icon--sm') ?>ดูรายละเอียด</span>
            <span class="btn btn--primary btn--sm is-disabled" aria-disabled="true"><?= tb_icon('play', 'icon icon--sm') ?>ทดลองใช้งาน</span>
        </div>
    </div>
</article>
