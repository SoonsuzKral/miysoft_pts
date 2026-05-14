<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Giriş Yap') — {{ config('app.name', 'MİYSOFT PTS') }}</title>
    <link rel="icon" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --primary: #02E0FB; --secondary: #FA6001; }
        .gradient-text { background: linear-gradient(135deg, #02E0FB, #FA6001); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 font-sans antialiased flex items-center justify-center p-4">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-[#02E0FB] opacity-5 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 rounded-full bg-[#FA6001] opacity-5 blur-3xl"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex flex-col items-center gap-2">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#02E0FB] to-[#00b8d9] flex items-center justify-center shadow-xl shadow-cyan-500/30">
                    <span class="text-white font-black text-2xl">M</span>
                </div>
                <span class="text-2xl font-bold text-white">MİYSOFT <span class="text-[#02E0FB]">PTS</span></span>
            </a>
            <p class="text-gray-400 text-sm mt-2">Personel Takip Sistemi</p>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl p-8">
            {{ $slot }}
        </div>

        <div class="text-center mt-6 text-sm text-gray-400">
            &copy; {{ now()->year }} <a href="{{ route('home') }}" class="text-[#02E0FB] hover:underline">MİYSOFT Teknoloji</a>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
