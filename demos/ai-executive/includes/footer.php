<?php

declare(strict_types=1);
?>
<div class="modal" id="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title" hidden>
    <div class="modal__backdrop" data-action="modal-close"></div>
    <div class="modal__panel">
        <header class="modal__head">
            <div><p class="modal__eyebrow" id="modal-eyebrow"></p><h2 class="modal__title" id="modal-title"></h2></div>
            <button type="button" class="icon-btn" data-action="modal-close" aria-label="ปิดหน้าต่าง"><?= exe_icon('x') ?></button>
        </header>
        <div class="modal__body" id="modal-body"></div>
        <footer class="modal__foot" id="modal-foot" hidden></footer>
    </div>
</div>
<div class="toasts" id="toasts" aria-live="polite"></div>

<script src="https://cdn.jsdelivr.net/npm/lucide@0.460.0/dist/umd/lucide.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="<?= exe_e(exe_asset('assets/js/mock-data.js')) ?>"></script>
<script src="<?= exe_e(exe_asset('assets/js/ai-analytics.js')) ?>"></script>
<script src="<?= exe_e(exe_asset('assets/js/charts.js')) ?>"></script>
<script src="<?= exe_e(exe_asset('assets/js/dashboard.js')) ?>"></script>
<script src="<?= exe_e(exe_asset('assets/js/views.js')) ?>"></script>
<script src="<?= exe_e(exe_asset('assets/js/reports.js')) ?>"></script>
</body>
</html>
