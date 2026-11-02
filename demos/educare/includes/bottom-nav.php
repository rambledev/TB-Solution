<?php

declare(strict_types=1);

/**
 * Mobile bottom navigation (4 key menus + "more" opens the sidebar).
 *
 * @var array $roleConfig
 */
$byId = array_column($roleConfig['menu'], null, 'id');
?>
<nav class="bottom-nav d-lg-none" aria-label="เมนูลัด">
<?php foreach ($roleConfig['mobile'] as $id): $item = $byId[$id]; ?>
    <a href="#<?= h($id) ?>" data-nav="<?= h($id) ?>"><i class="bi <?= h($item['icon']) ?>"></i><span><?= h(mb_strimwidth($item['label'], 0, 14, '…')) ?></span></a>
<?php endforeach; ?>
    <button type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar"><i class="bi bi-grid"></i><span>เมนูทั้งหมด</span></button>
</nav>
