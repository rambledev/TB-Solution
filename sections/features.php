<?php

declare(strict_types=1);

$features = tb_config('content.features', []);
?>
<section class="section features" id="features" aria-labelledby="features-title">
    <div class="container">
        <header class="section-head" data-reveal>
            <p class="eyebrow"><?= e($features['eyebrow'] ?? '') ?></p>
            <h2 class="section-title" id="features-title"><?= e($features['title'] ?? '') ?></h2>
            <p class="section-desc"><?= e($features['description'] ?? '') ?></p>
        </header>

        <div class="features__grid">
<?php foreach ($features['items'] ?? [] as $i => $item): ?>
            <article class="feature-card<?= !empty($item['accent']) ? ' feature-card--accent' : '' ?>" data-reveal data-reveal-delay="<?= e($i) ?>">
                <span class="icon-bubble<?= !empty($item['accent']) ? ' icon-bubble--yellow' : '' ?>"><?= tb_icon($item['icon'], 'icon') ?></span>
                <h3 class="feature-card__title"><?= e($item['title']) ?></h3>
                <p class="feature-card__subtitle"><?= e($item['subtitle']) ?></p>
                <p class="feature-card__desc"><?= e($item['description']) ?></p>
            </article>
<?php endforeach; ?>
        </div>
    </div>
</section>
