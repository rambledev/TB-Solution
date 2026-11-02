<?php

declare(strict_types=1);
?>
<header class="topbar">
    <button type="button" class="icon-btn topbar__menu" data-action="sidebar-open" aria-label="เปิดเมนู" aria-controls="sidebar"><?= exe_icon('menu') ?></button>
    <div class="topbar__title">
        <p class="topbar__crumb"><span data-bind="school"><?= exe_e(exe_config('school.name')) ?></span></p>
        <h1 id="page-title">Overview Dashboard</h1>
    </div>
    <div class="topbar__actions">
        <span class="chip-demo" title="ข้อมูลทั้งหมดเป็นข้อมูลจำลอง"><?= exe_icon('flask-conical') ?><span data-bind="scenario">Default</span></span>
        <button type="button" class="btn btn--ai" data-action="open-generator"><?= exe_icon('wand-sparkles') ?><span class="hide-sm">Generate Mock Data</span></button>
        <button type="button" class="icon-btn icon-btn--ring" data-action="toggle-theme" aria-label="สลับโหมดมืด/สว่าง" title="Dark Mode"><span class="theme-icon theme-icon--moon"><?= exe_icon('moon') ?></span><span class="theme-icon theme-icon--sun"><?= exe_icon('sun') ?></span></button>
        <div class="dropdown" data-dropdown>
            <button type="button" class="icon-btn icon-btn--ring" data-dropdown-toggle aria-expanded="false" aria-label="การแจ้งเตือน"><?= exe_icon('bell') ?><span class="dot-count" id="notif-count"></span></button>
            <div class="dropdown__menu" role="menu">
                <div class="dropdown__head"><strong>การแจ้งเตือนจาก AI</strong><small>อัปเดตตามข้อมูลปัจจุบัน</small></div>
                <ul class="notif-list" id="notif-list"></ul>
                <a class="dropdown__foot" href="#ai">ดู AI School Analytics <?= exe_icon('arrow-right') ?></a>
            </div>
        </div>
        <div class="dropdown hide-md" data-dropdown>
            <button type="button" class="profile" data-dropdown-toggle aria-expanded="false" aria-label="เมนูผู้ใช้">
                <span class="avatar"><?= exe_icon('user-round') ?></span>
                <span class="profile__text"><strong>ผู้อำนวยการ</strong><small>บัญชีสาธิต · ไม่ต้อง Login</small></span>
                <?= exe_icon('chevron-down', 'icon-sm') ?>
            </button>
            <div class="dropdown__menu dropdown__menu--sm" role="menu">
                <a class="dropdown__item" href="#settings"><?= exe_icon('settings') ?>ตั้งค่า</a>
                <a class="dropdown__item" href="#mock"><?= exe_icon('database') ?>Mock Data Center</a>
                <button type="button" class="dropdown__item" data-action="reset"><?= exe_icon('rotate-ccw') ?>Reset to Default</button>
                <a class="dropdown__item" href="../../"><?= exe_icon('log-out') ?>กลับหน้า TB-Solution</a>
            </div>
        </div>
    </div>
</header>
