<?php

declare(strict_types=1);

$hero = tb_config('content.hero', []);
?>
<section class="hero" id="home" aria-labelledby="hero-title">
    <div class="hero__bg" aria-hidden="true">
        <span class="blob blob--blue"></span>
        <span class="blob blob--yellow"></span>
        <span class="blob blob--sky"></span>
        <span class="hero__grid-pattern"></span>
    </div>

    <div class="container hero__inner">
        <div class="hero__content">
            <p class="eyebrow eyebrow--glass" data-reveal>
                <?= tb_icon('sparkles', 'icon icon--sm') ?><?= e($hero['eyebrow'] ?? '') ?>
            </p>

            <h1 class="hero__title" id="hero-title" data-reveal>
                <span class="hero__line"><?= e($hero['title_line1'] ?? '') ?></span>
                <span class="hero__line"><?= e($hero['title_line2'] ?? '') ?> <span class="hl-ai"><?= e($hero['highlight'] ?? '') ?><?= tb_icon('sparkles', 'hl-ai__spark') ?></span></span>
            </h1>

            <p class="hero__desc" data-reveal><?= e($hero['description'] ?? '') ?></p>

            <div class="hero__actions" data-reveal>
                <a class="btn btn--primary btn--lg" href="<?= e(tb_safe_url($hero['primary']['url'] ?? '#demos')) ?>">
                    <?= tb_icon('layout-grid', 'icon icon--sm') ?><?= e($hero['primary']['label'] ?? '') ?>
                </a>
                <a class="btn btn--ghost btn--lg" href="<?= e(tb_safe_url($hero['secondary']['url'] ?? '#about')) ?>">
                    <?= e($hero['secondary']['label'] ?? '') ?><?= tb_icon('arrow-right', 'icon icon--sm') ?>
                </a>
            </div>

            <ul class="hero__badges" data-reveal>
<?php foreach ($hero['badges'] ?? [] as $badge): ?>
                <li><?= tb_icon('check', 'icon icon--xs') ?><?= e($badge) ?></li>
<?php endforeach; ?>
            </ul>
        </div>

        <div class="hero-visual" role="img" aria-label="ภาพตัวอย่างแดชบอร์ดระบบงานที่มี AI ช่วยวิเคราะห์ข้อมูล รองรับทุกอุปกรณ์">
            <div class="hero-visual__stage" aria-hidden="true">
                <div class="dash" data-depth="10">
                    <div class="dash__bar">
                        <span class="dash__dots"><i></i><i></i><i></i></span>
                        <span class="dash__url"><?= tb_icon('shield', 'icon icon--xs') ?>tb-solution / dashboard</span>
                        <span class="dash__tag">ข้อมูลจำลอง</span>
                    </div>
                    <div class="dash__body">
                        <div class="dash__side">
                            <span class="is-active"><?= tb_icon('layout-grid', 'icon icon--sm') ?></span>
                            <span><?= tb_icon('bar-chart', 'icon icon--sm') ?></span>
                            <span><?= tb_icon('users', 'icon icon--sm') ?></span>
                            <span><?= tb_icon('file-text', 'icon icon--sm') ?></span>
                            <span><?= tb_icon('settings', 'icon icon--sm') ?></span>
                        </div>
                        <div class="dash__main">
                            <div class="dash__kpis">
                                <div class="kpi">
                                    <span class="kpi__label">งานที่เสร็จ</span>
                                    <strong class="kpi__value">1,248</strong>
                                    <span class="kpi__trend">+12.4%</span>
                                </div>
                                <div class="kpi">
                                    <span class="kpi__label">เวลาที่ลดลง</span>
                                    <strong class="kpi__value">32%</strong>
                                    <span class="kpi__trend">+8.1%</span>
                                </div>
                                <div class="kpi kpi--ai">
                                    <span class="kpi__label">AI Score</span>
                                    <strong class="kpi__value">94</strong>
                                    <span class="kpi__trend">ดีมาก</span>
                                </div>
                            </div>

                            <div class="dash__chart">
                                <div class="dash__chart-head">
                                    <span>ภาพรวมประสิทธิภาพ</span>
                                    <span class="dash__legend"><i class="is-blue"></i>ผลงาน <i class="is-yellow"></i>AI คาดการณ์</span>
                                </div>
                                <svg viewBox="0 0 320 110" preserveAspectRatio="none" class="dash__svg">
                                    <defs>
                                        <linearGradient id="dashArea" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0" stop-color="#2878E8" stop-opacity=".28"/>
                                            <stop offset="1" stop-color="#2878E8" stop-opacity="0"/>
                                        </linearGradient>
                                    </defs>
                                    <g class="dash__gridlines"><path d="M0 27.5H320M0 55H320M0 82.5H320"/></g>
                                    <path d="M0 88 C30 84 45 70 70 72 S115 58 140 60 S185 40 210 44 S240 30 260 28 L260 110 L0 110Z" fill="url(#dashArea)"/>
                                    <path class="dash__line" d="M0 88 C30 84 45 70 70 72 S115 58 140 60 S185 40 210 44 S240 30 260 28"/>
                                    <path class="dash__forecast" d="M260 28 C280 24 296 16 320 10"/>
                                    <circle cx="260" cy="28" r="5" class="dash__point"/>
                                </svg>
                            </div>

                            <div class="dash__row">
                                <div class="dash__bars">
                                    <span class="dash__mini-title">งานรายสัปดาห์</span>
                                    <div class="bars"><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
                                </div>
                                <div class="dash__tasks">
                                    <span class="dash__mini-title">งานอัตโนมัติ</span>
                                    <p><b class="is-done"><?= tb_icon('check', 'icon icon--xs') ?></b>สรุปรายงานประจำวัน</p>
                                    <p><b class="is-done"><?= tb_icon('check', 'icon icon--xs') ?></b>แจ้งเตือนงานค้าง</p>
                                    <p><b></b>วิเคราะห์ข้อมูลรายเดือน</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <img class="hero-orb" src="<?= e(asset('assets/images/illustrations/ai-core.svg')) ?>" alt="" width="240" height="240" decoding="async" fetchpriority="high" data-depth="24">

                <div class="float-card float-card--insight" data-depth="18">
                    <span class="float-card__icon is-yellow"><?= tb_icon('sparkles', 'icon icon--sm') ?></span>
                    <span>
                        <strong>AI Insight</strong>
                        <small>พบขั้นตอนที่ลดได้ 3 จุด</small>
                    </span>
                </div>

                <div class="float-card float-card--devices" data-depth="14">
                    <span class="float-card__devices">
                        <?= tb_icon('monitor', 'icon icon--sm') ?><?= tb_icon('tablet', 'icon icon--sm') ?><?= tb_icon('smartphone', 'icon icon--sm') ?>
                    </span>
                    <span>
                        <strong>Responsive</strong>
                        <small>ทุกอุปกรณ์</small>
                    </span>
                </div>

                <div class="float-card float-card--ring" data-depth="20">
                    <svg class="ring" viewBox="0 0 44 44">
                        <circle cx="22" cy="22" r="18" class="ring__track"/>
                        <circle cx="22" cy="22" r="18" class="ring__value"/>
                    </svg>
                    <span>
                        <strong>78%</strong>
                        <small>Automation</small>
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>
