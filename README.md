# TB-Solution — Landing Page

> **Smart Solutions for a Better Tomorrow** · AI-Powered Digital Solutions

เว็บไซต์หน้าแรกของ TB-Solution สำหรับนำเสนอแบรนด์ บริการพัฒนาระบบ และเป็นประตูเข้าสู่ระบบ Demo
พัฒนาด้วย **PHP 8.2+, HTML5, CSS3 และ Vanilla JavaScript** ไม่ต้องใช้ Database, Composer หรือ Node.js

---

## ติดตั้งและรัน

### 1. PHP built-in server (สำหรับพัฒนา)

```bash
cd tb-demo
php -S 127.0.0.1:8000
# เปิด http://127.0.0.1:8000
```

เปิด debug log (ฝั่ง PHP แสดงใน terminal / ฝั่ง JS แสดงใน browser console):

```bash
APP_DEBUG=1 php -S 127.0.0.1:8000
```

### 2. Apache / Nginx (shared hosting)

อัปโหลดไฟล์ทั้งหมดไปที่ document root (เช่น `public_html/`)

- **Apache:** มี `.htaccess` ที่ปิดการเข้าถึง `config/`, `includes/`, `sections/` โดยตรง และตั้ง security headers ไว้แล้ว
- **Nginx:** เพิ่ม rule นี้

```nginx
location ~ ^/(config|includes|sections)/ { deny all; }
```

### 3. Docker

```bash
docker compose up -d --build
# เปิด http://localhost:8080
```

### Environment variables

| ตัวแปร | ค่าเริ่มต้น | คำอธิบาย |
|---|---|---|
| `APP_URL` | (ว่าง) | URL จริงของเว็บไซต์ ไม่มี `/` ปิดท้าย เช่น `https://tb-solution.com` ใช้สร้าง canonical, `og:url` และ `og:image` แบบ absolute |
| `APP_DEBUG` | `0` | `1` = เปิด dev log ทั้ง PHP และ JavaScript |

> ⚠️ ก่อนขึ้น production ควรตั้ง `APP_URL` เพราะ Facebook / LINE ต้องใช้ `og:image` แบบ absolute URL

---

## โครงสร้างโปรเจกต์

```
tb-demo/
├── index.php                  # Entry point: ประกอบ header + sections + footer
├── config/
│   ├── site.php               # แบรนด์, SEO, เมนู, ช่องทางติดต่อ
│   ├── content.php            # เนื้อหาทุก section (แก้ข้อความที่นี่)
│   └── demos.php              # รายการระบบ Demo (ว่างไว้ รอ Phase ถัดไป)
├── includes/
│   ├── functions.php          # helpers: e(), tb_config(), tb_icon(), tb_log() ...
│   ├── header.php             # <head>, meta SEO, Open Graph, JSON-LD
│   ├── navbar.php             # Sticky navbar + hamburger menu
│   ├── footer.php             # Footer + toast + script
│   └── components/
│       ├── demo-card.php      # การ์ดระบบ Demo
│       └── demo-placeholder.php  # การ์ด "Coming Soon"
├── sections/
│   ├── hero.php               # Section 2: Hero + dashboard จำลอง
│   ├── features.php           # Section 3: Key Features
│   ├── demo-systems.php       # Section 4: ตัวอย่างระบบงาน
│   ├── about.php              # Section 5: Why TB-Solution (Bento Grid)
│   ├── process.php            # Section 6: Development Process
│   └── contact.php            # Section 7: Contact / CTA
└── assets/
    ├── css/style.css          # Design system + components
    ├── css/responsive.css     # Breakpoints
    ├── js/app.js              # Interactions (Vanilla JS)
    └── images/
        ├── logo.svg           # Horizontal logo — พื้นขาว
        ├── logo-light.svg     # Horizontal logo — พื้นน้ำเงิน
        ├── logo-icon.svg      # Icon logo
        ├── favicon.svg
        ├── og-image.png       # ภาพแชร์ 1200×630
        └── illustrations/ai-core.svg
```

---

## การเพิ่มระบบ Demo (Phase ถัดไป)

