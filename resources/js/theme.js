// Theme helper module
// Exposes `toggleTheme()` and initializes theme behavior.
export function toggleTheme() {
    const current = document.documentElement.classList.contains("dark")
        ? "dark"
        : "light";
    const next = current === "dark" ? "light" : "dark";
    try {
        localStorage.setItem("agwasuri_theme", next);
    } catch (e) {}
    applyTheme(next);
    try {
        // Set both cookie names to be robust across different client code
        // Use a long expiry and SameSite=Lax so the cookie is sent on navigation
        const expires = new Date();
        expires.setFullYear(expires.getFullYear() + 10);
        const expiresStr = expires.toUTCString();
        try {
            document.cookie = `agwasuri_theme=${next}; expires=${expiresStr}; path=/; SameSite=Lax`;
        } catch (e) {}
        try {
            document.cookie = `theme=${next}; expires=${expiresStr}; path=/; SameSite=Lax`;
        } catch (e) {}
    } catch (e) {}
    // Inform the server so it can set secure server-side cookies immediately
    try {
        fetch("/set-theme", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            credentials: "same-origin",
            body: JSON.stringify({ theme: next }),
        })
            .then(function (response) {
                // We no longer force a full reload here. The app now re-applies the
                // theme on client-side navigations (Livewire/Alpine) and the server
                // will also set cookies in the response. This keeps UX smooth.
            })
            .catch(function () {});
    } catch (e) {}
    try {
        window.dispatchEvent(
            new CustomEvent("agwasuri:theme-changed", { detail: next })
        );
    } catch (e) {}
}

function applyTheme(theme) {
    try {
        if (theme === "dark") {
            document.documentElement.classList.add("dark");
        } else {
            document.documentElement.classList.remove("dark");
        }

        // persist to localStorage and cookie for server-side reads
        try {
            localStorage.setItem("agwasuri_theme", theme);
        } catch (e) {}
        try {
            const expires = new Date();
            expires.setFullYear(expires.getFullYear() + 10);
            const expiresStr = expires.toUTCString();
            try {
                document.cookie = `agwasuri_theme=${theme}; expires=${expiresStr}; path=/; SameSite=Lax`;
            } catch (e) {}
            try {
                document.cookie = `theme=${theme}; expires=${expiresStr}; path=/; SameSite=Lax`;
            } catch (e) {}
        } catch (e) {}

        // Broadcast a DOM event for non-Livewire consumers
        try {
            window.dispatchEvent(
                new CustomEvent("agwasuri:theme-changed", { detail: theme })
            );
        } catch (e) {}

        // Notify Livewire listeners (client-side) so components can react
        if (window.Livewire && typeof Livewire.emit === "function") {
            try {
                Livewire.emit("dark-mode-toggled", {
                    darkMode: theme === "dark",
                });
            } catch (e) {}
        }
    } catch (e) {
        // swallow errors - non-critical
        console.warn("applyTheme error", e);
    }
}

// Initialize on module import: apply persisted theme if present
export function initTheme() {
    try {
        // Read persisted theme. Handle malformed/hidden-key variants in localStorage
        let saved = null;
        try {
            saved = localStorage.getItem("agwasuri_theme");
        } catch (e) {
            saved = null;
        }

        // If the normal key is missing but a visually-similar key exists
        // (e.g. zero-width characters), try to detect and normalize it.
        if (!saved) {
            try {
                for (let i = 0; i < localStorage.length; i++) {
                    const k = localStorage.key(i);
                    if (!k) continue;
                    // normalize key by removing common invisible characters and trimming
                    const normalized = k
                        .replace(/\u200B|\uFEFF|\u200E|\u200F/g, "")
                        .trim();
                    if (normalized === "agwasuri_theme") {
                        try {
                            const v = localStorage.getItem(k);
                            if (v) {
                                // promote to canonical key
                                localStorage.setItem("agwasuri_theme", v);
                                // optionally remove the weird key to avoid confusion
                                try {
                                    localStorage.removeItem(k);
                                } catch (e) {}
                                saved = v;
                                break;
                            }
                        } catch (e) {}
                    }
                }
            } catch (e) {}
        }

        // If still not found, fall back to cookie values (server might have set them)
        if (!saved) {
            try {
                const cookies = Object.fromEntries(
                    document.cookie.split(/;\s*/).map((c) => {
                        const idx = c.indexOf("=");
                        return [
                            c.slice(0, idx),
                            decodeURIComponent(c.slice(idx + 1)),
                        ];
                    })
                );
                if (cookies["agwasuri_theme"])
                    saved = cookies["agwasuri_theme"];
                else if (cookies["theme"]) saved = cookies["theme"];
            } catch (e) {}
        }

        applyTheme(saved);
        // Notify listeners about the current theme on init
        try {
            const cur =
                saved ||
                (document.documentElement.classList.contains("dark")
                    ? "dark"
                    : "light");
            window.dispatchEvent(
                new CustomEvent("agwasuri:theme-changed", { detail: cur })
            );
        } catch (e) {}
        // Listen for system preference changes and update if user hasn't chosen
        window
            .matchMedia?.("(prefers-color-scheme: dark)")
            .addEventListener("change", (e) => {
                if (!localStorage.getItem("agwasuri_theme")) applyTheme(null);
            });
    } catch (e) {}
}

// also expose applyTheme for programmatic use if needed
export { applyTheme };
