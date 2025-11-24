import Chart from "chart.js/auto";
import zoomPlugin from "chartjs-plugin-zoom";
import { DateTime } from "luxon";
import "chartjs-adapter-luxon";

Chart.register(zoomPlugin);

function isoToMs(iso) {
    const t = Date.parse(iso);
    return isNaN(t) ? null : t;
}

function initWaterCharts() {
    document.querySelectorAll("[data-chart]").forEach((canvas) => {
        // Avoid initializing the same canvas twice
        if (canvas.dataset._waterInit === "1") return;
        // Skip canvases that are not visible (wrapped in collapsed container)
        const isVisible = !!(canvas.offsetWidth || canvas.offsetHeight || canvas.getClientRects().length);
        if (!isVisible) return;
        try {
            const payload = JSON.parse(canvas.getAttribute("data-chart"));
            if (!payload || !payload.labels || payload.labels.length === 0)
                return;

            // Build datasets for ALL series and control visibility with the `hidden` flag.
            // This keeps dataset indexes stable for client-side toggles (legend chips)
            const seriesNames = Object.keys(payload.series || {});
            const visible = payload.visible || seriesNames;

            const datasets = seriesNames.map((name) => ({
                label: name,
                data: payload.series[name].map((v, i) => ({ x: payload.labels[i], y: v })),
                borderColor: payload.colors[name] || "#888",
                backgroundColor: "rgba(0,0,0,0)",
                pointRadius: 0,
                tension: 0.2,
                // initial visibility derived from payload.visible
                hidden: !visible.includes(name),
            }));

            const id = canvas.id || "";
                if (id.endsWith("-main")) {
                const ctx = canvas.getContext("2d");
                const mainChart = new Chart(ctx, {
                    type: "line",
                    data: { datasets },
                    options: {
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                type: "time",
                                time: { tooltipFormat: "MMM d HH:mm" },
                                    ticks: { autoSkip: true, maxTicksLimit: 6, maxRotation: 0, sampleSize: 150 },
                            },
                            y: { beginAtZero: false },
                        },
                        plugins: {
                                // hide Chart.js built-in legend; use Blade legend for controls
                                legend: { display: false, position: "top" },
                            zoom: {
                                pan: { enabled: true, mode: "x" },
                                zoom: {
                                    wheel: { enabled: true },
                                    pinch: { enabled: true },
                                    mode: "x",
                                },
                            },
                        },
                        interaction: { intersect: false, mode: "nearest" },
                    },
                });

                window._waterChartMain = mainChart;
                    // update overlay/chips to reflect initial visibility
                    try { updateEmptyOverlay(); } catch (e) {}
                // Ensure Chart.js measures actual canvas size after layout
                requestAnimationFrame(() => {
                    try {
                        mainChart.resize();
                        mainChart.update();
                            try { updateEmptyOverlay(); } catch (e) {}
                    } catch (e) {}
                });
                canvas.dataset._waterInit = "1";
            } else if (id.endsWith("-mini")) {
                const ctx = canvas.getContext("2d");
                const miniDatasets = seriesNames.map((name) => ({
                    label: name,
                    data: payload.series[name].map((v, i) => ({ x: payload.labels[i], y: v })),
                    borderColor: payload.colors[name] || "#aaa",
                    backgroundColor: "rgba(0,0,0,0)",
                    pointRadius: 0,
                    borderWidth: 1.2,
                    tension: 0.2,
                    hidden: !visible.includes(name),
                }));

                const miniChart = new Chart(ctx, {
                    type: "line",
                    data: { datasets: miniDatasets },
                    options: {
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                type: "time",
                                display: true,
                                time: {
                                    tooltipFormat: "HH:mm",
                                    displayFormats: { hour: "HH:mm", minute: "HH:mm" },
                                },
                                ticks: {
                                    autoSkip: true,
                                    maxTicksLimit: 6,
                                    sampleSize: 150,
                                    font: { size: 10 },
                                    callback: function(value, index, ticks) {
                                        try {
                                            const total = (ticks && ticks.length) || 1;
                                            const step = Math.max(1, Math.ceil(total / 5));
                                            const ms = (typeof value === 'number') ? value : Date.parse(String(value));
                                            return (index % step === 0) ? DateTime.fromMillis(ms).toFormat('HH:mm') : '';
                                        } catch (e) { return ''; }
                                    }
                                }
                            },
                            y: { display: false },
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: false },
                        },
                        interaction: { mode: "nearest" },
                    },
                });

                canvas.addEventListener("click", (ev) => {
                    const main = window._waterChartMain;
                    if (!main) return;
                    const points = miniChart.getElementsAtEventForMode(
                        ev,
                        "nearest",
                        { intersect: false },
                        true
                    );
                    if (!points.length) return;
                    const idx = points[0].index;
                    const iso = payload.labels[idx];
                    const center = isoToMs(iso);
                    if (!center) return;

                    const first = isoToMs(payload.labels[0]);
                    const last = isoToMs(
                        payload.labels[payload.labels.length - 1]
                    );
                    const total = Math.max(1, last - first);
                    const windowMs = Math.max(
                        60 * 60 * 1000,
                        Math.round(total * 0.25)
                    );

                    const min = new Date(center - windowMs / 2).toISOString();
                    const max = new Date(center + windowMs / 2).toISOString();

                    main.options.scales.x.min = min;
                    main.options.scales.x.max = max;
                    main.update();
                });

                window._waterChartMini = miniChart;
                requestAnimationFrame(() => {
                    try {
                        miniChart.resize();
                        miniChart.update();
                        try { updateEmptyOverlay(); } catch (e) {}
                    } catch (e) {}
                });
                canvas.dataset._waterInit = "1";
            }
        } catch (e) {
            console.warn("water-quality init error", e);
        }
    });
}

