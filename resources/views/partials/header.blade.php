{{-- MİYSOFT PTS — Admin Header --}}
<header id="admin-header">

    {{-- Sol: Hamburger + Başlık --}}
    <div class="flex items-center gap-3">
        <button class="pts-hamburger" onclick="openSidebar()" aria-label="Menü">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <span class="text-sm font-semibold text-gray-600 hidden sm:block">@yield('title', 'Genel Bakış')</span>
    </div>

    {{-- Orta: Arama --}}
    <div class="flex-1 hidden md:flex max-w-xs">
        <div class="relative w-full">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" placeholder="Ara..." autocomplete="off"
                class="w-full pl-9 pr-3 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-200 transition">
        </div>
    </div>

    {{-- Sağ: Tema + Bildirim + Kullanıcı --}}
    <div class="flex items-center gap-1">

        {{-- Tema Değiştir --}}
        <button class="admin-theme-btn" onclick="toggleAdminTheme()" title="Tema değiştir">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="adminThemeIcon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
            <span id="adminThemeLabel" style="font-size:.6875rem">Koyu</span>
        </button>

        {{-- Dökümantasyon --}}
        <a href="{{ route('admin.dokumantasyon.page') }}" class="pts-header-btn" title="Dökümantasyon">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </a>

        {{-- Website --}}
        <a href="{{ config('app.website_url', '#') }}" target="_blank" class="pts-header-btn" title="Website">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
            </svg>
        </a>

        {{-- Bildirim --}}
        <div class="relative">
            <button class="pts-header-btn" onclick="toggleNotif()" aria-label="Bildirimler">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span id="notif-badge"
                    style="display:none;position:absolute;top:-2px;right:-2px;min-width:16px;height:16px;padding:0 3px;font-size:10px;font-weight:700;background:#FA6001;color:#fff;border-radius:9999px;display:flex;align-items:center;justify-content:center"></span>
            </button>
            <div id="notif-dropdown" class="pts-dropdown" style="width:20rem">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:.75rem 1rem;border-bottom:1px solid #f1f5f9">
                    <span style="font-size:.875rem;font-weight:600;color:#1e293b">Bildirimler</span>
                    <button onclick="markAllRead()" style="font-size:.75rem;color:var(--pts-brand);background:none;border:none;cursor:pointer;font-weight:500">Tümünü okundu işaretle</button>
                </div>
                <div id="notif-list" style="max-height:16rem;overflow-y:auto">
                    <div style="padding:1.25rem;text-align:center;font-size:.8125rem;color:#94a3b8">Bildirim yok</div>
                </div>
                <div style="padding:.5rem 1rem;border-top:1px solid #f1f5f9;background:#f8fafc;text-align:center">
                    <a href="{{ route('admin.notifications.index') }}" style="font-size:.75rem;color:var(--pts-brand);font-weight:500">Tüm bildirimler &rarr;</a>
                </div>
            </div>
        </div>

        {{-- Kullanıcı --}}
        <div class="relative" id="user-menu-wrap">
            <button onclick="toggleUserMenu()" class="flex items-center gap-2 px-2 py-1 rounded-xl hover:bg-gray-50 focus:outline-none transition-colors">
                <div class="pts-user-avatar" style="background:var(--pts-brand);color:#0f172a">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                </div>
                <div class="hidden sm:block text-left">
                    <p class="text-sm font-semibold text-gray-800 leading-tight">{{ auth()->user()?->name }}</p>
                    <p class="text-xs text-gray-400 leading-tight">{{ auth()->user()?->roles->first()?->name ?? '' }}</p>
                </div>
                <svg class="hidden sm:block text-gray-400" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div id="user-dropdown" class="pts-dropdown">
                <div style="padding:.75rem 1rem;border-bottom:1px solid #f1f5f9">
                    <p style="font-size:.8125rem;font-weight:600;color:#1e293b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ auth()->user()?->name }}</p>
                    <p style="font-size:.75rem;color:#94a3b8;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ auth()->user()?->email }}</p>
                </div>
                <a href="{{ route('profile.edit') }}"
                   style="display:flex;align-items:center;gap:.75rem;padding:.625rem 1rem;font-size:.875rem;color:#374151;text-decoration:none;transition:background .15s"
                   onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <svg width="15" height="15" fill="none" stroke="#9ca3af" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Profilim
                </a>
                <a href="{{ route('admin.settings.index') }}"
                   style="display:flex;align-items:center;gap:.75rem;padding:.625rem 1rem;font-size:.875rem;color:#374151;text-decoration:none;transition:background .15s"
                   onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <svg width="15" height="15" fill="none" stroke="#9ca3af" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Ayarlar
                </a>
                <div style="height:1px;background:#f1f5f9;margin:.25rem 0"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        style="width:100%;display:flex;align-items:center;gap:.75rem;padding:.625rem 1rem;font-size:.875rem;color:#dc2626;background:none;border:none;cursor:pointer;transition:background .15s;text-align:left"
                        onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background=''">
                        <svg width="15" height="15" fill="none" stroke="#dc2626" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Çıkış Yap
                    </button>
                </form>
            </div>
        </div>

    </div>
</header>
