<?php

declare(strict_types=1);

$why = tb_config('content.why', []);
$items = $why['items'] ?? [];
?>
<section class="section why" id="about" aria-labelledby="why-title">
    <div class="container">
        <header class="section-head" data-reveal>
            <p class="eyebrow"><?= e($why['eyebrow'] ?? '') ?></p>
            <h2 class="section-title" id="why-title"><?= e($why['title'] ?? '') ?></h2>
            <p class="section-desc"><?= e($why['description'] ?? '') ?></p>
        </header>

        <div class="bento">
<?php if (isset($items['analysis'])): $item = $items['analysis']; ?>
            <article class="bento__card bento__card--analysis" data-reveal>
                <span class="icon-bubble"><?= tb_icon($item['icon'], 'icon') ?></span>
                <h3 class="bento__title"><?= e($item['title']) ?></h3>
                <p class="bento__desc"><?= e($item['description']) ?></p>

                <div class="flow" aria-hidden="true">
<?php foreach ($item['flow'] ?? [] as $step => $label): ?>
<?php if ($step > 0): ?>
                    <span class="flow__arrow"><?= tb_icon('arrow-right', 'icon icon--sm') ?></span>
<?php endif; ?>
                    <span class="flow__step flow__step--<?= e($step + 1) ?>"><?= e($label) ?></span>
<?php endforeach; ?>
                </div>

                <div class="checklist" aria-hidden="true">
                    <div class="checklist__row"><b><?= tb_icon('check', 'icon icon--xs') ?></b><i></i><em>ขั้นตอนซ้ำซ้อน</em></div>
                    <div class="checklist__row"><b><?= tb_icon('check', 'icon icon--xs') ?></b><i></i><em>ข้อมูลกระจัดกระจาย</em></div>
                    <div class="checklist__row"><b><?= tb_icon('check', 'icon icon--xs') ?></b><i></i><em>รายงานใช้เวลานาน</em></div>
                </div>
            </article>
<?php endif; ?>

<?php if (isset($items['ai'])): $item = $items['ai']; ?>
            <article class="bento__card bento__card--ai" data-reveal data-reveal-delay="1">
                <div class="bento__ai-glow" aria-hidden="true"></div>
                <span class="icon-bubble icon-bubble--white"><?= tb_icon($item['icon'], 'icon') ?></span>
                <h3 class="bento__title"><?= e($item['title']) ?></h3>
                <p class="bento__desc"><?= e($item['description']) ?></p>
                <ul class="bento__chips">
<?php foreach ($item['chips'] ?? [] as $chip): ?>
                    <li><?= tb_icon('zap', 'icon icon--xs') ?><?= e($chip) ?></li>
<?php endforeach; ?>
                </ul>
            </article>
<?php endif; ?>

<?php if (isset($items['fit'])): $item = $items['fit']; ?>
            <article class="bento__card bento__card--fit" data-reveal data-reveal-delay="2">
                <span class="icon-bubble"><?= tb_icon($item['icon'], 'icon') ?></span>
                <h3 class="bento__title"><?= e($item['title']) ?></h3>
                <p class="bento__desc"><?= e($item['description']) ?></p>
            </article>
<?php endif; ?>

<?php if (isset($items['devices'])): $item = $items['devices']; ?>
            <article class="bento__card bento__card--devices" data-reveal>
                <div>
                    <span class="icon-bubble"><?= tb_icon($item['icon'], 'icon') ?></span>
                    <h3 class="bento__title"><?= e($item['title']) ?></h3>
                    <p class="bento__desc"><?= e($item['description']) ?></p>
                </div>
                <div class="devices" aria-hidden="true">
                    <div class="devices__laptop"><span></span><span></span><span></span></div>
                    <div class="devices__tablet"><span></span><span></span></div>
                    <div class="devices__phone"><span></span><span></span></div>
                </div>
            </article>
<?php endif; ?>

<?php if (isset($items['scale'])): $item = $items['scale']; ?>
            <article class="bento__card bento__card--scale" data-reveal data-reveal-delay="1">
                <div>
                    <span class="icon-bubble"><?= tb_icon($item['icon'], 'icon') ?></span>
                    <h3 class="bento__title"><?= e($item['title']) ?></h3>
                    <p class="bento__desc"><?= e($item['description']) ?></p>
                </div>
                <div class="growth" aria-hidden="true">
                    <i></i><i></i><i></i><i></i><i></i>
                    <span class="growth__badge"><?= tb_icon('trending-up', 'icon icon--xs') ?>Scalable</span>
                </div>
            </article>
<?php endif; ?>
        </div>
    </div>
</section>
