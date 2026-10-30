<?php

declare(strict_types=1);

$brand = tb_config('brand', []);
$navItems = tb_config('nav', []);
$channels = tb_contact_channels();
$services = array_column(tb_config('content.process.steps', []), 'title');
?>
<footer class="footer">
    <div class="container footer__grid">
        <div class="footer__brand">
            <a class="brand brand--light" href="#home" aria-label="<?= e($brand['name'] ?? '') ?> — กลับไปด้านบน">
                <?= tb_logo_mark('footer') ?>
                <span class="brand__name">TB<span>-Solution</span></span>
            </a>
            <p class="footer__tagline"><?= e($brand['tagline'] ?? '') ?></p>
            <p class="footer__summary"><?= e($brand['summary'] ?? '') ?></p>
        </div>

        <nav class="footer__col" aria-label="เมนูเว็บไซต์">
            <h2 class="footer__title">เมนูเว็บไซต์</h2>
            <ul class="footer__list">
<?php foreach ($navItems as $item): ?>
                <li><a href="<?= e(tb_safe_url($item['href'])) ?>"><?= e($item['label']) ?></a></li>
<?php endforeach; ?>
            </ul>
        </nav>

        <div class="footer__col">
            <h2 class="footer__title">บริการของเรา</h2>
            <ul class="footer__list">
<?php foreach ($services as $service): ?>
                <li><a href="#services"><?= e($service) ?></a></li>
<?php endforeach; ?>
            </ul>
        </div>

        <div class="footer__col">
            <h2 class="footer__title">ติดต่อเรา</h2>
<?php if ($channels === []): ?>
            <p class="footer__muted">กำลังอัปเดตช่องทางการติดต่อ<br>สามารถฝากข้อความผ่านปุ่ม “ติดต่อเรา” ได้เลย</p>
<?php else: ?>
            <ul class="footer__contact">
<?php foreach ($channels as $channel): ?>
                <li>
                    <?= tb_icon($channel['icon'], 'icon icon--sm') ?>
<?php if ($channel['href'] !== ''): ?>
                    <a href="<?= e($channel['href']) ?>"><?= e($channel['value']) ?></a>
<?php else: ?>
                    <span><?= e($channel['value']) ?></span>
<?php endif; ?>
                </li>
<?php endforeach; ?>
            </ul>
<?php endif; ?>
        </div>
    </div>

    <div class="container footer__bottom">
        <p>&copy; <?= e(date('Y')) ?> <?= e($brand['name'] ?? '') ?>. All rights reserved.</p>
        <p class="footer__concept"><?= tb_icon('sparkles', 'icon icon--sm') ?><?= e($brand['concept'] ?? '') ?></p>
    </div>
</footer>

<div class="toast" id="toast" role="status" aria-live="polite" hidden></div>

<script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
</body>
</html>
