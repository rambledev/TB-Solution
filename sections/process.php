<?php

declare(strict_types=1);

$process = tb_config('content.process', []);
?>
<section class="section process" id="services" aria-labelledby="process-title">
    <div class="container">
        <header class="section-head" data-reveal>
            <p class="eyebrow"><?= e($process['eyebrow'] ?? '') ?></p>
            <h2 class="section-title" id="process-title"><?= e($process['title'] ?? '') ?></h2>
            <p class="section-desc"><?= e($process['description'] ?? '') ?></p>
        </header>

        <ol class="steps">
<?php foreach ($process['steps'] ?? [] as $i => $step): ?>
            <li class="step" data-reveal data-reveal-delay="<?= e($i) ?>">
                <span class="step__num"><?= e(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                <div class="step__card">
                    <span class="step__icon"><?= tb_icon($step['icon'], 'icon') ?></span>
                    <h3 class="step__title"><?= e($step['title']) ?></h3>
                    <p class="step__desc"><?= e($step['description']) ?></p>
                </div>
            </li>
<?php endforeach; ?>
        </ol>
    </div>
</section>