แก้เฉพาะ `config/demos.php` — **ไม่ต้องแก้ HTML**

```php
return [
    [
        'slug'        => 'my-system',            // a-z, 0-9, -
        'name'        => 'ชื่อระบบ',
        'category'    => 'หมวดหมู่',
        'description' => 'คำอธิบายสั้น ๆ',
        'image'       => 'assets/images/demos/my-system.webp', // อัตราส่วน 16:10
        'tags'        => ['Responsive', 'AI'],
        'detail_url'  => 'demos/my-system/',
        'demo_url'    => 'demos/my-system/login.php',
        'status'      => 'ready',                // หรือ 'coming_soon'
    ],
];
```

พฤติกรรมอัตโนมัติ:

- การ์ด "Coming Soon" จะเติมให้ครบ `placeholder_count` (ตั้งใน `config/content.php`, ค่าเริ่มต้น 3) และหายไปเองเมื่อมีระบบจริงครบ
- เมื่อมีมากกว่า 1 หมวดหมู่ จะแสดงปุ่มกรองหมวดหมู่ให้อัตโนมัติ
- ลิงก์ที่ยังไม่กำหนด หรือสถานะ `coming_soon` จะแสดงข้อความแจ้งอย่างสุภาพแทนการพาไปหน้าที่ไม่มีอยู่
- รายการที่ไม่มี `slug` หรือ `name` จะถูกข้ามและบันทึก log

## การตั้งค่าช่องทางติดต่อ

แก้ `config/site.php` → `contact`:

- ค่าที่ว่างจะถูกซ่อนจาก footer / CTA อัตโนมัติ
- ปุ่ม **ติดต่อเรา** ใช้ `contact_url` → ถ้าไม่มีจะใช้ `mailto:email`
- ปุ่ม **ขอคำปรึกษา** ใช้ `consult_url` → ถ้าไม่มีจะใช้ `line_url`
- ถ้ายังไม่ได้ตั้งค่าใด ๆ ปุ่มจะแสดงข้อความ "กำลังจะเปิดให้บริการเร็ว ๆ นี้"

---

## Design System

| Token | ค่า | ใช้สำหรับ |
|---|---|---|
| `--c-primary` | `#2878E8` | ปุ่มหลัก, ลิงก์, ไอคอน |
| `--c-deep` | `#173B75` | หัวข้อ, CTA, footer |
| `--c-light` | `#EAF4FF` | พื้นหลังไอคอน, ส่วนเน้นอ่อน |
| `--c-yellow` | `#FFE9A8` | Accent: AI, จุดเด่น |
| `--c-yellow-soft` | `#FFF6D9` | พื้นหลังการ์ด AI |
| `--c-text` | `#18345B` | ข้อความหลัก |

- **Font:** Prompt (Google Fonts) 300–700
- **Radius:** 12 / 18 / 24 / 32 px · ปุ่มเป็น pill
- **สไตล์:** Soft glassmorphism, pastel gradient, bento grid, floating UI cards
- **Breakpoints:** 1280 · 1199 · 991 (เมนู hamburger) · 767 · 575 · 420
- **Icons:** ชุดไอคอนแบบ Lucide ฝังเป็น inline SVG ผ่าน `tb_icon('name')` (ไม่ต้องโหลดจาก CDN)

## มาตรฐานที่ใช้

- ทุกค่าที่แสดงผลผ่าน `e()` (`htmlspecialchars`) เพื่อป้องกัน XSS
- URL จาก config ผ่าน `tb_safe_url()` (อนุญาตเฉพาะ http/https/mailto/tel, anchor และ path)
- Section ที่ render ไม่สำเร็จจะแสดงข้อความสุภาพแทน โดยหน้าอื่นยังทำงานได้ตามปกติ
- รองรับ `prefers-reduced-motion`, skip link, `aria-*` บนเมนูมือถือ
- Lazy loading ภาพที่อยู่นอกหน้าจอแรก, cache busting ด้วย `?v=filemtime`
- SEO: meta description, Open Graph, Twitter Card, JSON-LD Organization

## ตรวจสอบ PHP Syntax

```bash
find . -name '*.php' -exec php -l {} \;
```
