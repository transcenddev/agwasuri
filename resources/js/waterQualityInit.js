// Lightweight initializer to keep water-quality chart append/replay working
// across Livewire partial updates.

(function () {
    // Destroy chart instances before Livewire re-renders
    function destroyChartInstance(chartId) {
        try {
            if (!chartId) return;

            const targetCid =
                (window.__waterQualityKnownChartId &&
                    window.__waterQualityKnownChartId[chartId]) ||
                chartId ||
                "apexcharts" + chartId;

            if (
                window.ApexCharts &&
                typeof window.ApexCharts.getChartByID === "function"
            ) {
                const chartInstance = window.ApexCharts.getChartByID(targetCid);
                if (
                    chartInstance &&
                    typeof chartInstance.destroy === "function"
                ) {
                    chartInstance.destroy();
                    console.debug(
                        "waterQualityInit: destroyed chart instance",
                        targetCid
                    );
                }
            }

            // Clear cached references
            if (
                window.__waterQualityKnownChartId &&
                window.__waterQualityKnownChartId[chartId]
            ) {
                delete window.__waterQualityKnownChartId[chartId];
            }
            if (
                window.__waterQualitySeriesState &&
                window.__waterQualitySeriesState[chartId]
            ) {
                delete window.__waterQualitySeriesState[chartId];
            }

            try {
                localStorage.removeItem("waterQualityKnownChartId:" + chartId);
            } catch (e) {}

            console.debug("waterQualityInit: cleaned up chart", chartId);
        } catch (e) {
            console.error("waterQualityInit: destroy error", e);
        }
    }

    function rediscoverAndReplayRoot(root) {
        try {
            if (!root) return;
            const chartId =
                root.getAttribute && root.getAttribute("data-chart-id");
            if (!chartId) return;

            // Clear any persisted mapping so discovery will run again
            try {
                if (
                    window.__waterQualityKnownChartId &&
                    window.__waterQualityKnownChartId[chartId]
                ) {
                    delete window.__waterQualityKnownChartId[chartId];
                    try {
                        localStorage.removeItem(
                            "waterQualityKnownChartId:" + chartId
                        );
                    } catch (e) {}
                    console.debug(
                        "waterQualityInit: cleared known chart id for",
                        chartId
                    );
                }
            } catch (e) {}

            // Replay any buffered appends for this chart so recent points survive re-render
            try {
                const buf =
                    (window.__waterQualityAppendBuffer &&
                        window.__waterQualityAppendBuffer[chartId]) ||
                    [];
                if (buf && buf.length) {
                    console.debug(
                        "waterQualityInit: replaying",
                        buf.length,
                        "buffered points for",
                        chartId
                    );
                    // Dispatch each as a water-quality:point event; mark as __replay
                    buf.slice().forEach((p) => {
                        try {
                            p.__replay = true;
                            window.dispatchEvent(
                                new CustomEvent("water-quality:point", {
                                    detail: p,
                                })
                            );
                            delete p.__replay;
                        } catch (e) {}
                    });
                }
            } catch (e) {}
        } catch (e) {
            console.debug(
                "waterQualityInit: rediscoverAndReplayRoot failed",
                e
            );
        }
    }

    function initAllRoots(rootContainer) {
        try {
            const container = rootContainer || document;
            const roots = container.querySelectorAll
                ? container.querySelectorAll("[data-chart-id]")
                : [];
            roots.forEach((r) => rediscoverAndReplayRoot(r));
        } catch (e) {}
    }

    // On initial DOM ready, initialize any existing chart roots
    document.addEventListener("DOMContentLoaded", () => initAllRoots(document));

    // CRITICAL: Destroy chart instances BEFORE Livewire message processing
    if (window.Livewire && typeof Livewire.hook === "function") {
        Livewire.hook("message.received", (message, component) => {
            try {
                if (!component || !component.el) return;

                // Find all chart roots and destroy instances before DOM update
                const roots = component.el.querySelectorAll
                    ? component.el.querySelectorAll("[data-chart-id]")
                    : [];

                roots.forEach((root) => {
                    const chartId =
                        root.getAttribute && root.getAttribute("data-chart-id");
                    if (chartId) {
                        destroyChartInstance(chartId);
                    }
                });
            } catch (e) {
                console.debug(
                    "waterQualityInit: message.received cleanup error",
                    e
                );
            }
        });

        // Re-init roots inside the Livewire-updated component after message processed
        Livewire.hook("message.processed", (message, component) => {
            try {
                if (!component || !component.el) return;

                // Small delay to ensure DOM is fully rendered with correct dimensions
                setTimeout(() => {
                    initAllRoots(component.el);
                }, 100);
            } catch (e) {
                console.debug(
                    "waterQualityInit: message.processed init error",
                    e
                );
            }
        });
    } else {
        // Defensive: if Livewire isn't available yet, attempt a global hook later
        console.debug("waterQualityInit: Livewire not present at init time");
     }

    // Expose helper for manual invocation/debugging
    window.waterQualityInit = function (root) {
        rediscoverAndReplayRoot(root);
    };

    // Robust fallback: when a water-quality event arrives but the per-chart
    // append machinery isn't bound (e.g. due to script load order or
    // Livewire partials), request an authoritative snapshot from the server
    // and update visible ApexCharts instances. Rate-limited to avoid storms.
    (function () {
        window.__waterQualityLastSnapshotAt =
            window.__waterQualityLastSnapshotAt || {};
        const SNAP_INTERVAL = 500; // ms per-chart

        async function fetchAndApplySnapshot(root) {
            try {
                const chartId = root.getAttribute("data-chart-id");
                const userId = root.getAttribute("data-user-id");
                if (!chartId || !userId) return;
                const last = window.__waterQualityLastSnapshotAt[chartId] || 0;
                const now = Date.now();
                if (now - last < SNAP_INTERVAL) return; // rate limit
                window.__waterQualityLastSnapshotAt[chartId] = now;

                const limit = 32;
                const res = await fetch(
                    `/api/water-quality/latest?user=${encodeURIComponent(
                        userId
                    )}&limit=${limit}`,
                    { credentials: "same-origin" }
                );
                if (!res.ok) return;
                const json = await res.json();
                const labels = json.labels || [];
                const seriesObj = json.series || {};
                const keys = JSON.parse(
                    root.getAttribute("data-series-keys") || "[]"
                );
                const seriesNames = JSON.parse(
                    root.getAttribute("data-series-names") || "[]"
                );

                const rebuiltSnapshot = keys.map((key, idx) => {
                    const arr = seriesObj[key] || [];
                    return {
                        name: seriesNames[idx] || key,
                        data: arr.map((v, i) => {
                            const parsed = Date.parse(labels[i]);
                            return {
                                x: Number.isFinite(parsed) ? parsed : labels[i],
                                y: v,
                            };
                        }),
                    };
                });

                const targetCid =
                    (window.__waterQualityKnownChartId &&
                        window.__waterQualityKnownChartId[chartId]) ||
                    chartId ||
                    "apexcharts" + chartId;
                try {
                    if (
                        window.ApexCharts &&
                        typeof window.ApexCharts.exec === "function"
                    ) {
                        // Prefer original exec if wrapped
                        const execFn =
                            window.__waterQualityOriginalExec ||
                            window.ApexCharts.exec.bind(window.ApexCharts);
                        execFn(targetCid, "updateSeries", rebuiltSnapshot);
                    }
                } catch (e) {
                    // ignore apply errors
                }
            } catch (e) {
                // ignore overall errors
            }
        }

        // Global listener: when any water-quality event occurs, try to refresh
        // visible charts via authoritative snapshot. This covers cases where
        // inline append handlers weren't bound due to load order or DOM swaps.
        try {
            window.addEventListener("water-quality:point", (evt) => {
                try {
                    const roots = Array.from(
                        document.querySelectorAll("[data-chart-id]")
                    );
                    for (const root of roots) {
                        // fire-and-forget snapshot update per-root
                        fetchAndApplySnapshot(root);
                    }
                } catch (e) {}
            });
        } catch (e) {}
    })();

    // Apply chart annotations from `data-initial.thresholds` when available.
    (function () {
        function buildAnnotationsFromThresholds(thresholds) {
            const ann = [];
            if (!thresholds || typeof thresholds !== "object") return ann;
            const colorMap = {
                dissolved_oxygen_mg_l: "#10b981",
                temperature_c: ["#f59e0b", "#ef4444"],
                ph: ["#f97316", "#10b981"],
                salinity_ppt: ["#06b6d4", "#0ea5a4"],
            };

            Object.keys(thresholds).forEach((k) => {
                const v = thresholds[k];
                if (v === null || typeof v === "undefined") return;
                const cmap = colorMap[k] || "#f97316";
                if (Array.isArray(v)) {
                    v.forEach((vv, i) => {
                        if (vv === null || typeof vv === "undefined") return;
                        const bg = Array.isArray(cmap)
                            ? cmap[i] || cmap[0]
                            : cmap;
                        ann.push({
                            y: vv,
                            borderColor: bg,
                            label: {
                                text: `${k.replace(/_/g, " ")} = ${vv}`,
                                style: { color: "#fff", background: bg },
                            },
                        });
                    });
                } else if (typeof v === "number") {
                    const bg = Array.isArray(cmap) ? cmap[0] : cmap;
                    ann.push({
                        y: v,
                        borderColor: bg,
                        label: {
                            text: `${k.replace(/_/g, " ")} = ${v}`,
                            style: { color: "#fff", background: bg },
                        },
                    });
                }
            });
            return ann;
        }

        function applyAnnotationsToRoot(root) {
            try {
                if (!root) return;
                const chartId = root.getAttribute("data-chart-id");
                const raw = root.getAttribute("data-initial") || "{}";
                let init = {};
                try {
                    init = JSON.parse(raw);
                } catch (e) {
                    init = {};
                }
                const thresholds = init.thresholds || {};
                const ann = buildAnnotationsFromThresholds(thresholds);
                if (!ann.length) return;

                const targetCid =
                    (window.__waterQualityKnownChartId &&
                        window.__waterQualityKnownChartId[chartId]) ||
                    chartId ||
                    "apexcharts" + chartId;
                try {
                    if (
                        window.ApexCharts &&
                        typeof window.ApexCharts.exec === "function"
                    ) {
                        window.ApexCharts.exec(
                            targetCid,
                            "updateOptions",
                            { annotations: { yaxis: ann } },
                            false,
                            true
                        );
                    }
                } catch (e) {
                    // ignore
                }
            } catch (e) {
                // ignore
            }
        }

        // Apply on DOMContentLoaded for existing roots
        document.addEventListener("DOMContentLoaded", () => {
            try {
                const roots = Array.from(
                    document.querySelectorAll("[data-chart-id]")
                );
                roots.forEach((r) => applyAnnotationsToRoot(r));
            } catch (e) {}
        });

        // Re-apply when a water-quality:point event occurs (covers instance mapping changes)
        try {
            window.addEventListener("water-quality:point", (evt) => {
                try {
                    const roots = Array.from(
                        document.querySelectorAll("[data-chart-id]")
                    );
                    roots.forEach((r) => applyAnnotationsToRoot(r));
                } catch (e) {}
            });
        } catch (e) {}
    })();
})();

