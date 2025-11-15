// Theme helper module
// Exposes `toggleTheme()` and initializes theme behavior.
export function toggleTheme() {
    const current = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    applyTheme(next);
}

function applyTheme(theme) {
    try {
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        // persist to localStorage and cookie for server-side reads
        try { localStorage.setItem('agwasuri_theme', theme); } catch(e) {}
        try { document.cookie = `agwasuri_theme=${theme}; path=/; max-age=${60*60*24*365}`; } catch(e) {}

        // Broadcast a DOM event for non-Livewire consumers
        try {
            window.dispatchEvent(new CustomEvent('agwasuri:theme-changed', { detail: theme }));
        } catch (e) {}

        // Notify Livewire listeners (client-side) so components can react
        if (window.Livewire && typeof Livewire.emit === 'function') {
            try { Livewire.emit('dark-mode-toggled', { darkMode: theme === 'dark' }); } catch(e) {}
        }
    } catch (e) {
        // swallow errors - non-critical
        console.warn('applyTheme error', e);
    }
}

// Initialize on module import: apply persisted theme if present
(function init() {
    try {
        const saved = (function(){
            try { return localStorage.getItem('agwasuri_theme'); } catch(e) { return null; }
        })();
        if (saved === 'dark' || saved === 'light') {
            applyTheme(saved);
        } else {
            // Respect system preference if no saved preference
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                applyTheme('dark');
            }
        }
    } catch (e) {}
})();

// also expose applyTheme for programmatic use if needed
export { applyTheme };
