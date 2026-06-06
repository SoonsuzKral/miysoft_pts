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
</body>
</html>