// Retry helper: try initializing until canvases are found or attempts exhausted
function tryInitWithRetries(maxAttempts = 12, delay = 200) {
    let attempts = 0;
    const tick = () => {
        attempts++;
        const canvases = document.querySelectorAll("[data-chart]");
        if (canvases.length) {
            initWaterCharts();
            return;
        }
        if (attempts < maxAttempts) {
            setTimeout(tick, delay);
        } else {
            // If we still haven't found canvases, set up a MutationObserver as a last-resort
            // This handles cases where Livewire or other async rendering inserts the canvases later.
            console.warn(
                "water-quality: no chart canvases found after retries — attaching observer"
            );
            const observer = new MutationObserver((mutations, obs) => {
                for (const m of mutations) {
                    if (m.addedNodes && m.addedNodes.length) {
                        for (const node of m.addedNodes) {
                            if (node.nodeType === 1) {
                                if (
                                    node.matches &&
                                    node.matches("[data-chart]")
                                ) {
                                    initWaterCharts();
                                    obs.disconnect();
                                    return;
                                }
                                // also check descendants
                                if (
                                    node.querySelector &&
                                    node.querySelector("[data-chart]")
                                ) {
                                    initWaterCharts();
                                    obs.disconnect();
                                    return;
                                }
                            }
                        }
                    }
                }
            });
            observer.observe(document.documentElement || document.body, {
                childList: true,
                subtree: true,
            });
        }
    };
    tick();
}

// Initialize on DOM ready (fallback)
document.addEventListener("DOMContentLoaded", () => tryInitWithRetries());

// Also initialize after Livewire first load
document.addEventListener("livewire:load", () => tryInitWithRetries());

// Also initialize when Livewire notifies that the chart data/component mounted
document.addEventListener('waterchart-mounted', () => {
    tryInitWithRetries(8, 150);
});

// Some Livewire versions emit events via the Livewire JS bridge. Listen for that as well.
if (typeof window !== 'undefined') {
    try {
        if (window.livewire && typeof window.livewire.on === 'function') {
            window.livewire.on('waterchart-mounted', () => tryInitWithRetries(8, 150));
        } else if (window.Livewire && typeof window.Livewire.on === 'function') {
            window.Livewire.on('waterchart-mounted', () => tryInitWithRetries(8, 150));
        }
    } catch (e) {
        // Swallow — the DOM listener above handles the generic case.
        console.debug('Livewire event listener setup failed', e);
    }
}

// Re-run init after Livewire updates
if (window.Livewire && typeof window.Livewire.hook === "function") {
    Livewire.hook("message.processed", () => {
        tryInitWithRetries(6, 150);
    });
}

// Also re-attach the mini toggle after Livewire updates so event handlers are present
try {
    if (window.Livewire && typeof window.Livewire.hook === "function") {
        Livewire.hook("message.processed", () => {
            try { setupMiniToggle(); } catch (e) {}
        });
    } else if (window.livewire && typeof window.livewire.on === 'function') {
        window.livewire.on('message.processed', () => {
            try { setupMiniToggle(); } catch (e) {}
        });
    }
} catch (e) {}

