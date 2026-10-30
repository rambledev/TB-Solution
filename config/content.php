<?php

declare(strict_types=1);

/**
 * TB-Solution — section content.
 *
 * `icon` must be a key from tb_icon() in includes/functions.php.
 */
return [
    'hero' => [
        'eyebrow'     => 'AI-Powered Digital Solutions',
        'title_line1' => 'พัฒนาระบบงาน',
        'title_line2' => 'ด้วยพลังของ',
        'highlight'   => 'AI',
        'description' => 'TB-Solution รวบรวมแนวทางการพัฒนาระบบงานอัจฉริยะ ด้วยเทคโนโลยี AI ที่ช่วยวิเคราะห์ปัญหา เพิ่มประสิทธิภาพ และออกแบบระบบให้ตอบโจทย์การทำงานขององค์กร',
        'primary'     => ['label' => 'ดูระบบทั้งหมด', 'url' => '#demos'],
        'secondary'   => ['label' => 'เกี่ยวกับ TB-Solution', 'url' => '#about'],
        'badges'      => ['Desktop · Tablet · Mobile', 'ปรับแต่งได้ตามองค์กร'],
    ],

    'features' => [
        'eyebrow'     => 'Key Features',
        'title'       => 'จุดเด่นของระบบที่เราพัฒนา',
        'description' => 'ทุกระบบถูกออกแบบด้วยหลักคิดเดียวกัน คือใช้งานได้จริง ฉลาดขึ้นด้วย AI และเติบโตไปพร้อมกับองค์กร',
        'items' => [
            [
                'icon'        => 'sparkles',
                'title'       => 'AI Powered',
                'subtitle'    => 'AI ช่วยวิเคราะห์และแก้ไขปัญหา',
                'description' => 'นำ AI มาช่วยวิเคราะห์ข้อมูล ค้นหาจุดที่เป็นปัญหา และแนะนำแนวทางแก้ไขอย่างเป็นระบบ',
                'accent'      => true,
            ],
            [
                'icon'        => 'monitor-smartphone',
                'title'       => 'Responsive',
                'subtitle'    => 'รองรับทุกอุปกรณ์',
                'description' => 'ใช้งานได้ลื่นไหลทั้ง Desktop, Tablet และ Mobile ทำงานได้ทุกที่ทุกเวลา',
            ],
            [
                'icon'        => 'sliders',
                'title'       => 'Customizable',
                'subtitle'    => 'ปรับให้เหมาะกับองค์กร',
                'description' => 'ออกแบบขั้นตอนและหน้าจอให้ตรงกับรูปแบบการทำงานเฉพาะของแต่ละองค์กร',
            ],
            [
                'icon'        => 'pointer',
                'title'       => 'Easy to Use',
                'subtitle'    => 'ใช้งานง่าย ไม่ซับซ้อน',
                'description' => 'หน้าจอเรียบง่าย เข้าใจได้ทันที ลดเวลาเรียนรู้ของผู้ใช้งานทุกระดับ',
            ],
        ],
    ],

    'demos' => [
        'eyebrow'     => 'Demo Systems',
        'title'       => 'ตัวอย่างระบบงาน',
        'description' => 'เลือกชมระบบตัวอย่างที่เราพัฒนา เพื่อเป็นแนวทางสำหรับองค์กรของคุณ',
        // Number of "coming soon" cards to show while the demo list is still short
        'placeholder_count' => 3,
        'placeholder' => [
            'category'    => 'กำลังจัดเตรียม',
            'title'       => 'ระบบตัวอย่างใหม่เร็ว ๆ นี้',
            'description' => 'เรากำลังจัดเตรียมระบบตัวอย่าง เพื่อให้คุณได้ทดลองใช้งานจริงในเร็ว ๆ นี้',
        ],
        'notice' => [
            'title'       => 'มีระบบที่อยากเห็นเป็นพิเศษ?',
            'description' => 'บอกเราได้เลย เราพร้อมนำเสนอแนวทางที่ตรงกับการทำงานขององค์กรคุณ',
            'cta'         => 'พูดคุยกับเรา',
        ],
    ],

    'why' => [
        'eyebrow'     => 'Why TB-Solution',
        'title'       => 'ทำไมต้องเลือก TB-Solution',
        'description' => 'เราไม่ได้เริ่มจากการเขียนโค้ด แต่เริ่มจากการเข้าใจปัญหาของคุณ',
        'items' => [
            'analysis' => [
                'icon'        => 'search',
                'title'       => 'วิเคราะห์ปัญหาก่อนพัฒนาระบบ',
                'description' => 'ทำความเข้าใจขั้นตอนการทำงานจริง ระบุจุดติดขัด แล้วจึงออกแบบโซลูชันที่แก้ได้ตรงจุด',
                'flow'        => ['ปัญหาเดิม', 'วิเคราะห์', 'โซลูชัน'],
            ],
            'ai' => [
                'icon'        => 'sparkles',
                'title'       => 'ใช้ AI เป็นเครื่องมือช่วยเพิ่มประสิทธิภาพ',
                'description' => 'AI ช่วยทั้งในกระบวนการพัฒนาและในตัวระบบ ทำให้ได้งานที่เร็วขึ้นและแม่นยำขึ้น',
                'chips'       => ['วิเคราะห์ข้อมูล', 'สรุปรายงาน', 'ลดงานซ้ำซ้อน'],
            ],
            'fit' => [
                'icon'        => 'target',
                'title'       => 'ออกแบบให้เหมาะกับการทำงานจริง',
                'description' => 'ทุกหน้าจอและทุกขั้นตอนอ้างอิงจากผู้ใช้งานจริง ไม่ใช่ระบบสำเร็จรูปที่ต้องปรับตัวตาม',
            ],
            'devices' => [
                'icon'        => 'monitor-smartphone',
                'title'       => 'รองรับการใช้งานทุกอุปกรณ์',
                'description' => 'Responsive Design ที่แสดงผลสวยงามบนทุกขนาดหน้าจอ',
            ],
            'scale' => [
                'icon'        => 'trending-up',
                'title'       => 'พัฒนาต่อยอดได้ในอนาคต',
                'description' => 'โครงสร้างระบบยืดหยุ่น เพิ่มฟีเจอร์และเชื่อมต่อระบบอื่นได้เมื่อองค์กรเติบโต',
            ],
        ],
    ],

    'process' => [
        'eyebrow'     => 'Development Process',
        'title'       => 'ขั้นตอนการให้บริการ',
        'description' => 'กระบวนการทำงานที่ชัดเจน โปร่งใส และตรวจสอบได้ในทุกขั้นตอน',
        'steps' => [
            ['icon' => 'clipboard',  'title' => 'Requirement Analysis',    'description' => 'รับฟังความต้องการ และวิเคราะห์ปัญหาของการทำงานจริงอย่างละเอียด'],
            ['icon' => 'pen-tool',   'title' => 'Solution Design',         'description' => 'ออกแบบโครงสร้างระบบ ขั้นตอนการทำงาน และหน้าจอให้เหมาะกับผู้ใช้'],
            ['icon' => 'cpu',        'title' => 'AI-Assisted Development', 'description' => 'พัฒนาระบบโดยมี AI ช่วยเพิ่มความเร็วและคุณภาพของงาน'],
            ['icon' => 'rocket',     'title' => 'Testing & Deployment',    'description' => 'ทดสอบอย่างรอบด้าน ก่อนติดตั้งและเปิดใช้งานจริง'],
            ['icon' => 'life-buoy',  'title' => 'Support & Improvement',   'description' => 'ดูแลหลังการใช้งาน และพัฒนาต่อยอดอย่างต่อเนื่อง'],
        ],
    ],

    'cta' => [
        'eyebrow'     => "Let's build together",
        'title'       => 'มีไอเดียระบบที่อยากพัฒนา?',
        'description' => 'ให้เราช่วยเปลี่ยนปัญหาการทำงานให้กลายเป็นโซลูชันดิจิทัล',
        'primary'     => 'ติดต่อเรา',
        'secondary'   => 'ขอคำปรึกษา',
        'points'      => ['วิเคราะห์ความต้องการเบื้องต้น', 'แนะนำแนวทางที่เหมาะสม', 'ออกแบบให้ตรงกับงบประมาณ'],
    ],
];
