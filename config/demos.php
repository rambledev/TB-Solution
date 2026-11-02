<?php

declare(strict_types=1);

/**
 * TB-Solution — demo system registry.
 *
 * Add one array per demo system. The landing page renders the list automatically;
 * no HTML changes are required. While the list is shorter than
 * content.demos.placeholder_count, "coming soon" cards fill the remaining slots.
 *
 * Supported fields:
 *   slug        (string, required)  unique id, used for filtering / anchors
 *   name        (string, required)  system name
 *   category    (string)            category label, also used by the filter chips
 *   description (string)            short description (1–2 sentences)
 *   image       (string)            preview image path, e.g. assets/images/demos/xxx.webp (16:10)
 *   tags        (string[])          optional small tags
 *   detail_url  (string)            "ดูรายละเอียด" link
 *   demo_url    (string)            "ทดลองใช้งาน" link
 *   status      ('ready'|'coming_soon')
 *   name_en     (string)            optional English product name
 *   badge       (string)            optional highlight badge, e.g. "AI Powered"
 *   featured    (bool)              show as the large featured card (takes two grid columns)
 *
 * Example (do not uncomment until the real demo exists):
 *
 *   [
 *       'slug'        => 'example-system',
 *       'name'        => 'ชื่อระบบ',
 *       'category'    => 'หมวดหมู่',
 *       'description' => 'คำอธิบายระบบโดยย่อ',
 *       'image'       => 'assets/images/demos/example-system.webp',
 *       'tags'        => ['Responsive', 'AI'],
 *       'detail_url'  => 'demos/example-system/',
 *       'demo_url'    => 'demos/example-system/login.php',
 *       'status'      => 'ready',
 *   ],
 */
return [
    [
        'slug'        => 'ai-school',
        'name'        => 'AI School Management',
        'name_en'     => 'AI School Management & Intelligence Platform',
        'category'    => 'Education / AI',
        'description' => 'ระบบบริหารจัดการและวิเคราะห์ข้อมูลโรงเรียนด้วย AI สำหรับผู้บริหาร ช่วยติดตามผลการดำเนินงาน วิเคราะห์ข้อมูลนักเรียน ครู และผลสัมฤทธิ์ทางการศึกษา',
        'image'       => 'demos/ai-school/assets/images/preview.svg',
        'tags'        => ['Executive Dashboard', 'AI Insights', 'AI Teacher Evaluation', 'Student Risk', 'Responsive'],
        'badge'       => 'AI Powered',
        'featured'    => true,
        'detail_url'  => 'demos/ai-school/about.php',
        'demo_url'    => 'demos/ai-school/',
        'status'      => 'ready',
    ],
    [
        'slug'        => 'ai-executive',
        'name'        => 'AI Executive Dashboard',
        'name_en'     => 'AI Executive Dashboard for School Leaders',
        'category'    => 'Education / AI',
        'description' => 'แดชบอร์ดสำหรับผู้อำนวยการโรงเรียน วิเคราะห์ภาพรวม ครู นักเรียน และห้องเรียนด้วย AI พร้อม Mock Data Generator สลับสถานการณ์โรงเรียนได้ทันที',
        'image'       => 'demos/ai-executive/assets/images/preview.svg',
        'tags'        => ['AI Analytics', 'Teacher Evaluation', 'Mock Data', 'Dark Mode', 'Export PDF/Excel'],
        'badge'       => 'ใหม่',
        'featured'    => false,
        'detail_url'  => 'demos/ai-executive/#ai',
        'demo_url'    => 'demos/ai-executive/',
        'status'      => 'ready',
    ],
];
