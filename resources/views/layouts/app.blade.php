<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
    <title>@yield('title', 'Admin') — {{ config('app.name', 'MİYSOFT PTS') }}</title>
</head>
<body class="min-h-screen bg-gray-50 font-sans antialiased">
    <div class="min-h-screen flex">
        @include('partials.sidebar')
        <div class="flex-1 flex flex-col min-w-0">
            @include('partials.header')
            <main class="flex-1 overflow-auto">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    @yield('breadcrumbs')
                    @hasSection('page_header')
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                        @yield('page_header')
                    </div>
                    @endif
                    @include('partials.messages')
                    @yield('content')
                </div>
            </main>
            @include('partials.footer')
        </div>
    </div>
    @include('partials.scripts')
    @stack('scripts')
</body>
</html>
