<script>
(function(){
    try {
        // Keep Livewire and other dynamic updates in sync with the theme
        document.addEventListener('livewire:init', function(){ 
            try {
                if (window.Livewire && typeof Livewire.on === 'function') {
                    Livewire.on('dark-mode-toggled', function(payload){
                        try {
                            if (payload && payload.darkMode) {
                                document.documentElement.classList.add('dark');
                                try { localStorage.setItem('agwasuri_theme','dark'); } catch(e){}
                            } else {
                                document.documentElement.classList.remove('dark');
                                try { localStorage.setItem('agwasuri_theme','light'); } catch(e){}
                            }
                        } catch(e){}
                    });
                }
            } catch(e){}
        });

        // DOM-level custom event used elsewhere to signal theme changes
        window.addEventListener('agwasuri:theme-changed', function(e){
            try {
                if (e && e.detail === 'dark') { document.documentElement.classList.add('dark'); }
                else { document.documentElement.classList.remove('dark'); }
            } catch(e){}
        });

        // Re-apply theme after client-side navigations (Livewire / Alpine navigate)
        function reapplyThemeFromStorageOrCookie() {
            try {
                var pref = null;
                try { pref = localStorage.getItem('agwasuri_theme') || localStorage.getItem('theme'); } catch(e) { pref = null; }
                if (pref === 'dark') {
                    document.documentElement.classList.add('dark');
                    window.dispatchEvent(new CustomEvent('agwasuri:theme-changed', { detail: 'dark' }));
                    return;
                }
                if (pref === 'light') {
                    document.documentElement.classList.remove('dark');
                    window.dispatchEvent(new CustomEvent('agwasuri:theme-changed', { detail: 'light' }));
                    return;
                }

                // fallback to cookie if no localStorage preference
                var m = document.cookie.match('(?:^|; )agwasuri_theme=([^;]+)') || document.cookie.match('(?:^|; )theme=([^;]+)');
                if (m && m[1] === 'dark') {
                    document.documentElement.classList.add('dark');
                    window.dispatchEvent(new CustomEvent('agwasuri:theme-changed', { detail: 'dark' }));
                }
            } catch(e){}
        }

        document.addEventListener('livewire:navigated', function(){ try { reapplyThemeFromStorageOrCookie(); } catch(e){} });
        document.addEventListener('alpine:navigated', function(){ try { reapplyThemeFromStorageOrCookie(); } catch(e){} });

        // Some libraries (e.g. nprogress) overwrite `document.documentElement.className`
        // which can accidentally remove our `dark` class. Observe class changes
        // and re-apply the `dark` class when the user preference is dark.
        try {
            if (!window._agwasuriThemeClassObserver) {
                window._agwasuriThemeClassObserver = new MutationObserver(function(mutations){
                    try {
                        mutations.forEach(function(m){
                            if (m.type === 'attributes' && m.attributeName === 'class') {
                                // determine preference
                                var pref = null;
                                try { pref = localStorage.getItem('agwasuri_theme') || localStorage.getItem('theme'); } catch(e) { pref = null; }
                                if (!pref) {
                                    var mm = document.cookie.match('(?:^|; )agwasuri_theme=([^;]+)') || document.cookie.match('(?:^|; )theme=([^;]+)');
                                    if (mm && mm[1]) pref = mm[1];
                                }

                                if (pref === 'dark' && !document.documentElement.classList.contains('dark')) {
                                    // temporarily disconnect to avoid recursion
                                    try { window._agwasuriThemeClassObserver.disconnect(); } catch(e){}
                                    try { document.documentElement.classList.add('dark'); } catch(e){}
                                    try { window._agwasuriThemeClassObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] }); } catch(e){}
                                    window.dispatchEvent(new CustomEvent('agwasuri:theme-changed', { detail: 'dark' }));
                                }
                            }
                        });
                    } catch(e){}
                });
                try { window._agwasuriThemeClassObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] }); } catch(e){}
            }
        } catch(e){}
    } catch(e){}
})();
</script>

<script>
// Minimal non-verbose listeners: forward user-selected and delegated clicks without debug logs.
// Keep errors visible but avoid noisy console output used during debugging.

// Forward `user-selected` browser events to Livewire (so server-side #[On] handlers run)
window.addEventListener('user-selected', function(e){
    try {
        if (window.Livewire && typeof Livewire.emit === 'function') {
            var userId = (e && e.detail && (e.detail.userId ?? e.detail.id)) || null;
            Livewire.emit('user-selected', userId);
        }
    } catch(err) { console.warn('forward user-selected failed'); }
});

// Delegated click handler: forward clicks on elements with `data-livewire-user-id`
document.addEventListener('click', function(e){
    try {
        var el = e.target && e.target.closest ? e.target.closest('[data-livewire-user-id]') : null;
        if (!el) return;
        var id = el.getAttribute('data-livewire-user-id');
        if (!id) return;
        if (window.Livewire && typeof Livewire.emit === 'function') {
            Livewire.emit('user-selected', id);
            // Update placeholders if provided via data attributes
            try {
                var placeholderTarget = el.getAttribute('data-placeholder-target');
                var placeholderValue = el.getAttribute('data-placeholder-value');
                if (placeholderTarget && placeholderValue) {
                    var input = document.getElementById(placeholderTarget);
                    if (input) {
                        input.placeholder = placeholderValue;
                        input.value = '';
                    }
                }
            } catch(e){ /* noop */ }
        }
    } catch(e) { /* noop */ }
}, { capture: false });
</script>
