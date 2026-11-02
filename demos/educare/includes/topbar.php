<?php

declare(strict_types=1);

/**
 * @var string $role
 * @var array  $roleConfig
 */
?>
<header class="topbar">
    <button type="button" class="btn btn-icon d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="เปิดเมนู"><i class="bi bi-list"></i></button>
    <div class="topbar__title">
        <small class="topbar__crumb"><span class="badge-demo"><i class="bi bi-stars"></i> DEMO</span> <span id="context-label"></span></small>
        <h1 id="page-title">EduCare</h1>
    </div>
    <div class="topbar__context" id="context-slot"></div>
    <div class="topbar__actions">
        <div class="dropdown">
            <button type="button" class="btn btn-icon btn-icon--ring position-relative" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="การแจ้งเตือน">
                <i class="bi bi-bell"></i><span class="dot-badge" id="bell-count" hidden></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end notif-menu shadow">
                <div class="notif-menu__head"><strong id="bell-title">การแจ้งเตือน</strong><button type="button" class="btn btn-link btn-sm p-0" data-action="mark-all-read">อ่านทั้งหมด</button></div>
                <div class="notif-menu__list" id="bell-list"></div>
                <a class="notif-menu__foot" href="#notifications" id="bell-more">ดูทั้งหมด</a>
            </div>
        </div>
        <a class="btn btn-switch rounded-pill" href="./#roles" title="กลับไปหน้าเลือกบทบาท (ข้อมูลไม่หาย)"><i class="bi bi-arrow-left-right"></i><span class="d-none d-md-inline ms-1">เปลี่ยนบทบาท</span></a>
    </div>
</header>
