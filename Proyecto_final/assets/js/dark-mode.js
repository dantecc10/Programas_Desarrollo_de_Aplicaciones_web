(function() {
    var STORAGE_KEY = 'dark-mode';
    var CLASS_NAME = 'dark-mode';

    function isDark() {
        try {
            var saved = localStorage.getItem(STORAGE_KEY);
            if (saved !== null) return saved === '1';
        } catch (e) {}
        return window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    document.documentElement.classList.toggle(CLASS_NAME, isDark());
})();

document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('darkModeToggle');
    if (!btn) return;

    function setTheme(dark) {
        document.documentElement.classList.toggle('dark-mode', dark);
        try { localStorage.setItem('dark-mode', dark ? '1' : '0'); } catch (e) {}
        btn.innerHTML = dark
            ? '<i class="bi bi-sun-fill"></i>'
            : '<i class="bi bi-moon-fill"></i>';
        btn.title = dark ? 'Modo claro' : 'Modo oscuro';
        btn.setAttribute('aria-label', dark ? 'Activar modo claro' : 'Activar modo oscuro');
    }

    var isCurrentlyDark = document.documentElement.classList.contains('dark-mode');
    btn.innerHTML = isCurrentlyDark
        ? '<i class="bi bi-sun-fill"></i>'
        : '<i class="bi bi-moon-fill"></i>';

    btn.addEventListener('click', function(e) {
        e.preventDefault();
        setTheme(!document.documentElement.classList.contains('dark-mode'));
    });
});