// Toggle a dataset's visibility by its label on a Chart.js instance
function toggleDatasetByLabel(chart, label) {
    if (!chart || !chart.data || !chart.data.datasets) return;
    const idx = chart.data.datasets.findIndex((d) => d.label === label);
    if (idx === -1) return;

    // Prefer built-in helpers if available
    try {
        const currentlyVisible = chart.isDatasetVisible ? chart.isDatasetVisible(idx) : !chart.getDatasetMeta(idx).hidden;
        if (chart.setDatasetVisibility) {
            chart.setDatasetVisibility(idx, !currentlyVisible);
        } else {
            const meta = chart.getDatasetMeta(idx);
            meta.hidden = !!meta.hidden ? false : true;
        }
        chart.update();
    } catch (e) {
        console.warn('toggleDatasetByLabel error', e);
    }
}

// Toggle on both main and mini charts (if present), then notify Livewire
function toggleBothCharts(label) {
    try {
        if (window._waterChartMain) toggleDatasetByLabel(window._waterChartMain, label);
        if (window._waterChartMini) toggleDatasetByLabel(window._waterChartMini, label);
        // Update legend chip appearance
        updateLegendChipState(label);
        // Update empty overlay if user hid/shown all series
        try { updateEmptyOverlay(); } catch (e) {}
        try {
            if (window.livewire && typeof window.livewire.emit === 'function') {
                window.livewire.emit('toggle-chart-series', label);
            } else if (window.Livewire && typeof window.Livewire.emit === 'function') {
                window.Livewire.emit('toggle-chart-series', label);
            }
        } catch (e) {}
    } catch (e) {
        console.warn('toggleBothCharts error', e);
    }
}

// Called from Blade legend chips (onclick)
window.onLegendChipClick = function (name) {
    toggleBothCharts(name);
};

// Simple helper to invert chip styles (expects chips to have `data-series` attribute)
function updateLegendChipState(name) {
    try {
        const chip = document.querySelector(`[data-series="${name}"]`);
        if (!chip) return;
        chip.classList.toggle('opacity-40');
        chip.classList.toggle('line-through');
    } catch (e) {
        // noop
    }
}

// Show/hide the overlay when no series are visible
function updateEmptyOverlay() {
    const main = window._waterChartMain;
    const overlay = document.getElementById('chart-empty-overlay');
    if (!overlay) return;
    if (!main || !main.data || !main.data.datasets) {
        // If chart not ready, hide overlay
        overlay.classList.add('hidden');
        return;
    }
    const anyVisible = main.data.datasets.some((ds, idx) => {
        try { return !main.getDatasetMeta(idx).hidden; } catch (e) { return true; }
    });
    overlay.classList.toggle('hidden', anyVisible);
}

// Show all series helper
window._showAllSeries = function() {
    const main = window._waterChartMain;
    const mini = window._waterChartMini;
    if (main && main.data && main.data.datasets) {
        main.data.datasets.forEach((_, idx) => {
            if (main.setDatasetVisibility) main.setDatasetVisibility(idx, true);
            else main.getDatasetMeta(idx).hidden = false;
        });
        main.update();
    }
    if (mini && mini.data && mini.data.datasets) {
        mini.data.datasets.forEach((_, idx) => {
            if (mini.setDatasetVisibility) mini.setDatasetVisibility(idx, true);
            else mini.getDatasetMeta(idx).hidden = false;
        });
        mini.update();
    }
    document.querySelectorAll('[data-series]').forEach(chip => chip.classList.remove('opacity-40','line-through'));
    updateEmptyOverlay();
        try {
            if (window.livewire && typeof window.livewire.emit === 'function') {
                window.livewire.emit('set-series-visibility','all');
            } else if (window.Livewire && typeof window.Livewire.emit === 'function') {
                window.Livewire.emit('set-series-visibility','all');
            }
        } catch (e) {}
};

// Reset zoom helper
window._resetZoom = function() {
    const main = window._waterChartMain;
    if (!main) return;
    try {
        delete main.options.scales.x.min;
        delete main.options.scales.x.max;
        main.update();
    } catch (e) {}
};

