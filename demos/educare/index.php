<?php

declare(strict_types=1);

/**
 * EduCare Demo — landing page with role selection (no login).
 */
require __DIR__ . '/includes/bootstrap.php';

educare_log('Page', 'landing', 'START', ['uri' => $_SERVER['REQUEST_URI'] ?? '']);

$brand = educare_config('brand');
$roles = educare_config('roles');
$lastRole = edu_role($_SESSION['educare_role'] ?? null);
$pageTitle = 'EduCare Demo · ' . $brand['tagline'];
$clientConfig = ['debug' => EDU_DEBUG, 'page' => 'landing'];

require EDU_ROOT . '/includes/head.php';
?>
<body class="landing">
<header class="site-header">
    <div class="container site-header__inner">
        <a class="brand" href="./" aria-label="EduCare หน้าแรก">
            <?= edu_logo() ?>
            <span class="brand__text"><strong>EduCare</strong><small>ระบบดูแลนักเรียน เชื่อมโรงเรียน ครู และผู้ปกครอง</small></span>
        </a>
        <nav class="site-header__nav">
            <a class="btn btn-light btn-sm rounded-pill d-none d-sm-inline-flex" href="#roles"><i class="bi bi-play-circle me-1"></i>ทดลองใช้งาน</a>
            <a class="btn btn-outline-primary btn-sm rounded-pill" href="../../"><i class="bi bi-house me-1"></i>หน้าแรก</a>
        </nav>
    </div>
</header>

<main>
    <section class="hero">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="hero__eyebrow"><i class="bi bi-stars"></i> Interactive Demo · ไม่ต้อง Login</span>
                    <h1 class="hero__title">ดูแลทุกการเรียนรู้<br><span>เติบโตทุกช่วงวัย</span></h1>
                    <p class="hero__lead"><?= h($brand['summary']) ?></p>
                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-primary btn-lg rounded-pill px-4" href="#roles"><i class="bi bi-hand-index-thumb me-2"></i>เลือกบทบาทเพื่อทดลอง</a>
<?php if ($lastRole !== null): ?>
                        <a class="btn btn-outline-primary btn-lg rounded-pill px-4" href="app.php?role=<?= h($lastRole) ?>"><i class="bi bi-arrow-repeat me-2"></i>กลับไปที่ <?= h($roles[$lastRole]['title']) ?></a>
<?php endif; ?>
                    </div>
                    <ul class="hero__points">
                        <li><i class="bi bi-check-circle-fill"></i>ข้อมูลเชื่อมโยงกันทุกบทบาท</li>
                        <li><i class="bi bi-check-circle-fill"></i>แจ้งเตือนผู้ปกครองทันที</li>
                        <li><i class="bi bi-check-circle-fill"></i>ใช้ได้ทั้งมือถือและคอมพิวเตอร์</li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <div class="hero__art" aria-hidden="true">
                        <svg viewBox="0 0 520 420" class="w-100">
                            <defs>
                                <linearGradient id="hg1" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#E0F2FE"/><stop offset="1" stop-color="#DBEAFE"/></linearGradient>
                                <linearGradient id="hg2" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#0EA5E9"/><stop offset="1" stop-color="#2563EB"/></linearGradient>
                            </defs>
                            <circle cx="270" cy="210" r="190" fill="url(#hg1)"/>
                            <rect x="120" y="70" width="300" height="210" rx="22" fill="#fff" stroke="#E2E8F0" stroke-width="3"/>
                            <rect x="120" y="70" width="300" height="42" rx="22" fill="url(#hg2)"/>
                            <rect x="120" y="96" width="300" height="16" fill="url(#hg2)"/>
                            <circle cx="148" cy="91" r="7" fill="#fff" opacity=".85"/><rect x="164" y="85" width="90" height="12" rx="6" fill="#fff" opacity=".85"/>
                            <rect x="142" y="130" width="78" height="56" rx="12" fill="#EFF6FF"/><rect x="232" y="130" width="78" height="56" rx="12" fill="#ECFEFF"/><rect x="322" y="130" width="78" height="56" rx="12" fill="#FEF9C3"/>
                            <text x="160" y="166" font-size="22" font-weight="700" fill="#2563EB" font-family="Inter">118</text><text x="250" y="166" font-size="22" font-weight="700" fill="#0EA5E9" font-family="Inter">98%</text><text x="340" y="166" font-size="22" font-weight="700" fill="#CA8A04" font-family="Inter">★ 42</text>
                            <path d="M142 250 C 190 210, 230 245, 270 220 S 350 200, 398 210" fill="none" stroke="#2563EB" stroke-width="5" stroke-linecap="round"/>
                            <g transform="translate(40 230)"><rect width="150" height="120" rx="20" fill="#fff" stroke="#E2E8F0" stroke-width="3"/><circle cx="40" cy="44" r="22" fill="#FDE68A"/><circle cx="40" cy="40" r="10" fill="#F59E0B"/><rect x="72" y="30" width="60" height="10" rx="5" fill="#0F172A"/><rect x="72" y="48" width="44" height="8" rx="4" fill="#94A3B8"/><rect x="20" y="82" width="110" height="20" rx="10" fill="#DCFCE7"/><text x="36" y="97" font-size="12" font-family="Sarabun" font-weight="700" fill="#15803D">✓ มาเรียนแล้ว</text></g>
                            <g transform="translate(350 250)"><rect width="140" height="110" rx="20" fill="#fff" stroke="#E2E8F0" stroke-width="3"/><circle cx="34" cy="34" r="16" fill="#E0F2FE"/><path d="M27 34l5 5 9-10" stroke="#0EA5E9" stroke-width="4" fill="none" stroke-linecap="round"/><rect x="58" y="26" width="64" height="9" rx="4.5" fill="#0F172A"/><rect x="18" y="62" width="104" height="8" rx="4" fill="#CBD5E1"/><rect x="18" y="78" width="80" height="8" rx="4" fill="#CBD5E1"/></g>
                            <g transform="translate(400 40)"><circle r="34" cx="34" cy="34" fill="#FDE68A"/><path d="M34 14l6 13 14 2-10 10 2 14-12-7-12 7 2-14-10-10 14-2z" fill="#F59E0B"/></g>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="roles" id="roles" aria-labelledby="roles-title">
        <div class="container">
            <div class="text-center mb-4 mb-lg-5">
                <span class="section-eyebrow">เลือกบทบาทเพื่อเริ่มทดลอง</span>
                <h2 class="section-title" id="roles-title">ทดลองใช้งาน EduCare ได้ทันที</h2>
                <p class="section-lead">คลิกการ์ดเพื่อเข้าสู่ Dashboard ของบทบาทนั้น · ข้อมูลทุกบทบาทเชื่อมโยงกันจริง และเปลี่ยนบทบาทได้โดยข้อมูลไม่หาย</p>
            </div>
            <div class="row g-4">