// Listen for chart payload updates dispatched from Livewire and update/recreate ApexCharts safely
try {
    window.addEventListener("water-quality:chart", (evt) => {
        try {
            const payload = evt && evt.detail ? evt.detail : {};
            const chartObj = payload.chart || {};
            const cfg = chartObj.jsonConfig || {};
            const series = cfg.series || [];
            const chartId =
                cfg.chart && cfg.chart.id
                    ? cfg.chart.id
                    : chartObj.meta && chartObj.meta.chart_id
                    ? chartObj.meta.chart_id
                    : null;
            if (!chartId) return;

            const container = document.getElementById(chartId);
            if (!container) return;

            const targetCid =
                (window.__waterQualityKnownChartId &&
                    window.__waterQualityKnownChartId[chartId]) ||
                chartId ||
                "apexcharts" + chartId;

            window.__agwasuriCharts = window.__agwasuriCharts || {};
            let chartInstance = window.__agwasuriCharts[chartId] || null;

            if (
                !chartInstance &&
                window.ApexCharts &&
                typeof window.ApexCharts.getChartByID === "function"
            ) {
                try {
                    chartInstance = window.ApexCharts.getChartByID(targetCid);
                } catch (e) {
                    chartInstance = null;
                }
            }

            if (chartInstance) {
                // Update options and series in-place
                try {
                    chartInstance.updateOptions(cfg, false, true);
                    chartInstance.updateSeries(series, true);
                    window.__agwasuriCharts[chartId] = chartInstance;
                    console.debug("waterQualityInit: updated chart", chartId);
                } catch (e) {
                    console.error(
                        "waterQualityInit: failed to update chart",
                        chartId,
                        e
                    );
                }
                return;
            }

            // If DOM has canvas but instance missing, wipe once before creating
            try {
                if (container.querySelector(".apexcharts-canvas")) {
                    container.innerHTML = "";
                }
            } catch (e) {}

            // No existing instance: create one
            try {
                // ensure noData fallback shows axes when series empty
                cfg.noData = cfg.noData || {
                    text: "No water quality data available yet",
                    align: "center",
                    verticalAlign: "middle",
                    style: { fontSize: "14px", color: "#999" },
                };

                const inst = new ApexCharts(container, cfg);
                inst.render();
                window.__waterQualityKnownChartId =
                    window.__waterQualityKnownChartId || {};
                window.__waterQualityKnownChartId[chartId] =
                    cfg.chart && cfg.chart.id ? cfg.chart.id : chartId;
                window.__waterQualityChartInstances =
                    window.__waterQualityChartInstances || {};
                window.__waterQualityChartInstances[chartId] = inst;
                window.__agwasuriCharts[chartId] = inst;
                console.debug("waterQualityInit: created chart", chartId);
            } catch (e) {
                console.error(
                    "waterQualityInit: failed to create chart",
                    chartId,
                    e
                );
            }
        } catch (e) {
            console.error("waterQualityInit: chart event handler failed", e);
        }
    });
} catch (e) {}
