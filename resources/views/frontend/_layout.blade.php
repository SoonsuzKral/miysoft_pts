<!DOCTYPE html>
<html lang="tr" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MİYSOFT PTS') — Akıllı Personel Takip Sistemi</title>
    <meta name="description" content="@yield('meta_description', 'Türkiye\'nin en kapsamlı bulut tabanlı personel yönetim sistemi.')">
    <link rel="icon" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #02E0FB; --secondary: #FA6001; --bg: #FEFEFE; }
        body { font-family: 'Inter', sans-serif; }
        .gradient-text { background: linear-gradient(135deg, #02E0FB, #FA6001); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .btn-primary { background: linear-gradient(135deg, #02E0FB, #00b8d9); color: #0a0a1a; font-weight: 700; transition: all .3s; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 40px rgba(2,224,251,.4); }
        .btn-secondary { background: linear-gradient(135deg, #FA6001, #e05500); color: white; font-weight: 700; transition: all .3s; }
        .btn-secondary:hover { transform: translateY(-2px); box-shadow: 0 10px 40px rgba(250,96,1,.4); }
        .card-hover { transition: all .3s ease; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 60px rgba(2,224,251,.15); }
    </style>
    @stack('styles')
</head>
<body class="bg-[#FEFEFE] text-gray-800 antialiased">
<nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-gray-100 shadow-sm" x-data="{ open: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#02E0FB] to-[#00b8d9] flex items-center justify-center shadow-lg shadow-cyan-500/30">
                    <span class="text-white font-black text-sm">M</span>
                </div>
                <div class="leading-none">
                    <p class="font-black text-gray-900 text-base tracking-tight">MİYSOFT</p>
                    <p class="text-[10px] font-semibold text-[#02E0FB] tracking-widest uppercase">PTS</p>
                </div>
            </a>
            <div class="hidden md:flex items-center gap-1">
                @foreach([[route('home'),'Ana Sayfa'],[route('product'),'Ürün'],[route('pricing'),'Fiyatlandırma'],[route('blog.index'),'Blog'],[route('about'),'Hakkımızda'],[route('contact'),'İletişim']] as [$href,$label])
                <a href="{{ $href }}" class="px-3.5 py-2 text-sm font-medium text-gray-600 hover:text-[#02E0FB] hover:bg-[#02E0FB]/5 rounded-lg transition-all">{{ $label }}</a>
                @endforeach
            </div>
            <div class="hidden md:flex items-center gap-3">
                <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-[#02E0FB] px-4 py-2 transition-colors">Giriş Yap</a>
                <a href="{{ route('free-trial') }}" class="btn-primary px-5 py-2.5 rounded-xl text-sm inline-block">Ücretsiz Deneyin →</a>
            </div>
            <button @click="open = !open" class="md:hidden p-2 text-gray-500 hover:text-[#02E0FB]">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
        <div x-show="open" x-transition class="md:hidden pb-4 space-y-1">
            @foreach([[route('home'),'Ana Sayfa'],[route('product'),'Ürün'],[route('pricing'),'Fiyatlandırma'],[route('blog.index'),'Blog'],[route('about'),'Hakkımızda'],[route('contact'),'İletişim']] as [$href,$label])
            <a href="{{ $href }}" class="block px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-[#02E0FB]/5 rounded-lg">{{ $label }}</a>
            @endforeach
            <div class="pt-3 border-t border-gray-100 flex flex-col gap-2">
                <a href="{{ route('login') }}" class="text-center py-2.5 text-sm font-medium text-gray-600 border border-gray-200 rounded-xl">Giriş Yap</a>
                <a href="{{ route('free-trial') }}" class="btn-primary text-center py-2.5 text-sm rounded-xl">Ücretsiz Deneyin</a>
            </div>
        </div>
    </div>
</nav>
@yield('content')
@include('frontend._footer')
@stack('scripts')
</body>
</html>
