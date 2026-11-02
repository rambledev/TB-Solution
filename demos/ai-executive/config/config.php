<?php

declare(strict_types=1);

/**
 * AI Executive Dashboard — configuration.
 * This module is self-contained: it does not depend on any other demo in the repository.
 */
return [
    'app' => [
        'name'      => 'AI Executive Dashboard',
        'tagline'   => 'ระบบวิเคราะห์ข้อมูลโรงเรียนสำหรับผู้บริหาร',
        'version'   => 'Demo 1.0',
        'brand'     => 'TB-Solution',
    ],

    'school' => [
        'name'          => 'โรงเรียนตัวอย่าง Demo School',
        'academic_year' => '2569',
        'levels'        => 'ป.1 – ม.6',
    ],

    // Terms with data (oldest → newest). 2/2569 has not started yet → empty state demo.
    'terms' => [
        ['key' => '1/2568', 'year' => '2568', 'semester' => '1'],
        ['key' => '2/2568', 'year' => '2568', 'semester' => '2'],
        ['key' => '1/2569', 'year' => '2569', 'semester' => '1'],
    ],

    // Attendance months (6 months back, latest = current month in the dashboard).
    'months' => ['2026-05', '2026-06', '2026-07', '2026-08', '2026-09', '2026-10'],

    'menu' => [
        ['id' => 'overview',    'label' => 'Overview Dashboard',    'th' => 'ภาพรวมผู้บริหาร',      'icon' => 'layout-dashboard'],
        ['id' => 'ai',          'label' => 'AI School Analytics',   'th' => 'วิเคราะห์โรงเรียนด้วย AI', 'icon' => 'sparkles', 'ai' => true],
        ['id' => 'teachers',    'label' => 'AI Teacher Evaluation', 'th' => 'ประเมินครูด้วย AI',     'icon' => 'user-check', 'ai' => true],
        ['id' => 'students',    'label' => 'Student Analytics',     'th' => 'ผลการเรียนนักเรียน',    'icon' => 'graduation-cap'],
        ['id' => 'attendance',  'label' => 'Attendance Analytics',  'th' => 'การเข้าเรียน',          'icon' => 'calendar-check'],
        ['id' => 'classrooms',  'label' => 'Classroom Analytics',   'th' => 'เปรียบเทียบห้องเรียน',   'icon' => 'school'],
        ['id' => 'reports',     'label' => 'Executive Reports',     'th' => 'รายงานผู้บริหาร',       'icon' => 'file-chart-column'],
        ['id' => 'mock',        'label' => 'Mock Data Center',      'th' => 'จัดการข้อมูลจำลอง',     'icon' => 'database'],
        ['id' => 'settings',    'label' => 'Settings',              'th' => 'ตั้งค่า',               'icon' => 'settings'],
    ],

    /*
     * Mock data scenarios. Numbers are the base levels the generator works from;
     * every KPI shown in the UI is computed from the generated records.
     */
    'scenarios' => [
        'default'   => ['label' => 'Default Dataset',   'th' => 'ชุดข้อมูลมาตรฐาน',  'icon' => 'database',       'size' => 'normal', 'desc' => 'นักเรียน 600 คน ครู 35 คน 18 ห้อง สถานการณ์ทั่วไปที่มีทั้งจุดเด่นและจุดที่ต้องพัฒนา',
                        'att' => 94.6, 'att_trend' => 0.22, 'gpa' => 2.98, 'gpa_trend' => 0.05, 'sub' => 86, 'sub_trend' => 0.6, 'dev' => 1.2, 'risk' => 0.05, 'load_spread' => 1.0, 'problem_rooms' => 1, 'star_rooms' => 2],
        'large'     => ['label' => 'Large Dataset',     'th' => 'โรงเรียนขนาดใหญ่',  'icon' => 'layers',         'size' => 'large',  'desc' => 'นักเรียนประมาณ 1,470 คน ครู 84 คน 42 ห้อง สำหรับทดสอบการแสดงผลข้อมูลจำนวนมาก',
                        'att' => 94.3, 'att_trend' => 0.15, 'gpa' => 2.92, 'gpa_trend' => 0.03, 'sub' => 85, 'sub_trend' => 0.4, 'dev' => 1.0, 'risk' => 0.06, 'load_spread' => 1.2, 'problem_rooms' => 3, 'star_rooms' => 4],
        'excellent' => ['label' => 'Excellent School',  'th' => 'โรงเรียนผลงานดีเยี่ยม', 'icon' => 'trophy',     'size' => 'normal', 'desc' => 'การเข้าเรียนสูง ผลการเรียนดี ส่งงานครบ พัฒนาการดีขึ้นต่อเนื่อง',
                        'att' => 97.3, 'att_trend' => 0.18, 'gpa' => 3.38, 'gpa_trend' => 0.07, 'sub' => 94, 'sub_trend' => 0.5, 'dev' => 1.8, 'risk' => 0.012, 'load_spread' => 0.6, 'problem_rooms' => 0, 'star_rooms' => 4],
        'average'   => ['label' => 'Average School',    'th' => 'โรงเรียนระดับปานกลาง', 'icon' => 'scale',        'size' => 'normal', 'desc' => 'ผลงานระดับปานกลาง มีปัญหาบางห้องเรียน และมีนักเรียนที่ต้องติดตาม',
                        'att' => 93.4, 'att_trend' => -0.08, 'gpa' => 2.86, 'gpa_trend' => 0.0, 'sub' => 80, 'sub_trend' => 0.0, 'dev' => 0.3, 'risk' => 0.09, 'load_spread' => 1.2, 'problem_rooms' => 3, 'star_rooms' => 1],
        'at-risk'   => ['label' => 'At-Risk School',    'th' => 'โรงเรียนกลุ่มเสี่ยง', 'icon' => 'triangle-alert', 'size' => 'normal', 'desc' => 'การเข้าเรียนลดลง ผลสัมฤทธิ์ต่ำ นักเรียนเสี่ยงหลายกลุ่ม ส่งงานต่ำ ภาระงานครูไม่สมดุล',
                        'att' => 91.8, 'att_trend' => -0.75, 'gpa' => 2.66, 'gpa_trend' => -0.09, 'sub' => 74, 'sub_trend' => -1.4, 'dev' => -0.9, 'risk' => 0.14, 'load_spread' => 2.2, 'problem_rooms' => 6, 'star_rooms' => 0],
    ],

    'default_scenario' => 'default',
    'default_seed'     => 2569,
];
