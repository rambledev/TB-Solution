<?php

declare(strict_types=1);

/**
 * Page heading with optional actions (raw HTML built by the page).
 *
 * @var string $title
 * @var string $desc
 * @var string $eyebrow
 * @var string $actions
 */
?>
<div class="page-head">
    <div>
<?php if (!empty($eyebrow)): ?>
        <p class="eyebrow"><?= $eyebrow ?></p>
<?php endif; ?>
        <h2 class="page-head__title"><?= e($title) ?></h2>
<?php if (!empty($desc)): ?>
        <p class="page-head__desc"><?= e($desc) ?></p>
<?php endif; ?>
    </div>
<?php if (!empty($actions)): ?>
    <div class="page-head__actions"><?= $actions ?></div>
<?php endif; ?>
</div>
