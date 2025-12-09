// Lightweight initializer to keep water-quality chart append/replay working
// across Livewire partial updates.

(function () {
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

    // Re-init roots inside the Livewire-updated component after message processed
    if (window.Livewire && typeof Livewire.hook === "function") {
        Livewire.hook("message.processed", (message, component) => {
            try {
                if (!component || !component.el) return;
                initAllRoots(component.el);
            } catch (e) {}
        });
    } else {
        // Defensive: if Livewire isn't available yet, attempt a global hook later
        console.debug("waterQualityInit: Livewire not present at init time");
    }

    // Expose helper for manual invocation/debugging
    window.waterQualityInit = function (root) {
        rediscoverAndReplayRoot(root);
    };
})();
