<?php

declare(strict_types=1);

/**
 * Top navigation: page title, school, period filter, notifications, profile & role selector.
 *
 * @var array           $current   nav item of the current page
 * @var string          $role
 * @var SchoolAnalytics $analytics
 */
$school = ais_config('school');
$roles = ais_config('roles', []);
$roleInfo = $roles[$role];
$periods = ais_config('periods', []);
$period = ais_period();
$notifications = $analytics->notifications();
?>
<header class="topbar" id="topbar">
    <button type="button" class="btn-icon topbar__menu" data-sidebar-open aria-label="เปิดเมนู" aria-controls="sidebar"><?= ais_icon('menu') ?></button>

    <div class="topbar__title">
        <p class="topbar__crumb"><?= e($school['name']) ?> <span>/</span> <?= e($current['th']) ?></p>
        <h1><?= e($current['label']) ?></h1>
    </div>

    <div class="topbar__actions">
        <div class="school-chip" title="<?= e($school['name']) ?>">
            <?= ais_icon('calendar-range') ?>
            <span>ปีการศึกษา <strong><?= e($school['academic_year']) ?></strong></span>
        </div>

        <div class="dropdown" data-dropdown>
            <button type="button" class="btn btn--soft btn--sm" data-dropdown-toggle aria-expanded="false" aria-haspopup="true">
                <?= ais_icon('calendar-days') ?><span class="hide-sm"><?= e($periods[$period]['label']) ?></span><span class="show-sm"><?= e(strtoupper($period)) ?></span><?= ais_icon('chevron-down', 'icon-xs') ?>
            </button>
            <div class="dropdown__menu dropdown__menu--sm" role="menu">
                <p class="dropdown__label">ช่วงเวลาข้อมูล</p>
<?php foreach ($periods as $key => $p): ?>
                <button type="button" role="menuitemradio" aria-checked="<?= $key === $period ? 'true' : 'false' ?>" class="dropdown__item<?= $key === $period ? ' is-active' : '' ?>" data-period="<?= e($key) ?>">
                    <?= ais_icon($key === $period ? 'circle-check' : 'circle') ?><?= e($p['label']) ?>
                </button>
<?php endforeach; ?>
                <p class="dropdown__hint">ข้อมูลล่าสุด: <?= e($analytics->periodRange()) ?></p>
            </div>
        </div>

        <span class="badge badge--demo hide-md" title="ทุกตัวเลขเป็นข้อมูลจำลอง"><?= ais_icon('flask-conical') ?>DEMO DATA</span>

        <div class="dropdown" data-dropdown>
            <button type="button" class="btn-icon btn-icon--ring" data-dropdown-toggle aria-expanded="false" aria-label="การแจ้งเตือน">
                <?= ais_icon('bell') ?><span class="dot-count" data-notif-count><?= count($notifications) ?></span>
            </button>
            <div class="dropdown__menu dropdown__menu--wide" role="menu">
                <div class="dropdown__head">
                    <strong>การแจ้งเตือน</strong>
                    <button type="button" class="link-btn" data-notif-readall>อ่านทั้งหมดแล้ว</button>
                </div>
                <ul class="notif-list">
<?php foreach ($notifications as $n): ?>
                    <li>
                        <a class="notif" href="<?= e($n['link']) ?>" data-notif-id="<?= e($n['id']) ?>">
                            <span class="notif__icon tone-<?= e($n['tone']) ?>"><?= ais_icon($n['icon']) ?></span>
                            <span class="notif__text"><strong><?= e($n['title']) ?></strong><small><?= e($n['time']) ?></small></span>
                        </a>
                    </li>
<?php endforeach; ?>
                </ul>
                <a class="dropdown__footer" href="<?= e(ais_url('ai-insights')) ?>">ดู AI Insights ทั้งหมด <?= ais_icon('arrow-right', 'icon-xs') ?></a>
            </div>
        </div>

        <div class="dropdown" data-dropdown>
            <button type="button" class="profile-btn" data-dropdown-toggle aria-expanded="false" aria-label="โปรไฟล์และเลือกบทบาท">
                <span class="avatar avatar--<?= e($role) ?>"><?= ais_icon($roleInfo['icon']) ?></span>
                <span class="profile-btn__text hide-md"><strong><?= e($roleInfo['user']) ?></strong><small><?= e($roleInfo['title']) ?></small></span>
                <?= ais_icon('chevron-down', 'icon-xs hide-md') ?>
            </button>
            <div class="dropdown__menu" role="menu">
                <div class="dropdown__profile">
                    <span class="avatar avatar--lg avatar--<?= e($role) ?>"><?= ais_icon($roleInfo['icon']) ?></span>
                    <div><strong><?= e($roleInfo['user']) ?></strong><small>เข้าชมแบบไม่ต้อง Login (Demo)</small></div>
                </div>
                <p class="dropdown__label">สาธิตมุมมองตามบทบาท (Role Selector)</p>
<?php foreach ($roles as $key => $r):
    $href = $key === 'executive' ? ais_url('overview') : ais_url('role', ['as' => $key]); ?>
                <a role="menuitemradio" aria-checked="<?= $key === $role ? 'true' : 'false' ?>" class="dropdown__item<?= $key === $role ? ' is-active' : '' ?>" href="<?= e($href) ?>">
                    <?= ais_icon($r['icon']) ?><span><?= e($r['label']) ?> <small><?= e($r['en']) ?></small></span>
<?php if ($key === $role): ?><?= ais_icon('check', 'icon-xs ml-auto') ?><?php endif; ?>
                </a>
<?php endforeach; ?>
                <div class="dropdown__sep"></div>
                <a class="dropdown__item" href="<?= e(ais_url('settings')) ?>"><?= ais_icon('settings') ?>ตั้งค่า</a>
                <button type="button" class="dropdown__item" data-reset-demo><?= ais_icon('rotate-ccw') ?>Reset Demo Data</button>
                <a class="dropdown__item" href="../../"><?= ais_icon('log-out') ?>ออกจาก Demo</a>
            </div>
        </div>

        <a class="btn btn--ghost btn--sm hide-lg" href="../../"><?= ais_icon('arrow-left') ?>TB-Solution</a>
    </div>
</header>
<?php if ($role !== 'executive'): ?>
<div class="role-banner">
    <?= ais_icon('eye') ?>
    <span>กำลังสาธิตมุมมอง <strong><?= e($roleInfo['label']) ?> (<?= e($roleInfo['en']) ?>)</strong> — เฟสนี้เน้น Executive Dashboard เป็นหลัก</span>
    <a class="btn btn--sm btn--primary" href="<?= e(ais_url('overview')) ?>">กลับมุมมองผู้บริหาร</a>
</div>
<?php endif; ?>
