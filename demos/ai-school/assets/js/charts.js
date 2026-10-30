/**
 * AI School Intelligence — Chart.js rendering from PHP chart specs.
 *
 * Spec: { type, labels, datasets:[{label, data, color, type?, fill?, dashed?, axis?, colors?}],
 *         options:{suffix, decimals, yMin, yMax, y1Suffix, horizontal, legend, cutout, center, links, stacked},
 *         views:{ key: {label, type?, labels?, datasets?, options?} } }
 */
(function () {
    'use strict';

    var AIS = window.AIS;
    var PALETTE = {
        primary: '#2878E8', deep: '#173B75', sky: '#8DBEF7', yellow: '#F5C84C', green: '#22A06B',
        red: '#E5484D', orange: '#F0A030', gray: '#9AAAC2', mint: '#4FCB94', purple: '#7B7FEA'
    };
    var instances = {};

    function color(name) {
        return PALETTE[name] || name || PALETTE.primary;
    }

    function alpha(hex, a) {
        var h = hex.replace('#', '');
        var r = parseInt(h.substring(0, 2), 16), g = parseInt(h.substring(2, 4), 16), b = parseInt(h.substring(4, 6), 16);
        return 'rgba(' + r + ',' + g + ',' + b + ',' + a + ')';
    }

    function format(value, opts, axis) {
        if (value === null || value === undefined) return '–';
        var decimals = opts.decimals !== undefined ? opts.decimals : (Math.abs(value) < 10 && value % 1 !== 0 ? 2 : 0);
        var suffix = axis === 'y1' ? (opts.y1Suffix || '') : (opts.suffix || '');
        return Number(value).toLocaleString('th-TH', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }) + suffix;
    }

    function setDefaults() {
        if (!window.Chart) return false;
        var C = window.Chart;
        C.defaults.font.family = "'IBM Plex Sans Thai', 'Prompt', system-ui, sans-serif";
        C.defaults.font.size = 12;
        C.defaults.color = '#5B7396';
        C.defaults.borderColor = 'rgba(221, 233, 248, .8)';
        C.defaults.plugins.tooltip.backgroundColor = '#173B75';
        C.defaults.plugins.tooltip.padding = 10;
        C.defaults.plugins.tooltip.cornerRadius = 10;
        C.defaults.plugins.tooltip.titleFont = { weight: '600' };
        C.defaults.plugins.legend.labels.usePointStyle = true;
        C.defaults.plugins.legend.labels.boxWidth = 8;
        C.defaults.plugins.legend.labels.padding = 14;
        C.defaults.maintainAspectRatio = false;
        return true;
    }

    /** Draws the total in the middle of doughnut charts. */
    var centerText = {
        id: 'aisCenter',
        afterDraw: function (chart) {
            var center = chart.config.options.plugins.aisCenter;
            if (!center || !center.value || chart.config.type !== 'doughnut') return;
            var meta = chart.getDatasetMeta(0);
            if (!meta.data[0]) return;
            var x = meta.data[0].x, y = meta.data[0].y;
            var ctx = chart.ctx;
            ctx.save();
            ctx.textAlign = 'center';
            ctx.fillStyle = '#173B75';
            ctx.font = "700 24px 'IBM Plex Sans Thai', sans-serif";
            ctx.fillText(center.value, x, y + 4);
            ctx.fillStyle = '#5B7396';
            ctx.font = "400 12px 'IBM Plex Sans Thai', sans-serif";
            ctx.fillText(center.label || '', x, y + 22);
            ctx.restore();
        }
    };

    function buildDataset(ds, chartType, ctx, area) {
        var base = color(ds.color);
        var type = ds.type || chartType;
        var out = { label: ds.label, data: ds.data };

        if (chartType === 'doughnut') {
            var colors = (ds.colors || []).map(color);
            out.backgroundColor = colors;
            out.borderColor = '#fff';
            out.borderWidth = 3;
            out.hoverOffset = 8;
            return out;
        }

        if (type === 'line' || type === 'radar') {
            out.type = chartType === 'radar' ? undefined : 'line';
            out.borderColor = base;
            out.borderWidth = ds.dashed ? 1.6 : 2.4;
            out.borderDash = ds.dashed ? [6, 5] : undefined;
            out.pointRadius = ds.dashed ? 0 : 3;
            out.pointHoverRadius = 6;
            out.pointBackgroundColor = '#fff';
            out.pointBorderColor = base;
            out.pointBorderWidth = 2;
            out.tension = 0.35;
            out.spanGaps = true;
            if (ds.fill) {
                if (chartType === 'radar') {
                    out.backgroundColor = alpha(base, 0.18);
                } else if (ctx && area) {
                    var grad = ctx.createLinearGradient(0, area.top, 0, area.bottom);
                    grad.addColorStop(0, alpha(base, 0.24));
                    grad.addColorStop(1, alpha(base, 0));
                    out.backgroundColor = grad;
                } else {
                    out.backgroundColor = alpha(base, 0.12);
                }
                out.fill = chartType === 'radar' ? true : 'origin';
            } else {
                out.backgroundColor = base;
                out.fill = false;
            }
            out.order = 0;
        } else {
            out.type = 'bar';
            out.backgroundColor = ds.colors ? ds.colors.map(color) : alpha(base, 0.9);
            out.hoverBackgroundColor = base;
            out.borderRadius = 6;
            out.borderSkipped = false;
            out.maxBarThickness = 28;
            out.order = 1;
        }
        if (ds.axis === 'y1') out.yAxisID = 'y1';
        if (ds.stack) out.stack = ds.stack;
        return out;
    }

    function buildConfig(spec, canvas) {
        var opts = spec.options || {};
        var type = spec.type;
        var horizontal = Boolean(opts.horizontal) && type === 'bar';
        var ctx = canvas.getContext('2d');
        var hasY1 = spec.datasets.some(function (d) { return d.axis === 'y1'; });

        var config = {
            type: type,
            data: { labels: spec.labels, datasets: [] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: type === 'doughnut' || type === 'radar' ? 'nearest' : 'index', intersect: type === 'doughnut' },
                plugins: {
                    legend: { display: opts.legend !== false && (spec.datasets.length > 1 || type === 'doughnut'), position: type === 'doughnut' ? 'bottom' : 'top', align: type === 'doughnut' ? 'center' : 'end' },
                    tooltip: {
                        callbacks: {
                            label: function (item) {
                                var ds = item.dataset;
                                var value = type === 'doughnut' ? item.raw : (horizontal ? item.parsed.x : item.parsed.y);
                                if (type === 'radar') value = item.raw;
                                var text = ' ' + (ds.label || item.label) + ': ' + format(value, opts, ds.yAxisID);
                                if (type === 'doughnut') {
                                    var total = ds.data.reduce(function (a, b) { return a + b; }, 0);
                                    text = ' ' + item.label + ': ' + format(value, opts) + ' (' + (value / total * 100).toFixed(1) + '%)';
                                }
                                return text;
                            }
                        }
                    },
                    aisCenter: opts.center || null
                },
                onClick: function (event, elements) {
                    if (!opts.links || !elements.length) return;
                    var url = opts.links[elements[0].index];
                    AIS.log('Charts', 'onClick', 'navigate', { url: url });
                    if (url) window.location.href = url;
                },
                onHover: function (event, elements) {
                    if (opts.links) event.native.target.style.cursor = elements.length ? 'pointer' : 'default';
                }
            },
            plugins: [centerText]
        };

        if (type === 'doughnut') {
            config.options.cutout = opts.cutout || '64%';
        } else if (type === 'radar') {
            config.options.scales = {
                r: {
                    min: opts.yMin, max: opts.yMax,
                    ticks: { display: false, stepSize: 10 },
                    grid: { color: 'rgba(40,120,232,.12)' },
                    angleLines: { color: 'rgba(40,120,232,.12)' },
                    pointLabels: { font: { size: 11 }, color: '#18345B' }
                }
            };
        } else {
            var valueAxis = {
                min: opts.yMin, max: opts.yMax,
                grid: { color: 'rgba(221,233,248,.8)', drawTicks: false },
                border: { display: false },
                ticks: { padding: 8, callback: function (v) { return format(v, { decimals: opts.decimals && opts.decimals > 1 ? 1 : 0, suffix: opts.suffix && opts.suffix.length <= 2 ? opts.suffix : '' }); } },
                stacked: Boolean(opts.stacked)
            };
            var categoryAxis = { grid: { display: false }, border: { display: false }, ticks: { padding: 6, autoSkip: true, maxRotation: 0 }, stacked: Boolean(opts.stacked) };
            config.options.indexAxis = horizontal ? 'y' : 'x';
            config.options.scales = horizontal ? { x: valueAxis, y: categoryAxis } : { x: categoryAxis, y: valueAxis };
            if (hasY1) {
                config.options.scales.y1 = {
                    position: 'right', min: opts.y1Min, grid: { display: false }, border: { display: false },
                    ticks: { padding: 6, callback: function (v) { return Number(v).toLocaleString('th-TH'); } }
                };
            }
        }

        var area = null;
        config.data.datasets = spec.datasets.map(function (ds) { return buildDataset(ds, type, ctx, area); });
        // Re-create gradients once the chart area is known.
        config.plugins.push({
            id: 'aisGradient',
            beforeDatasetsDraw: function (chart) {
                if (chart.$aisGradient === chart.chartArea.bottom) return;
                chart.$aisGradient = chart.chartArea.bottom;
                spec.datasets.forEach(function (ds, i) {
                    if (ds.fill && type !== 'radar' && type !== 'doughnut' && chart.data.datasets[i]) {
                        chart.data.datasets[i].backgroundColor = buildDataset(ds, type, chart.ctx, chart.chartArea).backgroundColor;
                    }
                });
            }
        });
        return config;
    }

    function create(canvas, spec) {
        AIS.log('Charts', 'create', 'START', { id: canvas.id, type: spec.type });
        if (!window.Chart) {
            canvas.parentNode.innerHTML = '<p class="chart-note">ไม่สามารถแสดงกราฟได้ในขณะนี้ กรุณาลองใหม่อีกครั้ง</p>';
            AIS.logError('Charts', 'create', 'Chart.js not loaded', { id: canvas.id });
            return null;
        }
        if (instances[canvas.id]) instances[canvas.id].destroy();
        try {
            var chart = new window.Chart(canvas, buildConfig(spec, canvas));
            instances[canvas.id] = chart;
            AIS.log('Charts', 'create', 'END', { id: canvas.id });
            return chart;
        } catch (error) {
            AIS.logError('Charts', 'create', error, { id: canvas.id, spec: spec });
            return null;
        }
    }

    /** Merge a view (from spec.views) over the base spec. */
    function viewSpec(spec, key) {
        var base = JSON.parse(JSON.stringify(spec));
        var view = spec.views && spec.views[key];
        if (!view || !view.datasets) return base;
        base.type = view.type || spec.type;
        base.labels = view.labels || spec.labels;
        base.datasets = view.datasets;
        base.options = Object.assign({}, spec.options, view.options || {});
        return base;
    }

    function initAll(root) {
        AIS.log('Charts', 'initAll', 'START');
        setDefaults();
        var count = 0;
        (root || document).querySelectorAll('canvas[data-chart]').forEach(function (canvas) {
            var el = document.querySelector('script[data-chart-spec="' + canvas.id + '"]');
            if (!el) return;
            try {
                var spec = JSON.parse(el.textContent);
                canvas.$spec = spec;
                create(canvas, spec);
                count++;
            } catch (error) {
                AIS.logError('Charts', 'initAll', error, { id: canvas.id });
            }
        });

        document.querySelectorAll('[data-chart-views]').forEach(function (group) {
            var canvas = document.getElementById(group.getAttribute('data-chart-views'));
            group.querySelectorAll('[data-view]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (!canvas || !canvas.$spec) return;
                    group.querySelectorAll('[data-view]').forEach(function (b) { b.classList.toggle('is-active', b === btn); });
                    create(canvas, viewSpec(canvas.$spec, btn.getAttribute('data-view')));
                    AIS.log('Charts', 'switchView', 'view changed', { id: canvas.id, view: btn.getAttribute('data-view') });
                });
            });
        });
        AIS.log('Charts', 'initAll', 'END', { count: count });
    }

    AIS.charts = { create: create, viewSpec: viewSpec, initAll: initAll, instances: instances, setDefaults: setDefaults };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { initAll(); }); else initAll();
})();
