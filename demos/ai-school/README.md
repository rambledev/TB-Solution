# AI School Management & Intelligence Platform (Demo)

ระบบตัวอย่างแรกของ TB-Solution สำหรับนำเสนอลูกค้า — ระบบบริหารจัดการและวิเคราะห์ข้อมูลโรงเรียนด้วย AI สำหรับผู้บริหาร
ทดลองใช้ได้ทันทีโดยไม่ต้อง Login และใช้ **ข้อมูลจำลองทั้งหมด** (โรงเรียนตัวอย่าง TB Academy ปีการศึกษา 2569)

- URL: `/demos/ai-school/`
- หน้ารายละเอียดสินค้า: `/demos/ai-school/about.php`
- Stack: PHP 8.2+ · Vanilla JavaScript · Chart.js 4.4.1 · Lucide 0.460.0 · IBM Plex Sans Thai
  (ไม่ใช้ React, Next.js, Node.js backend หรือ TypeScript)

---

## 1. คู่มือเปิดใช้งาน

### วิธีที่ 1 — PHP built-in server (สำหรับพัฒนา)

```bash
cd tb-demo                      # root ของเว็บไซต์ TB-Solution
php -S localhost:8000           # PHP 8.2 ขึ้นไป
```

เปิด http://localhost:8000 (Landing Page) แล้วกด **ทดลองใช้งาน** ที่การ์ด AI School Management
หรือเข้าตรงที่ http://localhost:8000/demos/ai-school/

> ต้องมี `/` ปิดท้าย URL (`/demos/ai-school/`) เพราะลิงก์ในโมดูลใช้ path แบบ relative

เปิด debug log (ฝั่ง PHP แสดงใน terminal และฝั่ง browser แสดงใน Console):

```bash
APP_DEBUG=1 php -S localhost:8000
```

### วิธีที่ 2 — Docker

```bash
docker compose up -d --build    # เปิดที่ http://localhost:8080
```

บน Coolify ให้เลือก Build Pack เป็น **Dockerfile** และตั้ง port เป็น **80**

### Environment variables

| ตัวแปร | ค่าเริ่มต้น | คำอธิบาย |
|---|---|---|
| `APP_DEBUG` | `0` | `1` = เปิด log `[Module][Function]` ทั้ง PHP และ browser |
| `AI_PROVIDER` | `mock` | `mock` = จำลองแบบ Rule-based · `openai` = ใช้ OpenAI API |
| `OPENAI_API_KEY` | – | จำเป็นเมื่อ `AI_PROVIDER=openai` |
| `OPENAI_MODEL` | `gpt-4o-mini` | ชื่อโมเดลที่ใช้ |

---

## 2. โมดูลในระบบ

| เมนู | ไฟล์ | สิ่งที่ทำได้ |
|---|---|---|
| Overview | `pages/overview.php` | KPI 6 ใบ, AI Executive Summary (SWOT + Actions), กราฟ 6 ชุดสลับมุมมองได้, คลิกกราฟความเสี่ยงเพื่อดูรายชื่อ |
| Student Intelligence | `pages/students.php` | ตัวกรองระดับการศึกษา/ระดับชั้น, นักเรียนรายชั้น/ห้อง, การเข้าเรียน, ย้ายเข้า–ออก, ตารางค้นหา/เรียงลำดับ |
| Academic Analytics | `pages/academics.php` | GPA 3 ภาคเรียน, คะแนนรายวิชา, อัตราผ่าน/ไม่ผ่าน, วิชาที่ต้องปรับปรุง, Heatmap ระดับชั้น × วิชา |
| Teacher Intelligence | `pages/teachers.php` | ภาระงาน ชั่วโมงสอน บันทึกการสอน การอบรม ผลงาน, คลิกแถวไปหน้าประเมิน |
| AI Teacher Evaluation | `pages/teacher-evaluation.php` | รายชื่อครู (ค้นหา/กรอง/เรียง), 9 มิติ + Radar, จุดแข็ง/จุดที่ควรพัฒนา, ข้อเสนอแนะ, ประวัติ, แหล่งข้อมูล & ความครบถ้วน, ข้อจำกัด |
| Student Risk Analysis | `pages/student-risk.php` | การ์ด High/Medium/Low (กดเพื่อกรอง), สาเหตุที่พบ, รายชื่อ + Modal แผนช่วยเหลือ, โหมดปกปิดชื่อ |
| AI Insights | `pages/ai-insights.php` | ขอบเขตการวิเคราะห์ 6 ด้าน, Insight Cards 7 รายการ + Modal, AI Chat Assistant |
| School Performance | `pages/performance.php` | KPI 6 ด้านเทียบเป้าหมาย, กราฟรายเดือน (เลือก KPI ได้), ตารางรายเดือน |
| Executive Reports | `pages/reports.php`, `report.php` | รายงาน 6 ชุด: Preview (Modal), Print, Export PDF (ผ่านหน้าต่างพิมพ์ของเบราว์เซอร์) |
| Data Import Center | `pages/data-import.php` | แหล่งข้อมูล 6 แบบ, อัปโหลด CSV พร้อมตรวจสอบคอลัมน์และแสดงตัวอย่าง, Manual Entry, ไฟล์ตัวอย่าง, ประวัติพร้อมสถานะ Imported/Pending/Failed |
| Settings | `pages/settings.php` | ข้อมูลโรงเรียน, สถานะ AI, การแสดงผล, Reset Demo Data |
| Role Selector | `pages/role.php` | สาธิตมุมมองครู บุคลากร นักเรียน ผู้ปกครอง (เมนูโปรไฟล์มุมขวาบน) |

