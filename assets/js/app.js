/**
 * TB-Solution — Landing page interactions (Vanilla JS).
 *
 * Dev logs are printed only when window.TB_CONFIG.debug is true
 * (start PHP with APP_DEBUG=1).
 */
(function () {
    'use strict';

    var IS_DEV = Boolean(window.TB_CONFIG && window.TB_CONFIG.debug);
    var REDUCED_MOTION = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var MESSAGES = {
        pending: 'ช่องทางนี้กำลังจะเปิดให้บริการเร็ว ๆ นี้ ขอบคุณที่สนใจครับ',
        demoPending: 'ระบบตัวอย่างนี้กำลังจัดเตรียม และจะเปิดให้ทดลองใช้งานเร็ว ๆ นี้'
    };

    function log(fn, message, data) {
        if (!IS_DEV) return;
        if (data === undefined) {
            console.log('[App][' + fn + '] ' + message);
        } else {
            console.log('[App][' + fn + '] ' + message, data);
        }
    }

    function logError(fn, error, context) {
        console.error('[App][' + fn + '] ERROR', { error: error, context: context });
    }

    /* Navbar: sticky glass state + hamburger menu ------------------------- */
    function initNavbar() {
        log('initNavbar', 'START');

        var navbar = document.getElementById('navbar');
        if (!navbar) {
            log('initNavbar', 'END (navbar not found)');
            return;
        }
        var toggle = navbar.querySelector('.navbar__toggle');
        var menu = document.getElementById('primary-menu');
        var desktopQuery = window.matchMedia('(min-width: 992px)');

        function updateScrolled() {
            navbar.classList.toggle('is-scrolled', window.scrollY > 12);
        }

        function setMenu(open) {
            log('setMenu', 'START', { open: open });
            navbar.classList.toggle('is-open', open);
            document.body.classList.toggle('is-menu-open', open);
            if (toggle) {
                toggle.setAttribute('aria-expanded', String(open));
                toggle.setAttribute('aria-label', open ? 'ปิดเมนู' : 'เปิดเมนู');
            }
            log('setMenu', 'END', { open: open });
        }

        updateScrolled();
        window.addEventListener('scroll', updateScrolled, { passive: true });

        if (toggle) {
            toggle.addEventListener('click', function () {
                setMenu(!navbar.classList.contains('is-open'));
            });
        }

        if (menu) {
            menu.addEventListener('click', function (event) {
                if (event.target.closest('a')) setMenu(false);
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && navbar.classList.contains('is-open')) {
                setMenu(false);
                if (toggle) toggle.focus();
            }
        });

        document.addEventListener('click', function (event) {
            if (navbar.classList.contains('is-open') && !navbar.contains(event.target)) setMenu(false);
        });

        desktopQuery.addEventListener('change', function (event) {
            if (event.matches) setMenu(false);
        });

        log('initNavbar', 'END');
    }

    /* Highlight nav link of the section currently in view ---------------- */
    function initActiveLinks() {
        log('initActiveLinks', 'START');

        var links = document.querySelectorAll('.navbar__link[href^="#"]');
        if (!links.length || !('IntersectionObserver' in window)) {
            log('initActiveLinks', 'END (skipped)', { links: links.length });
            return;
        }

        var map = {};
        links.forEach(function (link) {
            var id = link.getAttribute('href').slice(1);
            var section = document.getElementById(id);
            if (section) map[id] = link;
        });

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                links.forEach(function (link) { link.classList.remove('is-active'); link.removeAttribute('aria-current'); });
                var active = map[entry.target.id];
                if (active) {
                    active.classList.add('is-active');
                    active.setAttribute('aria-current', 'true');
                }
            });
        }, { rootMargin: '-45% 0px -50% 0px' });

        Object.keys(map).forEach(function (id) { observer.observe(document.getElementById(id)); });

        log('initActiveLinks', 'END', { sections: Object.keys(map) });
    }

    /* Reveal-on-scroll animation ------------------------------------------ */
    function initReveal() {
        log('initReveal', 'START');

        var items = document.querySelectorAll('[data-reveal]');
        if (REDUCED_MOTION || !('IntersectionObserver' in window)) {
            items.forEach(function (el) { el.classList.add('is-visible'); });
            log('initReveal', 'END (shown without animation)', { count: items.length });
            return;
        }

        var observer = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                obs.unobserve(entry.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        items.forEach(function (el) { observer.observe(el); });

        log('initReveal', 'END', { count: items.length });
    }

    /* Subtle pointer parallax on hero visual (desktop only) -------------- */
    function initHeroParallax() {
        log('initHeroParallax', 'START');

        var stage = document.querySelector('.hero-visual__stage');
        var finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
        if (!stage || REDUCED_MOTION || !finePointer) {
            log('initHeroParallax', 'END (skipped)', { finePointer: finePointer, reducedMotion: REDUCED_MOTION });
            return;
        }

        var layers = stage.querySelectorAll('[data-depth]');
        var frame = null;

        stage.addEventListener('pointermove', function (event) {
            if (frame) return;
            frame = window.requestAnimationFrame(function () {
                var rect = stage.getBoundingClientRect();
                var x = (event.clientX - rect.left) / rect.width - 0.5;
                var y = (event.clientY - rect.top) / rect.height - 0.5;
                layers.forEach(function (layer) {
                    var depth = Number(layer.getAttribute('data-depth')) || 0;
                    layer.style.translate = (x * depth).toFixed(1) + 'px ' + (y * depth).toFixed(1) + 'px';
                });
                frame = null;
            });
        });

        stage.addEventListener('pointerleave', function () {
            layers.forEach(function (layer) { layer.style.translate = ''; });
        });

        log('initHeroParallax', 'END', { layers: layers.length });
    }

    /* Demo category filter (rendered only when >1 category exists) ------- */
    function initDemoFilter() {
        log('initDemoFilter', 'START');

        var buttons = document.querySelectorAll('.demo-filter [data-filter]');
        var cards = document.querySelectorAll('#demo-grid .demo-card');
        if (!buttons.length) {
            log('initDemoFilter', 'END (no filter)');
            return;
        }

        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                var filter = button.getAttribute('data-filter');
                log('initDemoFilter', 'filter selected', { filter: filter });

                buttons.forEach(function (b) {
                    var active = b === button;
                    b.classList.toggle('is-active', active);
                    b.setAttribute('aria-pressed', String(active));
                });

                cards.forEach(function (card) {
                    var category = card.getAttribute('data-category');
                    var visible = filter === '*' || category === filter || !category;
                    card.classList.toggle('is-hidden', !visible);
                });
            });
        });

        log('initDemoFilter', 'END', { buttons: buttons.length, cards: cards.length });
    }

    /* Links not configured yet show a friendly notice -------------------- */
    function initPendingLinks() {
        log('initPendingLinks', 'START');

        document.addEventListener('click', function (event) {
            var link = event.target.closest('[data-pending]');
            if (!link) return;
            event.preventDefault();
            var isDemo = Boolean(link.closest('.demo-card'));
            log('initPendingLinks', 'pending link clicked', { href: link.getAttribute('href'), isDemo: isDemo });
            showToast(isDemo ? MESSAGES.demoPending : MESSAGES.pending);
        });

        log('initPendingLinks', 'END', { count: document.querySelectorAll('[data-pending]').length });
    }

    /* Toast ---------------------------------------------------------------- */
    var toastTimer = null;

    function showToast(message) {
        log('showToast', 'START', { message: message });

        var toast = document.getElementById('toast');
        if (!toast) {
            log('showToast', 'END (toast element not found)');
            return;
        }

        toast.textContent = message;
        toast.hidden = false;
        window.requestAnimationFrame(function () { toast.classList.add('is-visible'); });

        window.clearTimeout(toastTimer);
        toastTimer = window.setTimeout(function () {
            toast.classList.remove('is-visible');
            window.setTimeout(function () { toast.hidden = true; }, 400);
        }, 3200);

        log('showToast', 'END');
    }

    /* Boot ----------------------------------------------------------------- */
    function init() {
        log('init', 'START');

        [initNavbar, initActiveLinks, initReveal, initHeroParallax, initDemoFilter, initPendingLinks].forEach(function (fn) {
            try {
                fn();
            } catch (error) {
                logError(fn.name, error, {});
            }
        });

        log('init', 'END');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
