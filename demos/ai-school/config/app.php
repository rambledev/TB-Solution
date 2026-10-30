<?php

declare(strict_types=1);

/**
 * AI School Intelligence — module configuration.
 *
 * Every page reads values through ais_config(). Icons are Lucide icon names.
 */
return [
    'product' => [
        'name'      => 'AI School Intelligence',
        'full_name' => 'AI School Management & Intelligence Platform',
        'name_th'   => 'AI School Management',
        'version'   => 'Demo 1.0',
    ],

    'school' => [
        'name'          => 'โรงเรียนตัวอย่าง TB Academy',
        'name_en'       => 'TB Academy (Demo School)',
        'academic_year' => '2569',
        'current_term'  => '1/2569',
        'levels'        => 'อนุบาล 1 – มัธยมศึกษาปีที่ 6',
        'address'       => 'ข้อมูลจำลองสำหรับการสาธิตเท่านั้น',
    ],

    // Global period filter (topbar). Values are the number of months back from the latest month.
    'periods' => [
        '3m'  => ['label' => '3 เดือนล่าสุด', 'months' => 3],
        '6m'  => ['label' => '6 เดือนล่าสุด', 'months' => 6],
        '12m' => ['label' => '12 เดือนล่าสุด', 'months' => 12],
    ],
    'default_period' => '6m',

    'nav' => [
        ['group' => 'ภาพรวม', 'items' => [
            ['page' => 'overview',    'label' => 'Overview',            'th' => 'ภาพรวมผู้บริหาร',   'icon' => 'layout-dashboard'],
        ]],
        ['group' => 'การวิเคราะห์', 'items' => [
            ['page' => 'students',    'label' => 'Student Intelligence', 'th' => 'ข้อมูลนักเรียน',      'icon' => 'graduation-cap'],
            ['page' => 'academics',   'label' => 'Academic Analytics',   'th' => 'ผลสัมฤทธิ์ทางการเรียน', 'icon' => 'book-open-check'],
            ['page' => 'teachers',    'label' => 'Teacher Intelligence', 'th' => 'ข้อมูลครูและบุคลากร',  'icon' => 'users'],
        ]],
        ['group' => 'AI Intelligence', 'items' => [
            ['page' => 'teacher-evaluation', 'label' => 'AI Teacher Evaluation', 'th' => 'ประเมินครูด้วย AI',      'icon' => 'clipboard-check', 'badge' => 'AI'],
            ['page' => 'student-risk',       'label' => 'Student Risk Analysis', 'th' => 'นักเรียนที่ควรติดตาม',  'icon' => 'heart-handshake'],
            ['page' => 'ai-insights',        'label' => 'AI Insights',           'th' => 'ศูนย์วิเคราะห์ AI',     'icon' => 'sparkles', 'badge' => 'AI'],
            ['page' => 'performance',        'label' => 'School Performance',    'th' => 'ผลการดำเนินงาน',       'icon' => 'gauge'],
        ]],
        ['group' => 'รายงานและข้อมูล', 'items' => [
            ['page' => 'reports',     'label' => 'Executive Reports',  'th' => 'รายงานผู้บริหาร', 'icon' => 'file-text'],
            ['page' => 'data-import', 'label' => 'Data Import Center', 'th' => 'นำเข้าข้อมูล',   'icon' => 'database'],
            ['page' => 'settings',    'label' => 'Settings',           'th' => 'ตั้งค่า',         'icon' => 'settings'],
        ]],
    ],

    // Demo role selector. Only "executive" has the full navigation in this phase.
    'roles' => [
        'executive' => ['label' => 'ผู้บริหาร',   'en' => 'Executive', 'icon' => 'briefcase',      'user' => 'ผู้บริหาร (บัญชีสาธิต)', 'title' => 'ผู้อำนวยการโรงเรียน'],
        'teacher'   => ['label' => 'ครู',         'en' => 'Teacher',   'icon' => 'presentation',   'user' => 'ครู (บัญชีสาธิต)',       'title' => 'ครูประจำชั้น'],
        'staff'     => ['label' => 'บุคลากร',    'en' => 'Staff',     'icon' => 'clipboard-list', 'user' => 'บุคลากร (บัญชีสาธิต)',  'title' => 'เจ้าหน้าที่งานทะเบียน'],
        'student'   => ['label' => 'นักเรียน',    'en' => 'Student',   'icon' => 'backpack',       'user' => 'นักเรียน (บัญชีสาธิต)', 'title' => 'นักเรียน'],
        'parent'    => ['label' => 'ผู้ปกครอง',  'en' => 'Parent',    'icon' => 'house',          'user' => 'ผู้ปกครอง (บัญชีสาธิต)', 'title' => 'ผู้ปกครอง'],
    ],

    /*
     * AI provider. "mock" = rule-based simulation over the demo data (default).
     * Set AI_PROVIDER=openai and OPENAI_API_KEY to use the OpenAI Chat Completions API;
     * any failure falls back to the mock provider.
     */
    'ai' => [
        'provider'     => strtolower((string) (getenv('AI_PROVIDER') ?: 'mock')),
        'openai_key'   => (string) (getenv('OPENAI_API_KEY') ?: ''),
        'openai_model' => (string) (getenv('OPENAI_MODEL') ?: 'gpt-4o-mini'),
        'timeout'      => 25,
    ],
];
