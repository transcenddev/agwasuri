<script>
(function(){
    try {
        // Prefer localStorage (client-side toggle), fallback to cookies (server-side).
        var theme = null;
        try { theme = localStorage.getItem('agwasuri_theme') || localStorage.getItem('theme'); } catch(e) {}
        if (!theme) {
            var m = document.cookie.match('(?:^|; )agwasuri_theme=([^;]+)');
            if (!m) { m = document.cookie.match('(?:^|; )theme=([^;]+)'); }
            if (m) { theme = decodeURIComponent(m[1]); }
        }
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else if (theme === 'light') {
            document.documentElement.classList.remove('dark');
        }
        // expose small helper in case other scripts want to toggle immediately
        window.agwasuriSetDark = function(isDark){
            try {
                if (isDark) document.documentElement.classList.add('dark');
                else document.documentElement.classList.remove('dark');
            } catch(e){}
        };
    } catch(e){}
})();
</script>
