import "./bootstrap";
// Import theme helper and expose toggleTheme globally so inline buttons can call it
import { initTheme, toggleTheme } from "./theme";
initTheme();
// Expose toggleTheme globally so non-module inline buttons can call it
window.toggleTheme = toggleTheme;
// Expose initTheme so consumers can re-run initialization after Livewire updates
window.initTheme = initTheme;

// Initialize water-quality Livewire-aware helpers
import "./waterQualityInit";

// Ensure Livewire partial updates re-apply theme state and re-dispatch the
// shared DOM event. This keeps the toggle icons and component state in sync
// after Livewire replaces DOM fragments (common on dashboard interactions).
if (window.Livewire) {
    Livewire.hook("message.processed", () => {
        try {
            const cur =
                localStorage.getItem("agwasuri_theme") ||
                (document.documentElement.classList.contains("dark")
                    ? "dark"
                    : "light");
            window.dispatchEvent(
                new CustomEvent("agwasuri:theme-changed", { detail: cur })
            );
            // Defensive: reattach helper if missing
            if (
                typeof window.toggleTheme !== "function" &&
                window.theme &&
                typeof window.theme.toggleTheme === "function"
            ) {
                window.toggleTheme = window.theme.toggleTheme;
            }
            // Re-run init if available
            if (typeof window.initTheme === "function") {
                try {
                    window.initTheme();
                } catch (e) {}
            }
        } catch (e) {}
    });
}
