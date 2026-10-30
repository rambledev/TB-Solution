<?php

declare(strict_types=1);

/**
 * AI insight card. Details open in the shared modal (pages.js) from the JSON block.
 *
 * @var array $insight
 * @var bool  $compact
 */
$compact ??= false;
?>
<article class="insight priority-<?= e($insight['priority']) ?>" data-insight-card data-priority="<?= e($insight['priority']) ?>" data-domain="<?= e($insight['domain']) ?>">
    <header class="insight__head">
        <span class="insight__icon"><?= ais_icon($insight['icon']) ?></span>
        <span class="badge badge--<?= e($insight['priority']) ?>"><?= e(ais_priority_label($insight['priority'])) ?></span>
    </header>
    <h3 class="insight__title"><?= e($insight['title']) ?></h3>
    <div class="insight__metric"><strong><?= e($insight['metric']) ?></strong><span><?= e($insight['metric_label']) ?></span></div>
<?php if (!$compact): ?>
    <p class="insight__summary"><?= e($insight['summary']) ?></p>
    <div class="insight__cols">
        <div>
            <p class="mini-label"><?= ais_icon('search', 'icon-xs') ?> สาเหตุที่เป็นไปได้</p>
            <ul class="dot-list"><?php foreach (array_slice($insight['causes'], 0, 2) as $c): ?><li><?= e($c) ?></li><?php endforeach; ?></ul>
        </div>
        <div>
            <p class="mini-label"><?= ais_icon('lightbulb', 'icon-xs') ?> ข้อเสนอแนะ</p>
            <ul class="dot-list"><?php foreach (array_slice($insight['actions'], 0, 2) as $c): ?><li><?= e($c) ?></li><?php endforeach; ?></ul>
        </div>
    </div>
<?php endif; ?>
    <footer class="insight__foot">
        <button type="button" class="btn btn--soft btn--sm" data-insight-open="<?= e($insight['id']) ?>"><?= ais_icon('eye') ?>ดูรายละเอียด</button>
        <a class="link-arrow" href="<?= e($insight['link']) ?>">ไปยังข้อมูล<?= ais_icon('arrow-right', 'icon-xs') ?></a>
    </footer>
</article>
