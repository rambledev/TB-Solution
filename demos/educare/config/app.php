<?php

declare(strict_types=1);

/**
 * EduCare Demo — configuration (self-contained module).
 * Roles: Teacher · Parent · Executive. There is intentionally no login and no Super Admin.
 */
return [
    'brand' => [
        'product' => 'EduCare',
        'parent'  => 'TB-Solution',
        'tagline' => 'ดูแลทุกการเรียนรู้ เติบโตทุกช่วงวัย',
        'summary' => 'แพลตฟอร์มบริหารจัดการข้อมูลนักเรียน เชื่อมโยงโรงเรียน ครู และผู้ปกครองไว้ในระบบเดียว',
    ],

    'roles' => [
        'teacher' => [
            'label' => 'ครู', 'en' => 'Teacher', 'icon' => 'bi-person-workspace', 'color' => 'primary',
            'title' => 'สำหรับครู', 'desc' => 'จัดการห้องเรียน บันทึกกิจกรรม ติดตามนักเรียน', 'cta' => 'ทดลองใช้งานครู',
            'menu' => [
                ['id' => 'dashboard',     'label' => 'Dashboard',              'icon' => 'bi-grid-1x2'],
                ['id' => 'classrooms',    'label' => 'ห้องเรียนของฉัน',         'icon' => 'bi-door-open'],
                ['id' => 'students',      'label' => 'ข้อมูลนักเรียน',          'icon' => 'bi-people'],
                ['id' => 'activities',    'label' => 'บันทึกกิจกรรมประจำวัน',   'icon' => 'bi-journal-text'],
                ['id' => 'attendance',    'label' => 'การเข้าเรียน',            'icon' => 'bi-clipboard-check', 'mobile' => true],
                ['id' => 'meals',         'label' => 'เมนูอาหาร',              'icon' => 'bi-egg-fried'],
                ['id' => 'portfolio',     'label' => 'Student Portfolio',      'icon' => 'bi-images'],
                ['id' => 'stars',         'label' => 'ระบบสะสมดาว',            'icon' => 'bi-star'],
                ['id' => 'announcements', 'label' => 'ประกาศ',                 'icon' => 'bi-megaphone'],
                ['id' => 'calendar',      'label' => 'ปฏิทินกิจกรรม',          'icon' => 'bi-calendar3'],
                ['id' => 'reports',       'label' => 'รายงาน',                 'icon' => 'bi-file-earmark-text'],
            ],
            'mobile' => ['dashboard', 'attendance', 'activities', 'stars'],
        ],
        'parent' => [
            'label' => 'ผู้ปกครอง', 'en' => 'Parent', 'icon' => 'bi-people-fill', 'color' => 'sky',
            'title' => 'สำหรับผู้ปกครอง', 'desc' => 'ติดตามกิจกรรม ผลงาน และพัฒนาการของบุตรหลาน', 'cta' => 'ทดลองใช้งานผู้ปกครอง',
            'menu' => [
                ['id' => 'overview',      'label' => 'ภาพรวมบุตรหลาน',  'icon' => 'bi-house-heart'],
                ['id' => 'activities',    'label' => 'กิจกรรมประจำวัน',  'icon' => 'bi-journal-text'],
                ['id' => 'attendance',    'label' => 'การเข้าเรียน',     'icon' => 'bi-clipboard-check'],
                ['id' => 'meals',         'label' => 'เมนูอาหาร',       'icon' => 'bi-egg-fried'],
                ['id' => 'portfolio',     'label' => 'Student Portfolio', 'icon' => 'bi-images'],
                ['id' => 'stars',         'label' => 'ดาวสะสม',         'icon' => 'bi-star'],
                ['id' => 'announcements', 'label' => 'ประกาศโรงเรียน',  'icon' => 'bi-megaphone'],
                ['id' => 'calendar',      'label' => 'ปฏิทินกิจกรรม',   'icon' => 'bi-calendar3'],
                ['id' => 'development',   'label' => 'รายงานพัฒนาการ',  'icon' => 'bi-graph-up-arrow'],
                ['id' => 'notifications', 'label' => 'การแจ้งเตือน',    'icon' => 'bi-bell'],
            ],
            'mobile' => ['overview', 'activities', 'stars', 'notifications'],
        ],
        'executive' => [
            'label' => 'ผู้บริหาร', 'en' => 'Executive', 'icon' => 'bi-building', 'color' => 'navy',
            'title' => 'สำหรับผู้บริหาร', 'desc' => 'ดูภาพรวมโรงเรียน สถิตินักเรียน และรายงาน', 'cta' => 'ทดลองใช้งานผู้บริหาร',
            'menu' => [
                ['id' => 'overview',      'label' => 'Executive Overview',  'icon' => 'bi-speedometer2'],
                ['id' => 'students',      'label' => 'ข้อมูลนักเรียน',       'icon' => 'bi-people'],
                ['id' => 'attendance',    'label' => 'สถิติการเข้าเรียน',    'icon' => 'bi-bar-chart-line'],
                ['id' => 'activities',    'label' => 'สถิติกิจกรรม',         'icon' => 'bi-activity'],
                ['id' => 'portfolio',     'label' => 'ผลงานนักเรียน',        'icon' => 'bi-images'],
                ['id' => 'development',   'label' => 'พัฒนาการนักเรียน',     'icon' => 'bi-graph-up-arrow'],
                ['id' => 'reports',       'label' => 'รายงาน',              'icon' => 'bi-file-earmark-text'],
                ['id' => 'announcements', 'label' => 'ประกาศและกิจกรรม',    'icon' => 'bi-megaphone'],
            ],
            'mobile' => ['overview', 'attendance', 'students', 'reports'],
        ],
    ],

    /*
     * Notification rules (read-only in the demo — there is no central admin page).
     * Channels are simulated: nothing is sent to real Push or LINE services.
     */
    'rules' => [
        ['event' => 'student_absent',  'label' => 'นักเรียนขาดเรียน',   'recipient' => 'parent', 'channels' => ['inapp', 'push', 'line'], 'timing' => 'immediate', 'enabled' => true],
        ['event' => 'student_late',    'label' => 'นักเรียนมาสาย',     'recipient' => 'parent', 'channels' => ['inapp', 'push'],         'timing' => 'immediate', 'enabled' => true],
        ['event' => 'student_present', 'label' => 'นักเรียนเข้าเรียน',  'recipient' => 'parent', 'channels' => ['inapp'],                 'timing' => 'immediate', 'enabled' => true],
        ['event' => 'daily_activity',  'label' => 'กิจกรรมประจำวัน',    'recipient' => 'parent', 'channels' => ['inapp', 'push'],         'timing' => 'immediate', 'enabled' => true],
        ['event' => 'portfolio_added', 'label' => 'เพิ่มผลงาน',         'recipient' => 'parent', 'channels' => ['inapp', 'push'],         'timing' => 'immediate', 'enabled' => true],
        ['event' => 'star_awarded',    'label' => 'ได้รับดาว',          'recipient' => 'parent', 'channels' => ['inapp', 'push'],         'timing' => 'immediate', 'enabled' => true],
        ['event' => 'announcement',    'label' => 'ประกาศใหม่',         'recipient' => 'parent', 'channels' => ['inapp', 'push', 'line'], 'timing' => 'immediate', 'enabled' => true],
        ['event' => 'school_event',    'label' => 'กิจกรรมโรงเรียน',    'recipient' => 'parent', 'channels' => ['inapp', 'line'],         'timing' => 'immediate', 'enabled' => true],
    ],
];
