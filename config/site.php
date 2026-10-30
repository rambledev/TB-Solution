<?php

declare(strict_types=1);

/**
 * TB-Solution — global site configuration.
 *
 * Change brand, SEO, navigation and contact channels here.
 * Templates read every value through tb_config(), so no HTML edits are needed.
 */
return [
    'brand' => [
        'name'    => 'TB-Solution',
        'tagline' => 'Smart Solutions for a Better Tomorrow',
        'concept' => 'AI-Powered Digital Solutions',
        'summary' => 'เราออกแบบและพัฒนาระบบงานดิจิทัลที่ใช้ AI ช่วยวิเคราะห์ปัญหา ลดขั้นตอน และเพิ่มประสิทธิภาพให้ทุกองค์กร',
    ],

    'seo' => [
        'title'       => 'TB-Solution | พัฒนาระบบงานด้วยพลังของ AI',
        'description' => 'TB-Solution พัฒนาระบบงานอัจฉริยะด้วยเทคโนโลยี AI ช่วยวิเคราะห์ปัญหา เพิ่มประสิทธิภาพ และออกแบบระบบที่รองรับทุกอุปกรณ์ ให้ตอบโจทย์การทำงานขององค์กร',
        'keywords'    => 'TB-Solution, พัฒนาระบบ, AI, ระบบงานองค์กร, Software Development, Responsive Web, ระบบอัตโนมัติ',
        // Public base URL without trailing slash, e.g. https://tb-solution.com (used for canonical / Open Graph)
        'base_url'    => getenv('APP_URL') ?: '',
        // Relative path to a 1200x630 PNG/JPG share image
        'og_image'    => 'assets/images/og-image.png',
        'locale'      => 'th_TH',
        'theme_color' => '#2878E8',
    ],

    'nav' => [
        ['label' => 'หน้าแรก',      'href' => '#home'],
        ['label' => 'ระบบทั้งหมด',  'href' => '#demos'],
        ['label' => 'เกี่ยวกับเรา',  'href' => '#about'],
        ['label' => 'บริการ',       'href' => '#services'],
        ['label' => 'ติดต่อเรา',     'href' => '#contact'],
    ],

    // Entry point of the demo portal (no login required in the demo phase)
    'demo_entry' => [
        'label' => 'เข้าสู่ระบบ Demo',
        'url'   => 'demos/ai-school/',
    ],

    /*
     * Contact channels — leave a value empty to hide it.
     * Buttons without a usable link show a friendly "coming soon" notice instead.
     * TODO: fill in real contact details before going live.
     */
    'contact' => [
        'email'       => '',
        'phone'       => '',
        'line_id'     => '',
        'line_url'    => '',
        'address'     => '',
        // Primary "ติดต่อเรา" button; falls back to mailto:email when empty
        'contact_url' => '',
        // "ขอคำปรึกษา" button, e.g. a Google Form / booking page
        'consult_url' => '',
    ],
];
