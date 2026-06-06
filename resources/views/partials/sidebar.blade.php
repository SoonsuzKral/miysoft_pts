{{-- MİYSOFT PTS — Sidebar --}}
<aside id="admin-sidebar">

    {{-- Logo --}}
    <div class="pts-sidebar-logo">
        <div class="pts-sidebar-logo-icon">
            <svg fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/>
            </svg>
        </div>
        <div class="pts-sidebar-logo-text">
            <p>MİYSOFT</p>
            <p>PTS</p>
        </div>
        <button onclick="closeSidebar()" class="pts-hamburger" style="margin-left:auto" aria-label="Kapat">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Şirket adı --}}
    <div class="pts-sidebar-company">
        {{ auth()->user()?->company?->name ?? 'Yönetim Paneli' }}
    </div>

    {{-- Nav --}}
    <nav class="pts-nav">

        @php
        $r = request()->route()?->getName() ?? '';
        $on = fn(string $p) => str_starts_with($r, $p);
        @endphp

        {{-- Dashboard --}}
        <a href="{{ route('admin.dashboard') }}" class="pts-nav-link {{ $on('admin.dashboard') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
            </svg>
            <span>Genel Bakış</span>
            <span class="pts-dot"></span>
        </a>

        {{-- ─── İNSAN KAYNAKLARI ─── --}}
        <p class="pts-nav-section">İnsan Kaynakları</p>

        @can('personel.view')
        <a href="{{ route('admin.personel.index') }}" class="pts-nav-link {{ $on('admin.personel') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Personel</span><span class="pts-dot"></span>
        </a>
        @endcan

        @can('leave.view')
        <a href="{{ route('admin.leave.index') }}" class="pts-nav-link {{ $on('admin.leave') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>İzin Yönetimi</span><span class="pts-dot"></span>
        </a>
        @endcan

        @can('attendance.view')
        <a href="{{ route('admin.puantaj.index') }}" class="pts-nav-link {{ $on('admin.puantaj') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Puantaj</span><span class="pts-dot"></span>
        </a>
        @endcan

        @can('shift.view')
        <a href="{{ route('admin.shifts.index') }}" class="pts-nav-link {{ $on('admin.shifts') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <span>Vardiya</span><span class="pts-dot"></span>
        </a>
        @endcan

        @can('personel.manage')
        <a href="{{ route('admin.processes.index') }}" class="pts-nav-link {{ $on('admin.processes') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
            </svg>
            <span>Onboarding / Süreç</span><span class="pts-dot"></span>
        </a>
        @endcan

        {{-- ─── ŞİRKET YAPISI ─── --}}
        <div class="pts-nav-divider"></div>
        <p class="pts-nav-section">Şirket Yapısı</p>

        @can('department.view')
        <a href="{{ route('admin.companies.index') }}"
           class="pts-nav-link {{ ($on('admin.companies') || $on('admin.departments') || $on('admin.positions')) ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span>Şirket & Departman</span><span class="pts-dot"></span>
        </a>
        @endcan

        @can('holiday.view')
        <a href="{{ route('admin.holidays.index') }}" class="pts-nav-link {{ $on('admin.holidays') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>Tatil Takvimi</span><span class="pts-dot"></span>
        </a>
        @endcan

        {{-- ─── VARLIKLAR & FİNANS ─── --}}
        <div class="pts-nav-divider"></div>
        <p class="pts-nav-section">Varlıklar & Finans</p>

        @can('asset.view')
        <a href="{{ route('admin.assets.index') }}" class="pts-nav-link {{ $on('admin.assets') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <span>Envanter & Zimmet</span><span class="pts-dot"></span>
        </a>
        @endcan

        @can('advance.view')
        <a href="{{ route('admin.advance.index') }}" class="pts-nav-link {{ $on('admin.advance') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span>Avans</span><span class="pts-dot"></span>
        </a>
        @endcan

        @can('expense.view')
        <a href="{{ route('admin.expense.index') }}" class="pts-nav-link {{ $on('admin.expense') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
            </svg>
            <span>Masraf</span><span class="pts-dot"></span>
        </a>
        @endcan

        @can('travel.view')
        <a href="{{ route('admin.travel.index') }}" class="pts-nav-link {{ $on('admin.travel') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
            </svg>
            <span>Seyahat</span><span class="pts-dot"></span>
        </a>
        @endcan

        @can('vehicle.view')
        <a href="{{ route('admin.vehicles.index') }}" class="pts-nav-link {{ $on('admin.vehicles') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16H9m8-4H7l1-5h8l1 5z"/>
            </svg>
            <span>Araç Yönetimi</span><span class="pts-dot"></span>
        </a>
        @endcan

        @can('service.view')
        <a href="{{ route('admin.services.index') }}" class="pts-nav-link {{ $on('admin.services') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 13.255A23.93 23.93 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <span>Hizmetler</span><span class="pts-dot"></span>
        </a>
        @endcan

        {{-- ─── ETKİLEŞİM ─── --}}
        <div class="pts-nav-divider"></div>
        <p class="pts-nav-section">Etkileşim</p>

        <a href="{{ route('admin.interactions.index') }}" class="pts-nav-link {{ $on('admin.interactions') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span>Duyurular & Anketler</span><span class="pts-dot"></span>
        </a>

        @can('visitor.view')
        <a href="{{ route('admin.visitors.index') }}" class="pts-nav-link {{ $on('admin.visitors') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span>Ziyaretçi</span><span class="pts-dot"></span>
        </a>
        @endcan

        {{-- ─── ANALİTİK ─── --}}
        <div class="pts-nav-divider"></div>
        <p class="pts-nav-section">Analitik</p>

        @can('report.view')
        <a href="{{ route('admin.reports.index') }}" class="pts-nav-link {{ $on('admin.reports') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span>Raporlar</span><span class="pts-dot"></span>
        </a>
        @endcan

        {{-- ─── SİSTEM ─── --}}
        <div class="pts-nav-divider"></div>
        <p class="pts-nav-section">Sistem</p>

        @can('settings.manage')
        <a href="{{ route('admin.cms.index') }}" class="pts-nav-link {{ $on('admin.cms') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            <span>İçerik Yönetimi</span><span class="pts-dot"></span>
        </a>
        @endcan

        @can('settings.manage')
        <a href="{{ route('admin.media.index') }}" class="pts-nav-link {{ $on('admin.media') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>Medya Kütüphanesi</span><span class="pts-dot"></span>
        </a>
        @endcan

        @can('subscription.view')
        <a href="{{ route('admin.subscriptions.index') }}" class="pts-nav-link {{ $on('admin.subscriptions') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            <span>Abonelik</span><span class="pts-dot"></span>
        </a>
        @endcan

        @can('settings.view')
        <a href="{{ route('admin.settings.index') }}" class="pts-nav-link {{ $on('admin.settings') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Sistem Ayarları</span><span class="pts-dot"></span>
        </a>
        @endcan

        @can('role.view')
        <a href="{{ route('admin.roles.index') }}" class="pts-nav-link {{ $on('admin.roles') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <span>Rol & Yetki</span><span class="pts-dot"></span>
        </a>
        @endcan

    </nav>

    {{-- Footer --}}
    <div class="pts-sidebar-footer">
        <div class="pts-user-avatar">
            {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
        </div>
        <div style="flex:1;min-width:0">
            <p class="pts-user-name">
                {{ auth()->user()?->name }}
            </p>
            <p class="pts-user-role">
                {{ auth()->user()?->roles->first()?->name ?? 'Kullanıcı' }}
            </p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" title="Çıkış Yap" class="pts-header-btn" style="color:#64748b">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </button>
        </form>
    </div>

</aside>