ส่วนประกอบที่ใช้ร่วมกันทุกหน้า: Sidebar (ย่อได้ / Drawer บนมือถือ), ตัวเลือกช่วงเวลา (3/6/12 เดือน), Notification Dropdown, ป้าย DEMO DATA, Reset Demo Data และปุ่มกลับหน้า TB-Solution

ฟีเจอร์ที่ยังไม่เปิดใช้งาน (API, Database, การส่งรายงานอัตโนมัติ, Login) จะแสดงป้าย **Coming Soon** และแจ้งข้อความเมื่อกด

---

## 3. Mock Data และความสอดคล้องของตัวเลข

`lib/SchoolData.php` สร้างข้อมูลด้วย seed คงที่ (ได้ผลเหมือนเดิมทุกครั้ง):

- นักเรียน 1,200 คน · 36 ห้อง (อ.1–อ.3 ระดับละ 2 ห้อง, ป.1–ป.6 ระดับละ 3 ห้อง, ม.1–ม.6 ระดับละ 2 ห้อง)
- ครู 52 คน + บุคลากร 13 คน = 65 คน
- ข้อมูลรายเดือน 24 เดือน (พ.ย. 67 – ต.ค. 69) และผลการเรียน 3 ภาคเรียน (1/2568, 2/2568, 1/2569)
- ชื่อทั้งหมดเป็นชื่อสมมติที่สุ่มจากรายการชื่อต้นและนามสกุล

ข้อมูลถูกสร้างที่ **ระดับนักเรียนและครูรายคน** ก่อน แล้วค่ารวมทุกตัว (ห้อง ระดับชั้น อนุกรมรายเดือน KPI Insight และคำตอบในแชต) คำนวณจากชุดเดียวกันใน `lib/SchoolAnalytics.php` ตัวเลขทุกหน้าจึงตรงกัน
ตัวอย่าง: นักเรียนที่ต้องติดตาม 128 คน (High 60 + Medium 68) ตรงกันทั้งใน KPI, Donut, หน้า Risk, รายงาน และคำตอบในแชต

เรื่องราวที่ฝังไว้ในข้อมูลเพื่อการสาธิต:
- การเข้าเรียนชั้น ม.2 ลดลงต่อเนื่อง (ห้อง ม.2/2 ต่ำสุด)
- GPA ห้อง ม.3/2 ลดลงมากที่สุด
- คณิตศาสตร์ ม.2 คะแนนลดลง และวิชาคณิตศาสตร์มีผู้ไม่ผ่านมากที่สุด
- ครู 4 คนมีภาระงานสูงกว่าค่าเฉลี่ยมากกว่า 20%
- ครู 2 คนมีข้อมูลประเมินไม่ครบถ้วน

---

## 4. AI: การจำลองและการเชื่อมต่อจริง

- ค่าเริ่มต้นใช้ `MockAiProvider` (Rule-based) ตรวจประเภทคำถามจากคีย์เวิร์ด แล้วตอบด้วยตัวเลขจาก `SchoolAnalytics`
  ทุกหน้าระบุชัดเจนว่าเป็น **Rule-based Simulation** ไม่ได้อ้างว่า AI วิเคราะห์ข้อมูลจริง
