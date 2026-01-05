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

            // Also drop any other cached references to avoid stale id reuse
            clearChartCache(chartId);

            console.debug("waterQualityInit: cleaned up chart", chartId);
        } catch (e) {
            console.error("waterQualityInit: destroy error", e);
        }
    }

    function clearChartCache(chartId) {
        if (!chartId) return;
        try {
            if (window.__waterQualityKnownChartId) {
                delete window.__waterQualityKnownChartId[chartId];
            }
            if (window.__waterQualityChartRegistry) {
                delete window.__waterQualityChartRegistry[chartId];
            }
            if (window.__waterQualityChartInstances) {
                delete window.__waterQualityChartInstances[chartId];
            }
            if (window.__agwasuriCharts) {
                delete window.__agwasuriCharts[chartId];
            }
            if (window.__waterQualityActiveRange) {
                delete window.__waterQualityActiveRange[chartId];
            }
            try {
                localStorage.removeItem("waterQualityKnownChartId:" + chartId);
            } catch (e) {}
            console.debug("waterQualityInit: cleared cache for", chartId);
        } catch (e) {
            console.debug("waterQualityInit: clear cache failed", e);
        }
    }

    function forceChartHeightAndResize(chartId, desiredHeight = 480) {
        try {
            if (
                !chartId ||
                !window.ApexCharts ||
                typeof window.ApexCharts.exec !== "function"
            )
                return;
            const targetCid =
                (window.__waterQualityKnownChartId &&
                    window.__waterQualityKnownChartId[chartId]) ||
                chartId ||
                "apexcharts" + chartId;

            try {
                window.ApexCharts.exec(
                    targetCid,
                    "updateOptions",
                    { chart: { height: desiredHeight, width: "100%" } },
                    false,
                    true
                );
            } catch (e) {}

            try {
                window.ApexCharts.exec(targetCid, "resize");
            } catch (e) {}
        } catch (e) {
            console.debug(
                "waterQualityInit: forceChartHeightAndResize failed",
                e
            );
        }
    }
    // Expose globally for consumers outside this IIFE (e.g., chart event listener below)
    try {
        window.forceChartHeightAndResize = forceChartHeightAndResize;
    } catch (e) {}

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

    // Debug helper: log date-range-updated events reaching the browser (with reentrancy guard)
    try {
        let handlingRange = false;
        window.addEventListener("date-range-updated", (evt) => {
            if (handlingRange) return; // prevent infinite recursion
            handlingRange = true;
            try {
                console.debug("waterQualityInit: date-range-updated event", evt.detail || evt);
                const detail = evt && evt.detail ? evt.detail : evt;
                const payload = Array.isArray(detail)
                    ? { startDate: detail[0], endDate: detail[1] }
                    : {
                          startDate: detail.startDate || detail[0] || detail.start || null,
                          endDate: detail.endDate || detail[1] || detail.end || null,
                          startTime: detail.startTime || null,
                          endTime: detail.endTime || null,
                          startDateTime: detail.startDateTime || null,
                          endDateTime: detail.endDateTime || null,
                      };

                // Bridge to Livewire global dispatch (best effort)
                if (window.Livewire && typeof window.Livewire.dispatch === "function") {
                    window.Livewire.dispatch("date-range-updated", payload);
                }

                // Targeted: call updateDateRange on the chart component directly if present
                try {
                    const root = document.querySelector('[data-chart-id][data-livewire-id]');
                    const compId = root ? root.getAttribute('data-livewire-id') : null;
                    if (compId && window.Livewire && typeof window.Livewire.find === "function") {
                        const comp = window.Livewire.find(compId);
                        if (comp && typeof comp.call === "function") {
                            comp.call("updateDateRange", payload.startDate, payload.endDate, payload.startTime, payload.endTime, payload.startDateTime, payload.endDateTime);
                        }
                    }
                } catch (e) {}
            } catch (e) {
                console.debug("waterQualityInit: date-range handler error", e);
            } finally {
                handlingRange = false;
            }
        });
    } catch (e) {}

    // Clear caches when server asks (e.g., user switch)
    try {
        window.addEventListener("water-quality:clear-cache", (evt) => {
            const detail = evt && evt.detail ? evt.detail : {};
            const ids = [detail.chartId, detail.nextChartId].filter(Boolean);
            ids.forEach((id) => clearChartCache(id));
        });
    } catch (e) {}

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

                    // After re-init, request authoritative snapshots for any chart roots
                    // inside the updated component so charts reflect current filters.
                    try {
                        const roots = component.el.querySelectorAll
                            ? component.el.querySelectorAll("[data-chart-id]")
                            : [];
                        // Enforce stable height and trigger a resize for each discovered chart
                        roots.forEach((r) => {
                            const cid =
                                (r.getAttribute &&
                                    r.getAttribute("data-chart-id")) ||
                                null;
                            if (cid) forceChartHeightAndResize(cid);
                        });
                        if (
                            roots &&
                            roots.length &&
                            window.__waterQualityFetchSnapshot
                        ) {
                            roots.forEach((r) => {
                                try {
                                    window.__waterQualityFetchSnapshot(r);
                                } catch (e) {}
                            });
                        }
                    } catch (e) {}
                }, 100);
            } catch (e) {
                console.debug(
                    "waterQualityInit: message.processed init error",
                    e
                );
            }
        });

        // Bridge Livewire-dispatched filter events to window-level CustomEvents
        // so any listener (including non-Livewire scripts) can react.
        if (typeof window.Livewire.on === "function") {
            window.Livewire.on("water-quality:filters", (detail) => {
                try {
                    window.dispatchEvent(
                        new CustomEvent("water-quality:filters", {
                            detail,
                        })
                    );
                } catch (e) {
                    console.debug(
                        "waterQualityInit: forward filters event failed",
                        e
                    );
                }
            });
        }
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
        window.__waterQualityActiveRange =
            window.__waterQualityActiveRange || {};
        const SNAP_INTERVAL = 500; // ms per-chart

        async function fetchAndApplySnapshot(root) {
            try {
                const chartId = root.getAttribute("data-chart-id");
                const userId = root.getAttribute("data-user-id");
                if (!chartId || !userId) return;

                const activeRange =
                    (window.__waterQualityActiveRange &&
                        window.__waterQualityActiveRange[chartId]) ||
                    null;
                if (
                    activeRange &&
                    (activeRange.startDate || activeRange.endDate)
                ) {
                    return; // respect active date/time filters; do not overwrite with unfiltered snapshot
                }
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

        // Expose the snapshot fetcher so other hooks (Livewire message.processed)
        // or manual triggers can request an authoritative refresh for a specific root.
        try {
            window.__waterQualityFetchSnapshot = fetchAndApplySnapshot;
        } catch (e) {}

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
                        const cid = root.getAttribute("data-chart-id");
                        const activeRange =
                            window.__waterQualityActiveRange && cid
                                ? window.__waterQualityActiveRange[cid]
                                : null;
                        if (
                            activeRange &&
                            (activeRange.startDate || activeRange.endDate)
                        ) {
                            continue; // skip snapshot when filters are active
                        }
                        // fire-and-forget snapshot update per-root
                        fetchAndApplySnapshot(root);
                    }
                } catch (e) {}
            });
            // When filters change (date preset/custom), Livewire may update only
            // KPI components. Allow dispatching a custom 'water-quality:filters'
            // event (from Livewire) with detail { userId, chartId } to request
            // a chart refresh for the selected user / chart.
            window.addEventListener("water-quality:filters", (evt) => {
                try {
                    const detail = evt && evt.detail ? evt.detail : {};
                    const userId = detail.userId || detail.user_id || null;
                    const chartId = detail.chartId || detail.chart_id || null;
                    const startDate =
                        detail.startDate || detail.start_date || null;
                    const endDate = detail.endDate || detail.end_date || null;
                    window.__waterQualityActiveRange =
                        window.__waterQualityActiveRange || {};

                    if (chartId) {
                        window.__waterQualityActiveRange[chartId] = {
                            startDate,
                            endDate,
                        };
                        // If a filter is active, rely on Livewire payload instead of unfiltered snapshot
                        if (startDate || endDate) return;

                        const root = document.querySelector(
                            `[data-chart-id="${chartId}"]`
                        );
                        if (root) fetchAndApplySnapshot(root);
                        return;
                    }
                    if (userId) {
                        const roots = Array.from(
                            document.querySelectorAll(
                                `[data-chart-id^="water-quality-${userId}"]`
                            )
                        );
                        if (roots && roots.length) {
                            roots.forEach((r) => {
                                const cid = r.getAttribute("data-chart-id");
                                if (cid) {
                                    window.__waterQualityActiveRange[cid] = {
                                        startDate,
                                        endDate,
                                    };
                                }
                                if (!startDate && !endDate) {
                                    fetchAndApplySnapshot(r);
                                }
                            });
                        }
                        return;
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

            let metaUserId = null;
            try {
                metaUserId =
                    chartObj && chartObj.meta
                        ? chartObj.meta.userId || chartObj.meta.user_id || null
                        : null;
            } catch (e) {
                metaUserId = null;
            }

            let urlUserId = null;
            try {
                urlUserId = new URLSearchParams(window.location.search).get(
                    "user"
                );
            } catch (e) {
                urlUserId = null;
            }

            let domUserId = null;
            try {
                const roots = Array.from(
                    document.querySelectorAll("[data-chart-id][data-user-id]")
                );
                for (const r of roots) {
                    const uid = r.getAttribute("data-user-id");
                    if (uid) {
                        domUserId = uid;
                        break;
                    }
                }
            } catch (e) {
                domUserId = null;
            }

            const derivedUserId = metaUserId || urlUserId || domUserId || null;
            console.debug("waterQualityInit: derive user", {
                metaUserId,
                urlUserId,
                domUserId,
                derivedUserId,
            });
            if (!derivedUserId) {
                console.warn(
                    "waterQualityInit: no derived user id; abort chart render"
                );
                return; // cannot determine user; abort
            }
            const derivedChartId = `water-quality-${derivedUserId}`;

            const payloadChartId =
                (chartObj.meta && chartObj.meta.chart_id) ||
                (cfg.chart && cfg.chart.id) ||
                derivedChartId;
            let workingChartId = derivedChartId;

            const startDate =
                (chartObj &&
                    chartObj.meta &&
                    (chartObj.meta.startDate || chartObj.meta.start_date)) ||
                null;
            const endDate =
                (chartObj &&
                    chartObj.meta &&
                    (chartObj.meta.endDate || chartObj.meta.end_date)) ||
                null;
            window.__waterQualityActiveRange =
                window.__waterQualityActiveRange || {};
            window.__waterQualityActiveRange[workingChartId] = {
                startDate,
                endDate,
            };

            // Ensure chart options carry explicit dimensions to avoid height drift
            cfg.chart = cfg.chart || {};
            if (!cfg.chart.height) cfg.chart.height = 480;
            if (!cfg.chart.width) cfg.chart.width = "100%";
            // Force chart id to current user-derived id to avoid stale instance reuse
            cfg.chart.id = workingChartId;

            console.debug("waterQualityInit: chart event", {
                chartId: workingChartId,
                cfgChartId: cfg && cfg.chart ? cfg.chart.id : null,
                metaChartId:
                    chartObj && chartObj.meta ? chartObj.meta.chart_id : null,
            });
            const MAX_RETRIES = 10;
            const RETRY_DELAY = 120;

            function clearCachedIds(badId) {
                try {
                    if (!badId) return;
                    if (window.__waterQualityKnownChartId) {
                        delete window.__waterQualityKnownChartId[badId];
                    }
                    if (window.__waterQualityChartRegistry) {
                        delete window.__waterQualityChartRegistry[badId];
                    }
                    if (window.__waterQualityChartInstances) {
                        delete window.__waterQualityChartInstances[badId];
                    }
                    if (window.__agwasuriCharts) {
                        delete window.__agwasuriCharts[badId];
                    }
                    if (window.__waterQualityActiveRange) {
                        delete window.__waterQualityActiveRange[badId];
                    }
                    if (window.__waterQualitySeriesState) {
                        delete window.__waterQualitySeriesState[badId];
                    }
                    try {
                        localStorage.removeItem(
                            "waterQualityKnownChartId:" + badId
                        );
                    } catch (e) {}
                } catch (e) {}
            }

            function renderWithRetry(attempt) {
                let container = null;
                let domChartId = null;
                try {
                    if (derivedUserId) {
                        const preferredRoot = document.querySelector(
                            `[data-chart-id="water-quality-${derivedUserId}"]`
                        );
                        if (preferredRoot) {
                            domChartId =
                                preferredRoot.getAttribute("data-chart-id") ||
                                null;
                            const innerByDomId = domChartId
                                ? preferredRoot.querySelector(`#${domChartId}`)
                                : null;
                            const innerByPayload = preferredRoot.querySelector(
                                `#${workingChartId}`
                            );
                            container =
                                innerByDomId || innerByPayload || preferredRoot;
                        }
                    }
                } catch (e) {
                    container = null;
                }

                // Fallback lookups if the preferred user-scoped root wasn't found
                if (!container) {
                    container =
                        document.getElementById(workingChartId) ||
                        document.querySelector(
                            `[data-chart-id="${workingChartId}"] #${workingChartId}`
                        ) ||
                        document.querySelector(
                            `[data-chart-id="${workingChartId}"]`
                        );
                }
                if (!container) {
                    if (attempt < MAX_RETRIES) {
                        setTimeout(
                            () => renderWithRetry(attempt + 1),
                            RETRY_DELAY
                        );
                    } else {
                        console.error(
                            "waterQualityInit: container not found for",
                            chartId,
                            "after",
                            attempt,
                            "retries"
                        );
                    }
                    return;
                }
                // If the container/root declares a chart id, prefer it
                try {
                    const fallbackDomChartId =
                        domChartId ||
                        (container.getAttribute &&
                            container.getAttribute("data-chart-id")) ||
                        null;
                    if (
                        fallbackDomChartId &&
                        fallbackDomChartId !== workingChartId
                    ) {
                        // Clear stale cache keyed by the incoming id so future execs target the live chart
                        clearCachedIds(workingChartId);
                        if (
                            payloadChartId &&
                            payloadChartId !== fallbackDomChartId
                        ) {
                            clearCachedIds(payloadChartId);
                        }
                        workingChartId = fallbackDomChartId;
                        cfg.chart.id = workingChartId;
                        window.__waterQualityActiveRange[workingChartId] =
                            window.__waterQualityActiveRange[
                                workingChartId
                            ] || { startDate, endDate };
                    }
                } catch (e) {}

                // If the payload id differs from the derived id, wipe the payload's caches to avoid stale appends
                if (payloadChartId && payloadChartId !== workingChartId) {
                    clearCachedIds(payloadChartId);
                }
                // Always prefer derived id
                if (derivedChartId && derivedChartId !== workingChartId) {
                    clearCachedIds(workingChartId);
                    workingChartId = derivedChartId;
                    cfg.chart.id = workingChartId;
                }

                console.debug("waterQualityInit: chart ids", {
                    metaUser:
                        chartObj && chartObj.meta ? chartObj.meta.userId : null,
                    metaChart:
                        chartObj && chartObj.meta
                            ? chartObj.meta.chart_id
                            : null,
                    cfgChartId: cfg && cfg.chart ? cfg.chart.id : null,
                    domChartId: domChartId,
                    workingChartId,
                    urlUser: urlUserId,
                });
                // Consolidated create/update logic below. Use a pending flag to avoid
                // concurrent double-inits during rapid Livewire swaps.
                // Always destroy any existing instance to avoid stale ids, then recreate fresh
                try {
                    if (
                        window.ApexCharts &&
                        typeof window.ApexCharts.getChartByID === "function"
                    ) {
                        const stale =
                            window.ApexCharts.getChartByID(workingChartId);
                        if (stale && typeof stale.destroy === "function") {
                            stale.destroy();
                        }
                        if (
                            payloadChartId &&
                            payloadChartId !== workingChartId
                        ) {
                            const stalePayload =
                                window.ApexCharts.getChartByID(payloadChartId);
                            if (
                                stalePayload &&
                                typeof stalePayload.destroy === "function"
                            ) {
                                stalePayload.destroy();
                            }
                        }
                    }
                } catch (e) {}

                // Always create fresh (no cache reuse)
                if (true) {
                    window.__waterQualityPending =
                        window.__waterQualityPending || {};
                    if (window.__waterQualityPending[workingChartId]) {
                        // Another render is in progress; schedule a short retry so the
                        // current handler can pick up the instance once it's available.
                        if (attempt < MAX_RETRIES) {
                            setTimeout(
                                () => renderWithRetry(attempt + 1),
                                RETRY_DELAY
                            );
                        } else {
                            console.error(
                                "waterQualityInit: pending create never resolved for",
                                workingChartId
                            );
                        }
                        return;
                    }

                    window.__waterQualityPending[workingChartId] = true;
                    try {
                        // If leftover canvas exists, wipe it to avoid ApexCharts errors
                        try {
                            if (
                                container.querySelector &&
                                container.querySelector(".apexcharts-canvas")
                            ) {
                                container.innerHTML = "";
                            }
                        } catch (e) {}

                        // Enforce container dimensions before create
                        try {
                            container.style.height = `${
                                cfg.chart.height || 480
                            }px`;
                            container.style.minHeight = `${
                                cfg.chart.height || 480
                            }px`;
                            container.style.width = "100%";
                        } catch (e) {}

                        cfg.noData = cfg.noData || {
                            text: "No water quality data available yet",
                            align: "center",
                            verticalAlign: "middle",
                            style: { fontSize: "14px", color: "#999" },
                        };

                        const inst = new ApexCharts(container, cfg);
                        inst.render()
                            .then(() => {
                                const resizeFn =
                                    (typeof forceChartHeightAndResize ===
                                    "function"
                                        ? forceChartHeightAndResize
                                        : window.forceChartHeightAndResize) ||
                                    null;
                                if (resizeFn)
                                    resizeFn(
                                        workingChartId,
                                        cfg.chart && cfg.chart.height
                                            ? cfg.chart.height
                                            : 480
                                    );
                                console.debug(
                                    "waterQualityInit: created chart",
                                    workingChartId
                                );
                                window.__waterQualityPending[
                                    workingChartId
                                ] = false;
                            })
                            .catch((renderErr) => {
                                console.error(
                                    "waterQualityInit: failed to render chart",
                                    workingChartId,
                                    renderErr
                                );
                                window.__waterQualityPending[
                                    workingChartId
                                ] = false;
                            });
                    } catch (e) {
                        console.error(
                            "waterQualityInit: failed to create chart",
                            workingChartId,
                            e
                        );
                        window.__waterQualityPending[workingChartId] = false;
                    }
                }
            }

            renderWithRetry(0);
            return; // renderWithRetry handles create/update paths
        } catch (e) {
            console.error("waterQualityInit: chart event handler failed", e);
        }
    });
} catch (e) {}
