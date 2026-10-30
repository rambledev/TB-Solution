<?php

declare(strict_types=1);

$section = tb_config('content.demos', []);
$demos = tb_demos();
$usedSlots = array_sum(array_map(static fn ($d) => $d['featured'] ? 2 : 1, $demos));
$placeholderCount = max(0, (int) ($section['placeholder_count'] ?? 3) - $usedSlots);
$categories = array_values(array_unique(array_column($demos, 'category')));
$contactLink = tb_contact_link('contact');
?>
<section class="section demos" id="demos" aria-labelledby="demos-title">
    <div class="container">
        <header class="section-head" data-reveal>
            <p class="eyebrow"><?= e($section['eyebrow'] ?? '') ?></p>
            <h2 class="section-title" id="demos-title"><?= e($section['title'] ?? '') ?></h2>
            <p class="section-desc"><?= e($section['description'] ?? '') ?></p>
        </header>

<?php if (count($categories) > 1): ?>
        <div class="demo-filter" role="group" aria-label="กรองตามหมวดหมู่" data-reveal>
            <button type="button" class="chip is-active" data-filter="*" aria-pressed="true">ทั้งหมด</button>
<?php foreach ($categories as $category): ?>
            <button type="button" class="chip" data-filter="<?= e($category) ?>" aria-pressed="false"><?= e($category) ?></button>
<?php endforeach; ?>
        </div>
<?php endif; ?>

        <div class="demos__grid" id="demo-grid">
<?php
foreach ($demos as $demo) {
    tb_component('demo-card', ['demo' => $demo]);
}
for ($i = 0; $i < $placeholderCount; $i++) {
    tb_component('demo-placeholder', ['placeholder' => $section['placeholder'] ?? [], 'index' => $i]);
}
?>
        </div>

        <div class="demos__notice" data-reveal>
            <span class="icon-bubble icon-bubble--yellow"><?= tb_icon('sparkles', 'icon') ?></span>
            <div class="demos__notice-text">
                <h3><?= e($section['notice']['title'] ?? '') ?></h3>
                <p><?= e($section['notice']['description'] ?? '') ?></p>
            </div>
            <a class="btn btn--primary" href="<?= e($contactLink['href']) ?>"><?= e($section['notice']['cta'] ?? '') ?><?= tb_icon('arrow-right', 'icon icon--sm') ?></a>
        </div>
    </div>
</section>
