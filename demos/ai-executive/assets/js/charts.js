/**
 * AI Executive Dashboard — Chart.js helpers (theme-aware, re-render safe).
 */
(function () {
    'use strict';

    var instances = {};
    var options = { reducedMotion: false };
    var PALETTE = {
        primary: '#2563EB', secondary: '#1E40AF', sky: '#60A5FA', violet: '#7C3AED', lavender: '#A78BFA',
        green: '#16A34A', amber: '#F59E0B', red: '#DC2626', slate: '#94A3B8', teal: '#0D9488'
    };

    function css(name) {
        return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    }

    function color(name) {
        return PALETTE[name] || name;
    }

    function alpha(hex, a) {
        var h = color(hex).replace('#', '');
        return 'rgba(' + parseInt(h.substr(0, 2), 16) + ',' + parseInt(h.substr(2, 2), 16) + ',' + parseInt(h.substr(4, 2), 16) + ',' + a + ')';
    }

    function applyTheme() {
        if (!window.Chart) return;
        var C = window.Chart;
        C.defaults.font.family = "'Prompt', 'Sarabun', system-ui, sans-serif";
        C.defaults.font.size = 12;
        C.defaults.color = css('--text-muted') || '#64748B';
        C.defaults.borderColor = css('--grid') || 'rgba(148,163,184,.18)';
        C.defaults.maintainAspectRatio = false;
        // Mutate (not replace) the defaults object — it carries Chart.js internals.
        C.defaults.animation.duration = options.reducedMotion ? 0 : 650;
        C.defaults.animation.easing = 'easeOutQuart';
        C.defaults.plugins.legend.labels.usePointStyle = true;
        C.defaults.plugins.legend.labels.boxWidth = 8;
        C.defaults.plugins.legend.labels.padding = 14;
        C.defaults.plugins.tooltip.backgroundColor = css('--tooltip') || '#0F172A';
        C.defaults.plugins.tooltip.padding = 10;
        C.defaults.plugins.tooltip.cornerRadius = 10;
        C.defaults.plugins.tooltip.titleFont = { weight: '600' };
    }

    function gradient(ctx, area, base) {
        var g = ctx.createLinearGradient(0, area.top, 0, area.bottom);
        g.addColorStop(0, alpha(base, 0.28));
        g.addColorStop(1, alpha(base, 0));
        return g;
    }

    /** Line dataset with a gradient fill that follows the chart area. */
    function line(label, data, name, extra) {
        var base = color(name);
        return Object.assign({
            type: 'line', label: label, data: data, borderColor: base, borderWidth: 2.4, tension: 0.35,
            pointRadius: 3, pointHoverRadius: 6, pointBackgroundColor: css('--surface') || '#fff', pointBorderColor: base, pointBorderWidth: 2,
            fill: true, spanGaps: true,
            backgroundColor: function (ctx) {
                var chart = ctx.chart;
                return chart.chartArea ? gradient(chart.ctx, chart.chartArea, base) : alpha(base, 0.15);
            }
        }, extra || {});
    }

    function dashed(label, data, name) {
        return { type: 'line', label: label, data: data, borderColor: color(name), borderWidth: 1.6, borderDash: [6, 5], pointRadius: 0, fill: false, tension: 0 };
    }

    function bar(label, data, name, extra) {
        var c = Array.isArray(name) ? name.map(color) : color(name);
        return Object.assign({ type: 'bar', label: label, data: data, backgroundColor: c, hoverBackgroundColor: c, borderRadius: 6, borderSkipped: false, maxBarThickness: 30 }, extra || {});
    }

    function axes(opts) {
        opts = opts || {};
        var value = { beginAtZero: opts.zero !== false && opts.min === undefined, min: opts.min, max: opts.max, grid: { color: css('--grid') }, border: { display: false }, ticks: { padding: 6, callback: function (v) { return v + (opts.suffix || ''); } } };
        var cat = { grid: { display: false }, border: { display: false }, ticks: { padding: 6, maxRotation: 0, autoSkip: true } };
        return opts.horizontal ? { x: value, y: cat } : { x: cat, y: value };
    }

    function tooltipSuffix(suffix, decimals) {
        return {
            callbacks: {
                label: function (item) {
                    var v = item.parsed && typeof item.parsed === 'object' ? (item.chart.options.indexAxis === 'y' ? item.parsed.x : item.parsed.y) : item.raw;
                    if (item.chart.config.type === 'doughnut' || item.chart.config.type === 'radar') v = item.raw;
                    var text = Number(v).toLocaleString('th-TH', { maximumFractionDigits: decimals === undefined ? 1 : decimals }) + (suffix || '');
                    if (item.chart.config.type === 'doughnut') {
                        var total = item.dataset.data.reduce(function (a, b) { return a + b; }, 0) || 1;
                        text += ' (' + (item.raw / total * 100).toFixed(1) + '%)';
                    }
                    return ' ' + (item.dataset.label || item.label) + ': ' + text;
                }
            }
        };
    }

    /** Create or replace a chart on a canvas id. Returns the Chart instance or null. */
    function render(id, config) {
        var canvas = document.getElementById(id);
        if (!canvas) return null;
        if (!window.Chart) {
            canvas.parentNode.innerHTML = '<div class="state state--inline"><p>ไม่สามารถโหลดกราฟได้ในขณะนี้ กรุณาลองใหม่อีกครั้ง</p></div>';
            console.error('[Charts][render] ERROR', { error: 'Chart.js not loaded', context: { id: id } });
            return null;
        }
        if (instances[id]) instances[id].destroy();
        try {
            config.options = Object.assign({ responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false } }, config.options || {});
            instances[id] = new window.Chart(canvas, config);
            return instances[id];
        } catch (error) {
            console.error('[Charts][render] ERROR', { error: error, context: { id: id } });
            return null;
        }
    }

    /** Replace a canvas with an empty-state message (and restore it later). */
    function empty(id, message) {
        var canvas = document.getElementById(id);
        if (!canvas) return;
        if (instances[id]) { instances[id].destroy(); delete instances[id]; }
        var box = canvas.parentNode;
        var note = box.querySelector('.state--inline');
        if (!note) {
            note = document.createElement('div');
            note.className = 'state state--inline';
            box.appendChild(note);
        }
        note.innerHTML = '<i data-lucide="inbox"></i><p>' + message + '</p>';
        canvas.hidden = true;
        if (window.lucide) window.lucide.createIcons({ root: note });
    }

    function restore(id) {
        var canvas = document.getElementById(id);
        if (!canvas) return;
        canvas.hidden = false;
        var note = canvas.parentNode.querySelector('.state--inline');
        if (note) note.remove();
    }

    window.EXD_CHARTS = {
        render: render, empty: empty, restore: restore, applyTheme: applyTheme,
        line: line, dashed: dashed, bar: bar, axes: axes, tooltip: tooltipSuffix, color: color, alpha: alpha,
        setReducedMotion: function (on) { options.reducedMotion = Boolean(on); applyTheme(); },
        instances: instances
    };
})();
