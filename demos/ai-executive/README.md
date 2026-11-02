# AI Executive Dashboard (Demo)

แดชบอร์ดสำหรับผู้บริหารโรงเรียน ใช้ติดตาม วิเคราะห์ และประเมินภาพรวมการดำเนินงานของโรงเรียน พร้อมการวิเคราะห์และข้อเสนอแนะเชิงกลยุทธ์แบบ **AI Simulation**
ระบบนี้เป็น Demo สำหรับนำเสนอขาย จึงใช้ **Mock Data ทั้งหมด** ไม่ต้องใช้ฐานข้อมูล และไม่ต้อง Login

- URL: `/demos/ai-executive/`
- เปิด Scenario ที่ต้องการได้ทันทีด้วยลิงก์: `/demos/ai-executive/?scenario=at-risk#ai`
- เป็นโมดูลแยกอิสระ ไม่ได้ใช้ไฟล์ร่วมกับ demo อื่นใน repository

## 1. วิธีเปิดใช้งาน

```bash
cd tb-demo                     # root ของเว็บไซต์ TB-Solution
php -S localhost:8000          # ต้องใช้ PHP 8.1 ขึ้นไป
```

จากนั้นเปิด http://localhost:8000/demos/ai-executive/
หรือเข้าจากหน้าแรก โดยกดปุ่ม **"ใหม่ · ทดลอง AI Executive Dashboard"** ในส่วน Hero หรือการ์ดในส่วน Demo Systems

ถ้าต้องการดู log สำหรับนักพัฒนา (ทั้งฝั่ง PHP และ Console ของ browser):

```bash
APP_DEBUG=1 php -S localhost:8000
```

ใช้กับ Apache หรือ Docker ได้เลย ไม่ต้องตั้งค่าเพิ่ม (ดู `Dockerfile` ที่ root ของ repository)

## 2. Tech Stack

- **ฝั่ง server:** Native PHP ไม่ใช้ Framework และไม่ใช้ Database ทุกชนิด
- **ฝั่ง browser:** HTML5, CSS3 (Custom CSS พร้อม Dark Mode) และ JavaScript ES6+
- **ไลบรารีภายนอก:** Chart.js 4.4.1, Lucide Icons 0.460.0, Google Fonts Prompt + Sarabun และ SheetJS 0.18.5 ซึ่งโหลดเฉพาะตอนกด Export Excel

## 3. เมนูและฟีเจอร์

| เมนู | สิ่งที่ทำได้ |
|---|---|
| Overview Dashboard | KPI 6 ใบ (เทียบเดือนก่อน แสดง % และ Trend), AI Daily Insight, กราฟ 6 ชุด, AI Insights 3 อันดับแรก |
| AI School Analytics ⭐ | ภาพรวม, จุดแข็ง/จุดอ่อน, แนวโน้มที่ดีขึ้น, ปัญหาเร่งด่วน, กลุ่มเสี่ยง, ห้องเด่น, การคาดการณ์ 3 เดือน, Insight Cards แยกเป็น Critical / Warning / Opportunity / Recommendation |
| AI Teacher Evaluation ⭐ | สรุปภาพรวมครู, AI Teacher Insights, กราฟรายกลุ่มสาระ, ตารางประเมิน (ค้นหา/กรอง/เรียง/แบ่งหน้า), รายละเอียดรายบุคคล 10 ด้านพร้อม Radar |
| Student Analytics | เลือกระดับชั้น, GPA รายชั้น 3 ภาคเรียน, วิชา, Ranking, พัฒนาการดีขึ้น, ต่ำกว่าเกณฑ์, Progress Tracking รายบุคคล |
| Attendance Analytics | อัตรารายวันและรายเดือน, ขาด/สาย/ขาดต่อเนื่อง, เปรียบเทียบห้อง, รายชื่อที่ต้องติดตาม |
| Classroom Analytics | กราฟเปรียบเทียบ 5 ตัวชี้วัด, การ์ดห้องเรียนเรียงอันดับ, รายละเอียดห้อง |
| Executive Reports | รายงาน 8 หัวข้อ เลือกช่วงเวลา/ภาคเรียน/ปีการศึกษาได้, Print, Export PDF (ผ่าน Print CSS), Export Excel (6 แผ่นงาน) |
| Mock Data Center | Generate / Randomize / Change Scenario / Reset, สรุปจำนวนข้อมูล, ดาวน์โหลด JSON |
| Settings | ธีม (สว่าง/มืด/ตามระบบ), ย่อ Sidebar, จำนวนแถวต่อหน้า, ลดภาพเคลื่อนไหว, ล้างข้อมูล |

ส่วนที่ใช้ร่วมกันทุกหน้า:
- **ตัวกรองกลาง:** ช่วงเวลา, ปีการศึกษา, ภาคเรียน และห้องเรียน/ระดับชั้น
- **ปุ่มบน Topbar:** Generate Mock Data, Dark Mode และการแจ้งเตือนจาก AI
- **อื่น ๆ:** Toast, Loading/Empty/Error State และ Sidebar ที่ย่อได้ (บนมือถือจะเป็น Drawer)

