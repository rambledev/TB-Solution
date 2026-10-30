<?php

declare(strict_types=1);

$cta = tb_config('content.cta', []);
$contactLink = tb_contact_link('contact');
$consultLink = tb_contact_link('consult');
$channels = tb_contact_channels();
?>
<section class="section contact" id="contact" aria-labelledby="contact-title">
    <div class="container">
        <div class="cta" data-reveal>
            <div class="cta__decor" aria-hidden="true">
                <span class="cta__ring cta__ring--1"></span>
                <span class="cta__ring cta__ring--2"></span>
            </div>

            <div class="cta__content">
                <p class="eyebrow eyebrow--dark"><?= tb_icon('sparkles', 'icon icon--sm') ?><?= e($cta['eyebrow'] ?? '') ?></p>
                <h2 class="cta__title" id="contact-title"><?= e($cta['title'] ?? '') ?></h2>
                <p class="cta__desc"><?= e($cta['description'] ?? '') ?></p>

                <div class="cta__actions">
                    <a class="btn btn--yellow btn--lg" href="<?= e($contactLink['href']) ?>"<?= $contactLink['pending'] ? ' data-pending' : '' ?>>
                        <?= tb_icon('mail', 'icon icon--sm') ?><?= e($cta['primary'] ?? '') ?>
                    </a>
                    <a class="btn btn--glass btn--lg" href="<?= e($consultLink['href']) ?>"<?= $consultLink['pending'] ? ' data-pending' : '' ?>>
                        <?= tb_icon('message', 'icon icon--sm') ?><?= e($cta['secondary'] ?? '') ?>
                    </a>
                </div>

<?php if ($channels !== []): ?>
                <ul class="cta__channels">
<?php foreach ($channels as $channel): ?>
                    <li><?= tb_icon($channel['icon'], 'icon icon--sm') ?><?= e($channel['value']) ?></li>
<?php endforeach; ?>
                </ul>
<?php endif; ?>
            </div>

            <div class="cta__visual" aria-hidden="true">
                <img class="cta__orb" src="<?= e(asset('assets/images/illustrations/ai-core.svg')) ?>" alt="" width="240" height="240" loading="lazy" decoding="async">
                <ul class="cta__points">
<?php foreach ($cta['points'] ?? [] as $point): ?>
                    <li><b><?= tb_icon('check', 'icon icon--xs') ?></b><?= e($point) ?></li>
<?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</section>
