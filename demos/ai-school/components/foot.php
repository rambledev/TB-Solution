<?php

declare(strict_types=1);

/**
 * Shared modal/toast roots and scripts.
 *
 * @var string $page
 */
$pageScripts = [
    'ai-insights'        => ['assets/js/chat.js'],
    'data-import'        => ['assets/js/import.js'],
];
?>
<div class="modal" id="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title" hidden>
    <div class="modal__backdrop" data-modal-close></div>
    <div class="modal__panel" role="document">
        <header class="modal__head">
            <div>
                <p class="modal__eyebrow" id="modal-eyebrow"></p>
                <h2 class="modal__title" id="modal-title"></h2>
            </div>
            <button type="button" class="btn-icon" data-modal-close aria-label="ปิดหน้าต่าง"><?= ais_icon('x') ?></button>
        </header>
        <div class="modal__body" id="modal-body"></div>
        <footer class="modal__foot" id="modal-foot" hidden></footer>
    </div>
</div>
<div class="toast-stack" id="toast-stack" aria-live="polite"></div>
<script type="application/json" id="ais-insights"><?= ais_json(SchoolAnalytics::instance()->insights()) ?></script>

<script src="https://cdn.jsdelivr.net/npm/lucide@0.460.0/dist/umd/lucide.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="<?= e(ais_asset('assets/js/core.js')) ?>"></script>
<script src="<?= e(ais_asset('assets/js/charts.js')) ?>"></script>
<script src="<?= e(ais_asset('assets/js/pages.js')) ?>"></script>
<?php foreach ($pageScripts[$page] ?? [] as $script): ?>
<script src="<?= e(ais_asset($script)) ?>"></script>
<?php endforeach; ?>
</body>
</html>