- โครงสร้างสำหรับต่อยอด: `lib/Ai/AiProvider.php` (interface) → `MockAiProvider` / `OpenAiProvider` → เลือกใช้ผ่าน `AiAssistant`
- เมื่อตั้งค่า `AI_PROVIDER=openai` และใส่ `OPENAI_API_KEY` ระบบจะส่ง **เฉพาะข้อมูลสรุประดับโรงเรียน/ห้องเรียน** (`chatContext()`) ให้โมเดล ไม่ส่งข้อมูลนักเรียนรายบุคคล
  หากเรียก API ไม่สำเร็จ ระบบจะกลับไปใช้โหมดจำลองให้อัตโนมัติ
- Endpoint: `POST api/chat.php` รับ `{ "message": "...", "history": [...] }`

หลักการด้านจริยธรรมที่ระบบยึดไว้:
- **การประเมินครู** วิเคราะห์จาก 9 มิติ โดยผลสัมฤทธิ์ของนักเรียนมีน้ำหนักเพียง 12% และทุกผลแสดงแหล่งข้อมูล ความครบถ้วน และข้อจำกัดกำกับไว้ AI ทำหน้าที่สนับสนุนการประเมินเท่านั้น ไม่ได้ตัดสินแทนคน
- **การวิเคราะห์ความเสี่ยงนักเรียน** ใช้ถ้อยคำเชิงช่วยเหลือ (ต้องดูแลใกล้ชิด / ควรติดตาม / ปกติ) และมีโหมดปกปิดชื่อ รายงานความเสี่ยงแสดงเพียงรหัสนักเรียน

---

## 5. Demo Mode

- ป้าย DEMO DATA แสดงที่ Topbar, Sidebar, Footer และในรายงาน
- ระบบไม่บันทึกข้อมูลใดลงฝั่ง server สิ่งที่ผู้ชมทดลอง (ประวัติแชต, รายการนำเข้า, การแจ้งเตือนที่อ่านแล้ว, การตั้งค่า) จะเก็บใน `localStorage` ของเบราว์เซอร์นั้นเท่านั้น
- ปุ่ม **Reset Demo Data** ล้างข้อมูลที่ทดลองไว้ทั้งหมดและกลับสู่สถานะตั้งต้น

---

## 6. โครงสร้างไฟล์

```
demos/ai-school/
├── index.php            # router + layout (?page=...)
├── about.php            # หน้ารายละเอียดสินค้า
├── report.php           # รายงาน A4 สำหรับพิมพ์/บันทึก PDF
├── bootstrap.php        # โหลด helper ของ TB-Solution + lib
├── api/                 # chat.php (AI), sample.php (ไฟล์ตัวอย่าง CSV)
├── components/          # head, sidebar, topbar, foot, kpi-card, insight-card, page-head
├── config/              # app.php (โรงเรียน เมนู บทบาท AI), reports.php
├── data/schema.sql      # โครงสร้างฐานข้อมูล MySQL/MariaDB สำหรับเฟสถัดไป
├── lib/                 # SchoolData, SchoolAnalytics, helpers, Ai/*
├── pages/               # 12 หน้า
└── assets/              # css/app.css, js/{core,charts,pages,chat,import}.js, images/preview.svg
```

การเชื่อมกับ Landing Page:
- `config/demos.php` (root) — เพิ่มรายการ `ai-school` โดยตั้งเป็น `featured` และมีป้าย `AI Powered`
- `config/site.php` — ปุ่ม "เข้าสู่ระบบ Demo" ชี้ไปที่ `demos/ai-school/`

---

## 7. ต่อยอดเฟสถัดไป

1. **ฐานข้อมูล**: นำเข้า `data/schema.sql` แล้วเขียน repository ที่คืนข้อมูลในรูปแบบเดียวกับ `SchoolData::get()`
2. **Login และสิทธิ์**: ใช้ตาราง `users.role` ร่วมกับ `config/app.php` › `roles`
3. **การนำเข้าไฟล์จริง**: ย้ายการตรวจสอบใน `assets/js/import.js` ไปทำที่ฝั่ง server และบันทึกลงตาราง `data_imports`
4. **AI จริง**: ตั้งค่า `AI_PROVIDER=openai` หรือเพิ่ม provider ใหม่ที่ implement `AiProvider`
