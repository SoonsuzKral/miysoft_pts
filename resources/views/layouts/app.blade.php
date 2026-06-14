<!DOCTYPE html>
<html lang="tr">
<head>
    @include('partials.head')
    <title>@yield('title', 'Admin') &mdash; {{ config('app.name', 'MİYSOFT PTS') }}</title>
</head>
<body>

    <div id="sidebar-overlay" onclick="closeSidebar()"></div>

    <div id="admin-wrapper">
        @include('partials.sidebar')

        <div id="admin-main">
            @include('partials.header')

            <div id="admin-content">
                <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

                    @yield('breadcrumbs')

                    @hasSection('page_header')
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                        @yield('page_header')
                    </div>
                    @endif

                    @include('partials.messages')

                    @yield('content')

                </div>
                @include('partials.footer')
            </div>
        </div>
    </div>

    @include('partials.scripts')
    @stack('scripts')

    <script>
    // ── Admin Dark Theme ──
    function getAdminTheme() { return localStorage.getItem('adminTheme') || 'light'; }

    function setAdminTheme(theme) {
        document.documentElement.setAttribute('data-admin-theme', theme);
        localStorage.setItem('adminTheme', theme);
        var lbl = document.getElementById('adminThemeLabel');
        var ico = document.getElementById('adminThemeIcon');
        if (theme === 'dark') {
            if (lbl) lbl.textContent = 'Açık';
            if (ico) ico.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>';
        } else {
            if (lbl) lbl.textContent = 'Koyu';
            if (ico) ico.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>';
        }
    }

    function toggleAdminTheme() {
        var current = getAdminTheme();
        var next = current === 'dark' ? 'light' : 'dark';
        setAdminTheme(next);
    }

    setAdminTheme(getAdminTheme());
    </script>
</body>
</html>