ภาคเรียน 2/2569 ยังไม่มีข้อมูล ถ้าเลือกภาคเรียนนี้ ระบบจะแสดง Empty State ให้ดูเป็นตัวอย่าง

## 4. Mock Data System

ปุ่ม **Generate Mock Data** บน Topbar มีตัวเลือกดังนี้

| Scenario | ลักษณะ |
|---|---|
| Default Dataset | นักเรียน 600 คน ครู 35 คน 18 ห้อง (ป.1–ม.6) |
| Large Dataset | นักเรียน 1,470 คน ครู 84 คน 42 ห้อง |
| Excellent School | การเข้าเรียนสูง ผลการเรียนดี ส่งงานครบ พัฒนาการดีขึ้น |
| Average School | ผลงานระดับปานกลาง มีปัญหาบางห้อง มีนักเรียนต้องติดตาม |
| At-Risk School | การเข้าเรียนลดลง ผลสัมฤทธิ์ต่ำ นักเรียนเสี่ยงหลายกลุ่ม ภาระงานครูไม่สมดุล |
| Reset Data | กลับไปใช้ Default Dataset |

การทำงานของข้อมูล:
- **การสร้างข้อมูล:** `api/mock-data.php?scenario=…&seed=…` สร้างข้อมูลด้วย PHP Array จากไฟล์ใน `data/` ถ้าใช้ scenario และ seed เดิม จะได้ข้อมูลชุดเดิมทุกครั้ง
- **ข้อมูลที่ครอบคลุม:** ข้อมูลการเข้าเรียนย้อนหลัง 6 เดือน (และรายวันของเดือนล่าสุด), ผลการเรียน 3 ภาคเรียน, การส่งงาน, พัฒนาการ, พฤติกรรม, กิจกรรม, ภาระงานครู และการพัฒนาตนเอง
- **การเก็บข้อมูล:** ชุดข้อมูลที่สร้างจะเก็บใน **LocalStorage** (`exd.dataset`) จึงยังอยู่หลัง Refresh ส่วนการ Reset จะลบออกแล้วกลับไปใช้ Default ที่ฝังมากับหน้าเว็บ
- **ความสอดคล้องของตัวเลข:** ทุกตัวเลขในทุกโมดูลคำนวณจากชุดข้อมูลเดียวกันใน `assets/js/ai-analytics.js` เมื่อเปลี่ยน Scenario ทั้ง KPI, กราฟ, ตาราง, AI Insights, การประเมินครู และรายงาน จึงเปลี่ยนพร้อมกันและไม่ขัดแย้งกัน

## 5. AI Simulation

- **ไม่มีการเรียก AI API ภายนอก:** การวิเคราะห์ทั้งหมดเป็น Rule-based บนข้อมูลจำลอง และทุกหน้าที่เกี่ยวข้องมีป้าย **AI Simulation / Demo Analysis** กำกับไว้
- **การคาดการณ์:** ใช้แนวโน้มเชิงเส้นอย่างง่าย และระบุบนหน้าจอว่าไม่ใช่โมเดลพยากรณ์จริง
- **การประเมินครู:** คะแนนเป็นตัวชี้วัดจำลองจาก 10 ด้าน ไม่ใช่การตัดสินคุณภาพครูโดยอัตโนมัติ ต้องพิจารณาบริบทและปัจจัยประกอบเสมอ

## 6. โครงสร้างไฟล์

```
demos/ai-executive/
├── index.php                 # Single-page shell + ฝัง Default Dataset
├── config/config.php         # เมนู, ภาคเรียน, Scenario
├── data/                     # Mock Data Engine (PHP Array)
│   ├── generator.php         #   ประกอบชุดข้อมูล + MockRandom (มี seed)
│   ├── mock-classrooms.php  mock-teachers.php  mock-students.php
│   ├── mock-attendance.php  mock-academic.php  mock-activities.php
│   └── names.php             #   ชื่อสมมติ
├── api/mock-data.php         # JSON API สำหรับ Generate / Randomize
├── includes/                 # helpers, header, sidebar, topbar, filters, footer
├── views/                    # โครง HTML ของ 9 เมนู
└── assets/
    ├── css/app.css           # Design system + Dark Mode + Print
    ├── js/mock-data.js       # LocalStorage store
    ├── js/ai-analytics.js    # KPI, กราฟ, Insights, Teacher Eval, Forecast (pure functions)
    ├── js/charts.js          # Chart.js helpers
    ├── js/dashboard.js       # Router, ตัวกรอง, ตาราง, Modal, Toast, Overview
    ├── js/views.js           # หน้าโมดูลทั้งหมด + Modal รายละเอียด
    └── js/reports.js         # รายงาน + Print/PDF/Excel
```
