@props(['theme' => null])

@php
    $themePreference = $theme ?? auth()->user()?->theme ?? request()->cookie('theme') ?? 'system';
@endphp

<script>
    (function () {
        const root = document.documentElement;
        const media = window.matchMedia('(prefers-color-scheme: dark)');
        let currentTheme = @json($themePreference);

        const apply = (theme) => {
            const isDark = theme === 'dark' || (theme === 'system' && media.matches);

            root.classList.toggle('dark', isDark);
            root.style.colorScheme = isDark ? 'dark' : 'light';
        };

        apply(currentTheme);

        media.addEventListener('change', () => {
            if (currentTheme === 'system') {
                apply('system');
            }
        });

        window.setTheme = (theme) => {
            currentTheme = theme;
            apply(theme);
        };
    })();
</script>