// Mini-chart collapse/expand toggle handler
function setupMiniToggle() {
    const btn = document.getElementById('waterChart-mini-toggle');
    const wrap = document.getElementById('waterChart-mini-wrap');
    if (!btn || !wrap) return;

    // Read persisted collapsed state (string '1' = collapsed)
    const persisted = localStorage.getItem('waterChartMiniCollapsed');
    const isCollapsed = persisted === '1';

    if (isCollapsed) {
        wrap.classList.add('hidden');
    } else {
        wrap.classList.remove('hidden');
    }

    // Initialize aria-expanded and chevron rotation
    const expanded = !wrap.classList.contains('hidden');
    btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    // also set aria-pressed and update visual active state
    try { setTimelineActive(btn, expanded); } catch (e) {}
    const chevron = document.getElementById('waterChart-mini-toggle-chevron');
    if (chevron) {
        if (expanded) chevron.classList.remove('rotate-180'); else chevron.classList.add('rotate-180');
    }

    btn.addEventListener('click', () => {
        const nowHidden = wrap.classList.contains('hidden');
        if (nowHidden) {
            // Show it
            wrap.classList.remove('hidden');
            btn.setAttribute('aria-expanded', 'true');
            if (chevron) chevron.classList.remove('rotate-180');
            // Persist
            try { localStorage.setItem('waterChartMiniCollapsed', '0'); } catch (e) {}
            // Ensure mini-chart initializes (lazy init) and reflow main chart
            tryInitWithRetries(6, 150);
            requestAnimationFrame(() => {
                try { window._waterChartMain?.resize(); } catch (e) {}
            });
        } else {
            // Hide it
            wrap.classList.add('hidden');
            btn.setAttribute('aria-expanded', 'false');
            if (chevron) chevron.classList.add('rotate-180');
            try { localStorage.setItem('waterChartMiniCollapsed', '1'); } catch (e) {}
        }
    });
}

// Wire the mini-toggle during boot
document.addEventListener('DOMContentLoaded', setupMiniToggle);
document.addEventListener('livewire:load', setupMiniToggle);

// Robust delegated handlers for the Timeline toggle so it works after Livewire re-renders
function _toggleMiniFromButton(btn) {
    const wrap = document.getElementById('waterChart-mini-wrap');
    if (!wrap || !btn) return;
    const chevron = document.getElementById('waterChart-mini-toggle-chevron');
    const nowHidden = wrap.classList.contains('hidden');
    if (nowHidden) {
        wrap.classList.remove('hidden');
        btn.setAttribute('aria-expanded', 'true');
        if (chevron) chevron.classList.remove('rotate-180');
        try { localStorage.setItem('waterChartMiniCollapsed', '0'); } catch (e) {}
        tryInitWithRetries(6, 150);
        requestAnimationFrame(() => { try { window._waterChartMain?.resize(); } catch (e) {} });
        // reflect active state on the button
        try { setTimelineActive(btn, true); } catch (e) {}
    } else {
        wrap.classList.add('hidden');
        btn.setAttribute('aria-expanded', 'false');
        if (chevron) chevron.classList.add('rotate-180');
        try { localStorage.setItem('waterChartMiniCollapsed', '1'); } catch (e) {}
        try { setTimelineActive(btn, false); } catch (e) {}
    }
}

// Update the Timeline toggle's visual/aria active state
function setTimelineActive(btn, isActive) {
    if (!btn) return;
    // update ARIA pressed
    btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    // active classes (accent)
    const add = ['bg-cvsu','text-white','shadow-sm'];
    const remove = ['bg-white/90','text-slate-700','dark:bg-slate-800','dark:text-slate-200','border','border-slate-200','dark:border-slate-700'];
    if (isActive) {
        remove.forEach(c => btn.classList.remove(c));
        add.forEach(c => btn.classList.add(c));
    } else {
        add.forEach(c => btn.classList.remove(c));
        remove.forEach(c => btn.classList.add(c));
    }
}

// Click/pointer delegation — cover cases where click lands on SVG child, or Livewire has re-rendered the button
['click','pointerdown'].forEach((evtName) => {
    document.addEventListener(evtName, (e) => {
        try {
            const target = e.target;
            // Use both id match and a permissive attribute selector in case structure changes
            const btn = (target.closest && target.closest('#waterChart-mini-toggle')) || (target.closest && target.closest('[data-mini-toggle]'));
            if (!btn) return;
            // If this is pointerdown, don't prevent default; we handle toggle on either event
            _toggleMiniFromButton(btn);
        } catch (err) {
            // non-fatal
        }
    }, { passive: true });
});

// Keyboard accessibility: toggle on Enter/Space when button is focused
document.addEventListener('keydown', (e) => {
    try {
        if (!e.target) return;
        const btn = e.target.closest && e.target.closest('#waterChart-mini-toggle');
        if (!btn) return;
        if (e.key === 'Enter' || e.key === ' ' || e.code === 'Space') {
            e.preventDefault();
            _toggleMiniFromButton(btn);
        }
    } catch (err) {}
});

export {};
