<?php

declare(strict_types=1);
?>
<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toasts"></div>
<div class="modal fade" id="edu-modal" tabindex="-1" aria-labelledby="edu-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down" id="edu-modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="edu-modal-title"></h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body" id="edu-modal-body"></div>
            <div class="modal-footer" id="edu-modal-foot"></div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= h(edu_asset('assets/js/store.js')) ?>"></script>
<script src="<?= h(edu_asset('assets/js/notify.js')) ?>"></script>
<script src="<?= h(edu_asset('assets/js/ui.js')) ?>"></script>
<script src="<?= h(edu_asset('assets/js/parts.js')) ?>"></script>
