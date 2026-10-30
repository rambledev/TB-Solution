<?php

declare(strict_types=1);

/**
 * Executive KPI card.
 *
 * @var array $kpi  item from SchoolAnalytics::kpiCards()
 */
$d = $kpi['delta'];
$arrow = ['up' => 'trending-up', 'down' => 'trending-down', 'flat' => 'minus'][$d['dir']];
?>
<a class="kpi tone-<?= e($kpi['tone']) ?>" href="<?= e($kpi['link']) ?>">
    <div class="kpi__top">
        <span class="kpi__icon"><?= ais_icon($kpi['icon']) ?></span>
        <span class="trend trend--<?= e($d['tone']) ?>" title="<?= e($kpi['compare']) ?>"><?= ais_icon($arrow) ?><?= e($d['text']) ?></span>
    </div>
    <p class="kpi__label"><?= e($kpi['label']) ?></p>
    <p class="kpi__value"><?= e($kpi['value']) ?><small><?= e($kpi['unit']) ?></small></p>
    <div class="kpi__foot">
        <span class="kpi__meta"><?= e($kpi['meta'] ?? $kpi['compare']) ?></span>
<?php if (!empty($kpi['spark'])): ?>
        <canvas class="kpi__spark" width="96" height="32" data-spark="<?= e(implode(',', $kpi['spark'])) ?>" aria-hidden="true"></canvas>
<?php endif; ?>
    </div>
</a>