<?php foreach ($roles as $key => $role): ?>
                <div class="col-md-4">
                    <a class="role-card role-card--<?= h($role['color']) ?>" href="app.php?role=<?= h($key) ?>" data-role-link="<?= h($key) ?>">
                        <span class="role-card__icon"><i class="bi <?= h($role['icon']) ?>"></i></span>
                        <span class="role-card__en"><?= h($role['en']) ?></span>
                        <h3 class="role-card__title"><?= h($role['title']) ?></h3>
                        <p class="role-card__desc"><?= h($role['desc']) ?></p>
                        <ul class="role-card__list">
<?php foreach (array_slice($role['menu'], 1, 4) as $item): ?>
                            <li><i class="bi <?= h($item['icon']) ?>"></i><?= h($item['label']) ?></li>
<?php endforeach; ?>
                        </ul>
                        <span class="role-card__btn"><?= h($role['cta']) ?><i class="bi bi-arrow-right"></i></span>
                    </a>
                </div>
<?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="flow">
        <div class="container">
            <div class="flow__box">
                <h2 class="section-title section-title--sm"><i class="bi bi-diagram-3 text-primary me-2"></i>ลองดูการเชื่อมโยงข้อมูลแบบเรียลไทม์</h2>
                <ol class="flow__steps">
                    <li><span>1</span><div><strong>ครูเช็คชื่อ “ขาดเรียน”</strong><small>เมนูการเข้าเรียนในบทบาทครู</small></div></li>
                    <li><span>2</span><div><strong>Notification Engine ทำงานทันที</strong><small>จำลองส่ง In-App · Push · LINE</small></div></li>
                    <li><span>3</span><div><strong>ผู้ปกครองเห็นการแจ้งเตือน</strong><small>พร้อมสถานะการส่งแต่ละช่องทาง</small></div></li>
                    <li><span>4</span><div><strong>ผู้บริหารเห็นสถิติเปลี่ยน</strong><small>Dashboard อัปเดตจากข้อมูลชุดเดียวกัน</small></div></li>
                </ol>
                <p class="flow__tip"><i class="bi bi-lightbulb"></i> เปิดบทบาทครูและผู้ปกครองคนละแท็บ แล้วดูข้อมูลอัปเดตข้ามแท็บได้ทันที</p>
                <div class="flow__actions">
                    <span class="text-secondary small" id="demo-status"><i class="bi bi-database"></i> ข้อมูล Demo: ชุดเริ่มต้น</span>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill" data-action="reset-demo"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset Demo Data</button>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require EDU_ROOT . '/includes/brand-footer.php'; ?>
<?php require EDU_ROOT . '/includes/scripts.php'; ?>
</body>
</html>
<?php
educare_log('Page', 'landing', 'END');
