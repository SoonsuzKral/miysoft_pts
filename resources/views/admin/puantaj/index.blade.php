@extends('layouts.app')
@section('title', 'Puantaj Yönetimi')
@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#02E0FB] transition-colors">Dashboard</a>
    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-800 font-medium">Puantaj Yönetimi</span>
@endsection
@section('page_header')
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Puantaj Yönetimi</h1>
        <p class="text-sm text-gray-500 mt-0.5" id="headerSubtitle">Canlı puantaj durumu</p>
    </div>
    <div class="flex items-center gap-2">
        @can('attendance.export')
        <button onclick="exportPuantaj()"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
            Excel İndir
        </button>
        <button onclick="exportPuantajPdf()"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            PDF
        </button>
        @endcan
        @can('attendance.create')
        <button onclick="openManualEntryModal()"
            class="flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-[#02E0FB] to-cyan-500 hover:from-cyan-500 hover:to-[#02E0FB] rounded-xl shadow-md hover:shadow-lg transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Manuel Kayıt
        </button>
        @endcan
    </div>
@endsection
@section('content')

<style>
.animate-scale-in { animation: scaleIn .25s ease-out; }
@keyframes scaleIn { from { opacity: 0; transform: scale(0.95) translateY(-10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
.animate-pulse-soft { animation: pulseSoft 2s ease-in-out infinite; }
@keyframes pulseSoft { 0%, 100% { opacity: 1; } 50% { opacity: .6; } }
.animate-slide-in { animation: slideIn .3s ease-out; }
@keyframes slideIn { from { opacity: 0; transform: translateX(40px); } to { opacity: 1; transform: translateX(0); } }
.p-card { transition: all .2s ease; background: #fff; border: 1px solid #e5e7eb; }
.p-card:hover { transform: translateY(-2px); border-color: #02E0FB; box-shadow: 0 4px 12px rgba(2,224,251,.15); }
.filter-card { transition: all .2s ease; }
.filter-card:focus-within { box-shadow: 0 0 0 2px rgba(2,224,251,.15); border-color: #02E0FB; }
.status-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
.status-dot.working { background: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.2); }
.status-dot.on_break { background: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,.2); }
.status-dot.checked_out { background: #ef4444; }
.status-dot.not_started { background: #94a3b8; }
.status-dot.shift_pending { background: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.2); }
.live-refresh-indicator { transition: all .3s ease; }
@keyframes spin { to { transform: rotate(360deg); } }
.spinner { animation: spin 1s linear infinite; }
@media (max-width: 640px) {
    .resp-table thead { display: none; }
    .resp-table tbody tr { display: flex; flex-direction: column; padding: 12px; border-bottom: 1px solid #f1f5f9; }
    .resp-table tbody td { display: flex; align-items: center; justify-content: space-between; padding: 6px 0; border: none; text-align: right; }
    .resp-table tbody td:before { content: attr(data-label); font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; }
    .resp-table tbody td:first-child { padding-top: 0; }
    .resp-table tbody td:last-child { padding-bottom: 0; }
}
</style>

{{-- KPI Hero --}}
<div class="bg-gradient-to-br from-white via-gray-50 to-gray-100 rounded-2xl p-4 sm:p-6 mb-6 shadow-sm border border-gray-200/80 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-64 h-64 bg-[#02E0FB]/5 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-48 h-48 bg-cyan-500/5 rounded-full blur-3xl -ml-16 -mb-16 pointer-events-none"></div>
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
        @foreach([
            ['id'=>'kpi-total','label'=>'Toplam Personel','color'=>'text-gray-600','bg'=>'bg-gray-100','valueColor'=>'text-gray-800','icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
            ['id'=>'kpi-working','label'=>'Çalışıyor','color'=>'text-emerald-600','bg'=>'bg-emerald-100','valueColor'=>'text-emerald-600','icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['id'=>'kpi-break','label'=>'Molada','color'=>'text-amber-600','bg'=>'bg-amber-100','valueColor'=>'text-amber-600','icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['id'=>'kpi-out','label'=>'Çıkış Yaptı','color'=>'text-red-600','bg'=>'bg-red-100','valueColor'=>'text-red-600','icon'=>'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['id'=>'kpi-notstarted','label'=>'Başlamadı','color'=>'text-gray-500','bg'=>'bg-gray-100','valueColor'=>'text-gray-500','icon'=>'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4'],
            ['id'=>'kpi-mobile','label'=>'Mobil Giriş','color'=>'text-purple-600','bg'=>'bg-purple-100','valueColor'=>'text-purple-600','icon'=>'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z'],
            ['id'=>'kpi-manual','label'=>'Manuel Kayıt','color'=>'text-orange-600','bg'=>'bg-orange-100','valueColor'=>'text-orange-600','icon'=>'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
            ['id'=>'kpi-biometric','label'=>'Biyometrik','color'=>'text-green-600','bg'=>'bg-green-100','valueColor'=>'text-green-600','icon'=>'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ] as $card)
        <div class="p-card rounded-xl px-3 py-3 cursor-default">
            <div class="flex items-center justify-between mb-1.5">
                <p class="text-[9px] font-semibold uppercase tracking-wider {{ $card['color'] }}">{{ $card['label'] }}</p>
                <div class="w-7 h-7 rounded-lg {{ $card['bg'] }} flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 {{ $card['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/></svg>
                </div>
            </div>
            <p class="text-xl font-black {{ $card['valueColor'] }}" id="{{ $card['id'] }}">—</p>
        </div>
        @endforeach
    </div>
</div>

{{-- Tab Navigation --}}
<div class="flex flex-wrap gap-1 border-b border-gray-200 mb-5">
    <button onclick="setPuantajTab('live')" id="ptab-live" class="px-4 py-2.5 text-sm font-medium border-b-2 border-[#02E0FB] text-[#02E0FB] transition-all">
        <span class="flex items-center gap-2">
            <span class="relative flex w-2 h-2">
                <span class="animate-ping absolute inline-flex w-full h-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex w-2 h-2 rounded-full bg-emerald-500"></span>
            </span>
            Canlı
        </span>
    </button>
    <button onclick="setPuantajTab('daily')" id="ptab-daily" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-all">Günlük Özet</button>
    <button onclick="setPuantajTab('detail')" id="ptab-detail" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-all">Personel Detay</button>
    <button onclick="setPuantajTab('monthly')" id="ptab-monthly" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-all">Aylık Özet</button>
</div>

{{-- ═══════════════════════ CANLI TAB ═══════════════════════ --}}
<div id="view-live">
    {{-- Vardiya Özet Kartları --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-5" id="shiftCards"></div>

    {{-- Filtreler --}}
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <input type="text" id="liveSearch" placeholder="Personel ara..." oninput="filterLiveCards()"
            class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB] w-48">
        <select id="liveStatusFilter" onchange="filterLiveCards()" class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
            <option value="">Tüm Durumlar</option>
            <option value="working">Çalışıyor</option>
            <option value="on_break">Molada</option>
            <option value="checked_out">Çıkış Yaptı</option>
            <option value="not_started">Başlamadı</option>
            <option value="shift_pending">Vardiya Bekliyor</option>
        </select>
        <select id="liveShiftFilter" onchange="filterLiveCards()" class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
            <option value="">Tüm Vardiyalar</option>
            @foreach($shifts as $s)
            <option value="{{ $s->id }}">{{ $s->name }}</option>
            @endforeach
        </select>
        <select id="liveDeptFilter" onchange="filterLiveCards()" class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
            <option value="">Tüm Departmanlar</option>
            @foreach($departments as $d)
            <option value="{{ $d->id }}">{{ $d->name }}</option>
            @endforeach
        </select>
        <div class="ml-auto flex items-center gap-2 text-xs text-gray-400">
            <span id="liveCount">0 kişi</span>
            <button onclick="toggleLiveAutoRefresh()" id="liveAutoBtn" class="px-2 py-1 rounded bg-emerald-100 text-emerald-700 font-medium text-[10px]">CANLI</button>
            <span id="liveTimer" class="text-[10px]"></span>
        </div>
    </div>

    {{-- Personel Kartları --}}
    <div id="liveCards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3"></div>
</div>

{{-- ═══════════════════════ GÜNLÜK TAB ═══════════════════════ --}}
<div id="view-daily" class="hidden">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Tarih</label>
                <input type="date" id="dailyDate" value="{{ today()->toDateString() }}"
                    class="filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Vardiya</label>
                <select id="dailyShift" class="filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
                    <option value="">Tümü</option>
                    @foreach($shifts as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Departman</label>
                <select id="dailyDept" class="filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
                    <option value="">Tümü</option>
                    @foreach($departments as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Durum</label>
                <select id="dailyStatus" class="filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
                    <option value="">Tümü</option>
                    <option value="working">Çalışıyor</option>
                    <option value="present">Tamamlandı</option>
                    <option value="absent">Devamsız</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button onclick="loadDailyOverview()" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-[#02E0FB] to-cyan-500 hover:from-cyan-500 hover:to-[#02E0FB] rounded-xl shadow-sm transition-all">Filtrele</button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-3 mb-4">
        <div class="bg-emerald-50 rounded-xl px-4 py-3 border border-emerald-100">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-600">Çalışıyor / Tamam</p>
            <p class="text-xl font-black text-emerald-700" id="dailyPresentCount">—</p>
        </div>
        <div class="bg-red-50 rounded-xl px-4 py-3 border border-red-100">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-red-600">Devamsız</p>
            <p class="text-xl font-black text-red-700" id="dailyAbsentCount">—</p>
        </div>
        <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-500">Toplam</p>
            <p class="text-xl font-black text-gray-700" id="dailyTotalCount">—</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm resp-table">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Personel</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Departman</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Vardiya</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Giriş</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Çıkış</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Net Mesai</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Gecikme</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Durum</th>
                    </tr>
                </thead>
                <tbody id="dailyTableBody" class="divide-y divide-gray-50">
                    <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400 text-sm">Yükleniyor...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ═══════════════════════ PERSONEL DETAY TAB ═══════════════════════ --}}
<div id="view-detail" class="hidden">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Personel</label>
                <select id="detailPersonel" class="filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
                    <option value="">— Seçin —</option>
                    @foreach($personels as $p)
                    <option value="{{ $p->id }}">{{ $p->first_name }} {{ $p->last_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Yıl</label>
                <select id="detailYear" class="filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
                    @for($y = now()->year; $y >= now()->year - 2; $y--)
                    <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Ay</label>
                <select id="detailMonth" class="filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
                    @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>{{ \Carbon\Carbon::create(null, $m)->locale('tr')->monthName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button onclick="loadPersonelDetail()" class="px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-[#02E0FB] to-cyan-500 hover:from-cyan-500 hover:to-[#02E0FB] rounded-xl shadow-sm transition-all">Getir</button>
                <button onclick="exportPersonelPdf()" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-all">
                    <svg class="w-4 h-4 text-red-600 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    PDF
                </button>
            </div>
        </div>
    </div>

    <div id="detailContent" class="hidden">
        {{-- Personel Bilgi Kartı --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-[#02E0FB] to-cyan-500 text-white text-lg font-bold flex items-center justify-center shadow-md" id="detailAvatar">—</div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900" id="detailName">—</h3>
                    <p class="text-sm text-gray-500"><span id="detailDept">—</span> · <span id="detailPos">—</span></p>
                    <p class="text-xs text-gray-400 mt-0.5"><span id="detailEmail">—</span> · <span id="detailPhone">—</span> · İşe Giriş: <span id="detailHireDate">—</span></p>
                </div>
            </div>
        </div>

        {{-- Bugünkü Durum --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <h4 class="text-sm font-bold text-gray-800 mb-3">Bugünkü Puantaj</h4>
                <div id="detailToday">
                    <div class="text-center text-gray-400 py-4">Henüz yüklenmedi</div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <h4 class="text-sm font-bold text-gray-800 mb-3">Aylık Özet</h4>
                <div id="detailMonthlySummary">
                    <div class="text-center text-gray-400 py-4">Henüz yüklenmedi</div>
                </div>
            </div>
        </div>

        {{-- Aylık Günlük Detay --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/30">
                <h4 class="text-sm font-bold text-gray-800">Günlük Detay</h4>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm resp-table">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="px-3 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Gün</th>
                            <th class="px-3 py-2.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Giriş</th>
                            <th class="px-3 py-2.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Çıkış</th>
                            <th class="px-3 py-2.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Çalışma</th>
                            <th class="px-3 py-2.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Gecikme</th>
                            <th class="px-3 py-2.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Fazla Mesai</th>
                            <th class="px-3 py-2.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Durum</th>
                        </tr>
                    </thead>
                    <tbody id="detailDailyBody" class="divide-y divide-gray-50"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════ AYLIK TAB ═══════════════════════ --}}
<div id="view-monthly" class="hidden">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Yıl</label>
                <select id="monthlyYear" class="filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
                    @for($y = now()->year; $y >= now()->year - 2; $y--)
                    <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Ay</label>
                <select id="monthlyMonth" class="filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
                    @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>{{ \Carbon\Carbon::create(null, $m)->locale('tr')->monthName }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Departman</label>
                <select id="monthlyDept" class="filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
                    <option value="">Tümü</option>
                    @foreach($departments as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button onclick="loadMonthlyOverview()" class="px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-[#02E0FB] to-cyan-500 hover:from-cyan-500 hover:to-[#02E0FB] rounded-xl shadow-sm transition-all">Filtrele</button>
                <button onclick="exportPuantajPdf()" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-all">
                    <svg class="w-4 h-4 text-red-600 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    PDF Rapor
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-4 gap-3 mb-4">
        <div class="bg-blue-50 rounded-xl px-4 py-3 border border-blue-100">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-blue-600">Ort. Çalışma</p>
            <p class="text-xl font-black text-blue-700" id="monthlyAvgHours">—</p>
        </div>
        <div class="bg-amber-50 rounded-xl px-4 py-3 border border-amber-100">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-amber-600">Toplam Fazla Mesai</p>
            <p class="text-xl font-black text-amber-700" id="monthlyOvertime">—</p>
        </div>
        <div class="bg-red-50 rounded-xl px-4 py-3 border border-red-100">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-red-600">Toplam Gecikme</p>
            <p class="text-xl font-black text-red-700" id="monthlyLate">—</p>
        </div>
        <div class="bg-purple-50 rounded-xl px-4 py-3 border border-purple-100">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-purple-600">Toplam Devamsızlık</p>
            <p class="text-xl font-black text-purple-700" id="monthlyAbsent">—</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm resp-table">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Personel</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Departman</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Çalışma Günü</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Toplam Saat</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Ort. Saat</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Fazla Mesai</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Gecikme</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Devamsızlık</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Verim</th>
                    </tr>
                </thead>
                <tbody id="monthlyTableBody" class="divide-y divide-gray-50">
                    <tr><td colspan="9" class="px-4 py-12 text-center text-gray-400 text-sm">Yükleniyor...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Manuel Kayıt Modal --}}
<div id="manualModal" class="hidden fixed inset-0 z-[110] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeManualModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 p-6 border border-gray-100 animate-scale-in">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Manuel Giriş/Çıkış Kaydı</h3>
            <button onclick="closeManualModal()" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="manualForm" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Personel <span class="text-red-500">*</span></label>
                <select name="personel_id" required class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#02E0FB] focus:ring-1 focus:ring-[#02E0FB]/20 transition-all">
                    <option value="">— Personel seçin —</option>
                    @foreach($personels as $p)
                    <option value="{{ $p->id }}">{{ $p->first_name }} {{ $p->last_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kayıt Türü <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach([['in','Giriş','bg-green-100 text-green-700 border-green-300'],['out','Çıkış','bg-red-100 text-red-700 border-red-300'],['break_start','Mola Başlangıç','bg-yellow-100 text-yellow-700 border-yellow-300'],['break_end','Mola Bitiş','bg-blue-100 text-blue-700 border-blue-300']] as [$val,$lbl,$cls])
                    <label class="flex items-center gap-2 p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 has-[:checked]:{{ $cls }} transition-all">
                        <input type="radio" name="type" value="{{ $val }}" class="w-4 h-4 text-[#02E0FB]">
                        <span class="text-sm font-medium text-gray-700">{{ $lbl }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kayıt Tarihi ve Saati <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="recorded_at" required value="{{ now()->format('Y-m-d\TH:i') }}"
                    class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#02E0FB] focus:ring-1 focus:ring-[#02E0FB]/20 transition-all">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Not</label>
                <textarea name="note" rows="2" placeholder="Neden manuel kayıt oluşturuluyor..."
                    class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#02E0FB] focus:ring-1 focus:ring-[#02E0FB]/20 transition-all"></textarea>
            </div>
        </form>
        <div class="flex justify-end gap-3 mt-5 pt-4 border-t border-gray-100">
            <button onclick="closeManualModal()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">İptal</button>
            <button onclick="submitManualRecord()" class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-[#02E0FB] to-cyan-500 hover:from-cyan-500 hover:to-[#02E0FB] rounded-xl shadow-sm transition-all">Kaydet</button>
        </div>
    </div>
</div>

{{-- Global Modal --}}
<div id="globalModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div id="modalBackdrop" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto border border-gray-100">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <h2 id="modalTitle" class="text-lg font-bold text-gray-900">Başlık</h2>
            <button onclick="closeModal()" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="modalBody" class="px-6 py-5"></div>
        <div id="modalFooter" class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-2"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const PUANTAJ_URLS = {
    liveStatus: '{{ route("admin.puantaj.live-status") }}',
    dailyOverview: '{{ route("admin.puantaj.daily-overview") }}',
    personelDetail: '{{ route("admin.puantaj.personel-detail") }}',
    monthlyOverview: '{{ route("admin.puantaj.monthly-overview") }}',
    todayStats: '{{ route("admin.puantaj.today-stats") }}',
    shifts: '{{ route("admin.puantaj.shifts-today") }}',
    store: '{{ route("admin.puantaj.record") }}',
    export: '{{ route("admin.puantaj.export") }}',
    exportPdf: '{{ route("admin.puantaj.export-pdf") }}',
    exportPersonelPdf: id => `/admin/puantaj/personel/${id}/export/pdf`,
};

let currentTab = 'live';
let liveTimer = null;
let liveAutoRefresh = true;
let liveInterval = null;

const statusConfig = {
    working: { label: 'Çalışıyor', bg: 'bg-emerald-50', text: 'text-emerald-700', dot: 'bg-emerald-500', border: 'border-emerald-200', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    on_break: { label: 'Molada', bg: 'bg-amber-50', text: 'text-amber-700', dot: 'bg-amber-500', border: 'border-amber-200', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
    checked_out: { label: 'Çıkış Yaptı', bg: 'bg-red-50', text: 'text-red-700', dot: 'bg-red-500', border: 'border-red-200', icon: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' },
    not_started: { label: 'Başlamadı', bg: 'bg-gray-50', text: 'text-gray-500', dot: 'bg-gray-400', border: 'border-gray-200', icon: 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4' },
    shift_pending: { label: 'Vardiya Bekliyor', bg: 'bg-blue-50', text: 'text-blue-700', dot: 'bg-blue-500', border: 'border-blue-200', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
};

const statusColors = { working: '#10b981', on_break: '#f59e0b', checked_out: '#ef4444', not_started: '#94a3b8', shift_pending: '#3b82f6' };

document.addEventListener('DOMContentLoaded', () => {
    loadLiveStatus();
    loadTodayStats();
    loadShiftCards();
    if (liveAutoRefresh) startLiveRefresh();
});

function toast(type, msg) {
    const c = type === 'success' ? 'bg-emerald-500' : type === 'warning' ? 'bg-amber-500' : 'bg-red-500';
    const el = document.createElement('div');
    el.className = `fixed top-5 right-5 z-[999] ${c} text-white px-5 py-3 rounded-xl shadow-2xl text-sm font-medium animate-slide-in max-w-sm`;
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3500);
}

// ─── Tab Switching ────────────────────────────────────────
function setPuantajTab(tab) {
    ['live','daily','detail','monthly'].forEach(t => {
        const view = document.getElementById('view-' + t);
        if (view) view.classList.toggle('hidden', t !== tab);
        const btn = document.getElementById('ptab-' + t);
        if (btn) {
            btn.classList.toggle('border-[#02E0FB]', t === tab);
            btn.classList.toggle('text-[#02E0FB]', t === tab);
            btn.classList.toggle('border-transparent', t !== tab);
            btn.classList.toggle('text-gray-500', t !== tab);
        }
    });
    currentTab = tab;
    if (tab === 'live') { loadLiveStatus(); loadTodayStats(); loadShiftCards(); }
    if (tab === 'daily') loadDailyOverview();
    if (tab === 'monthly') loadMonthlyOverview();
}

// ─── Live Auto Refresh ────────────────────────────────────
function startLiveRefresh() {
    if (liveInterval) clearInterval(liveInterval);
    liveInterval = setInterval(() => {
        if (liveAutoRefresh && currentTab === 'live') {
            loadLiveStatus();
            loadTodayStats();
            loadShiftCards();
            const el = document.getElementById('liveTimer');
            if (el) el.textContent = new Date().toLocaleTimeString('tr-TR');
        }
    }, 15000);
}

function toggleLiveAutoRefresh() {
    liveAutoRefresh = !liveAutoRefresh;
    const btn = document.getElementById('liveAutoBtn');
    if (btn) {
        btn.textContent = liveAutoRefresh ? 'CANLI' : 'DURAKLAT';
        btn.classList.toggle('bg-emerald-100', liveAutoRefresh);
        btn.classList.toggle('text-emerald-700', liveAutoRefresh);
        btn.classList.toggle('bg-gray-200', !liveAutoRefresh);
        btn.classList.toggle('text-gray-500', !liveAutoRefresh);
    }
    if (liveAutoRefresh) { startLiveRefresh(); loadLiveStatus(); }
    else if (liveInterval) { clearInterval(liveInterval); liveInterval = null; }
}

// ─── KPI ──────────────────────────────────────────────────
function loadTodayStats() {
    axios.get(PUANTAJ_URLS.todayStats).then(res => {
        const s = res.data.stats;
        document.getElementById('kpi-total').textContent = s.totalPersonel ?? '—';
        document.getElementById('kpi-working').textContent = s.currentlyWorking ?? '—';
        document.getElementById('kpi-break').textContent = s.onBreak ?? '—';
        document.getElementById('kpi-out').textContent = (s.todayOuts || 0);
        document.getElementById('kpi-notstarted').textContent = (s.totalPersonel || 0) - (s.currentlyWorking || 0) - (s.onBreak || 0) - (s.todayOuts || 0);
        document.getElementById('kpi-mobile').textContent = s.totalMobile ?? '—';
        document.getElementById('kpi-manual').textContent = s.totalManual ?? '—';
        document.getElementById('kpi-biometric').textContent = s.totalBiometric ?? '—';
        document.getElementById('headerSubtitle').textContent = `Bugün ${s.totalPersonel} personel, ${s.currentlyWorking} çalışıyor, ${s.onBreak} molada`;
    });
}

// ─── Shift Cards ──────────────────────────────────────────
function loadShiftCards() {
    axios.get(PUANTAJ_URLS.shifts).then(res => {
        const data = res.data.data || [];
        const el = document.getElementById('shiftCards');
        if (!data.length) { el.innerHTML = ''; return; }
        el.innerHTML = data.map(s => `
            <div class="rounded-xl px-4 py-3 border-l-4 shadow-sm" style="border-left-color: ${s.color || '#02E0FB'}; background: #fff; border-color: ${s.color || '#02E0FB'}20;">
                <div class="flex items-center justify-between mb-1">
                    <h4 class="text-sm font-bold text-gray-800">${s.name}</h4>
                    <span class="text-xs font-medium text-gray-500">${s.start}-${s.end}</span>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <span class="text-emerald-600 font-semibold">${s.checked_in}/${s.total_assigned}</span>
                    <span class="text-gray-400">giriş yaptı</span>
                    <span class="ml-auto text-xs font-bold ${s.completion >= 80 ? 'text-emerald-600' : s.completion >= 50 ? 'text-amber-600' : 'text-red-600'}">%${s.completion}</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5 mt-2">
                    <div class="h-1.5 rounded-full transition-all" style="width: ${s.completion}%; background: ${s.color || '#02E0FB'}"></div>
                </div>
            </div>
        `).join('');
    });
}

// ─── Live Cards ───────────────────────────────────────────
let liveData = [];

function loadLiveStatus() {
    axios.get(PUANTAJ_URLS.liveStatus).then(res => {
        liveData = res.data.data || [];
        filterLiveCards();
        const stats = res.data.stats;
        document.getElementById('kpi-working').textContent = stats.working ?? '—';
        document.getElementById('kpi-break').textContent = stats.onBreak ?? '—';
        document.getElementById('kpi-out').textContent = stats.checkedOut ?? '—';
        document.getElementById('kpi-notstarted').textContent = stats.notStarted ?? '—';
    });
}

function filterLiveCards() {
    const search = (document.getElementById('liveSearch')?.value || '').toLowerCase();
    const status = document.getElementById('liveStatusFilter')?.value || '';
    const shiftId = document.getElementById('liveShiftFilter')?.value || '';
    const deptId = document.getElementById('liveDeptFilter')?.value || '';

    let filtered = liveData.filter(p => {
        if (search && !p.name.toLowerCase().includes(search)) return false;
        if (status && p.status !== status) return false;
        if (shiftId && (!p.shift || p.shift.id != shiftId)) return false;
        if (deptId && p.department_id != deptId) return false;
        return true;
    });

    document.getElementById('liveCount').textContent = filtered.length + ' kişi';
    renderLiveCards(filtered);
}

function renderLiveCards(data) {
    const el = document.getElementById('liveCards');
    if (!data.length) {
        el.innerHTML = '<div class="col-span-full text-center text-gray-400 py-12 text-sm">Eşleşen personel bulunamadı</div>';
        return;
    }
    el.innerHTML = data.map(p => {
        const sc = statusConfig[p.status] || statusConfig.not_started;
        const shiftBadge = p.shift
            ? `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium" style="background: ${p.shift.color}15; color: ${p.shift.color}">${p.shift.name}</span>`
            : '';
        return `<div class="p-card rounded-xl px-4 py-3.5 cursor-default transition-all border-l-4" style="border-left-color: ${statusColors[p.status] || '#94a3b8'}">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#02E0FB] to-cyan-500 text-white text-xs font-bold flex items-center justify-center shadow-sm">${p.initials}</div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">${p.name}</p>
                        <p class="text-xs text-gray-400">${p.department}</p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5">
                    ${shiftBadge}
                    <span class="status-dot ${p.status}"></span>
                </div>
            </div>
            <div class="flex items-center gap-4 mt-3 pt-2.5 border-t border-gray-50 text-xs">
                <div>
                    <span class="text-gray-400">Giriş:</span>
                    <span class="font-semibold text-gray-700 ml-1">${p.check_in || '—'}</span>
                </div>
                <div>
                    <span class="text-gray-400">Çıkış:</span>
                    <span class="font-semibold text-gray-700 ml-1">${p.check_out || '—'}</span>
                </div>
                <div>
                    <span class="text-gray-400">Kaynak:</span>
                    <span class="font-medium ml-1 ${p.source === 'mobile' ? 'text-purple-600' : p.source === 'biometric' ? 'text-green-600' : 'text-gray-500'}">${p.source || '—'}</span>
                </div>
                <div class="ml-auto">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold ${sc.bg} ${sc.text}">${p.status_label}</span>
                </div>
            </div>
        </div>`;
    }).join('');
}

// ─── Daily Overview ───────────────────────────────────────
function loadDailyOverview() {
    const params = {
        date: document.getElementById('dailyDate').value,
        shift_id: document.getElementById('dailyShift').value,
        department_id: document.getElementById('dailyDept').value,
        status: document.getElementById('dailyStatus').value,
    };
    axios.get(PUANTAJ_URLS.dailyOverview, { params }).then(res => {
        const data = res.data.data || [];
        const stats = res.data.stats || {};

        document.getElementById('dailyPresentCount').textContent = (stats.present || 0) + (stats.working || 0);
        document.getElementById('dailyAbsentCount').textContent = stats.absent || 0;
        document.getElementById('dailyTotalCount').textContent = stats.total || 0;

        const tbody = document.getElementById('dailyTableBody');
        if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="px-4 py-12 text-center text-gray-400 text-sm">Kayıt bulunamadı</td></tr>';
            return;
        }

        const statusMap = {
            working: { label: 'Çalışıyor', bg: 'bg-emerald-50 text-emerald-700' },
            present: { label: 'Tamamlandı', bg: 'bg-blue-50 text-blue-700' },
            absent: { label: 'Devamsız', bg: 'bg-red-50 text-red-700' },
        };

        tbody.innerHTML = data.map(s => {
            const sm = statusMap[s.status] || { label: s.status, bg: 'bg-gray-100 text-gray-600' };
            const shiftName = s.shift
                ? `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium" style="background: ${s.shift.color}15; color: ${s.shift.color}">${s.shift.name}</span>`
                : '<span class="text-gray-400">—</span>';
            return `<tr class="hover:bg-gray-50/80 transition-colors">
                <td data-label="Personel" class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#02E0FB] to-cyan-500 text-white text-xs font-bold flex items-center justify-center shadow-sm">${s.initials}</div>
                        <span class="font-medium text-gray-800">${s.name}</span>
                    </div>
                </td>
                <td data-label="Departman" class="px-4 py-3 text-gray-500 text-xs">${s.department}</td>
                <td data-label="Vardiya" class="px-4 py-3">${shiftName}</td>
                <td data-label="Giriş" class="px-4 py-3 text-center font-mono font-semibold ${s.check_in ? 'text-green-700' : 'text-gray-300'}">${s.check_in || '—'}</td>
                <td data-label="Çıkış" class="px-4 py-3 text-center font-mono font-semibold ${s.check_out ? 'text-red-600' : 'text-gray-300'}">${s.check_out || '—'}</td>
                <td data-label="Mesai" class="px-4 py-3 text-center font-semibold text-gray-800">${s.net_work_hours} sa</td>
                <td data-label="Gecikme" class="px-4 py-3 text-center ${s.late_minutes > 0 ? 'text-red-600 font-semibold' : 'text-gray-400'}">${s.late_minutes > 0 ? s.late_minutes + ' dk' : '—'}</td>
                <td data-label="Durum" class="px-4 py-3 text-center">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold ${sm.bg}">${sm.label}</span>
                    ${!s.is_pair_complete ? '<span class="ml-1 text-orange-400" title="Eksik kayıt">⚠</span>' : ''}
                </td>
            </tr>`;
        }).join('');
    });
}

// ─── Personel Detail ──────────────────────────────────────
function loadPersonelDetail() {
    const personelId = document.getElementById('detailPersonel').value;
    if (!personelId) { toast('warning', 'Lütfen bir personel seçin.'); return; }

    const params = {
        personel_id: personelId,
        year: document.getElementById('detailYear').value,
        month: document.getElementById('detailMonth').value,
    };

    axios.get(PUANTAJ_URLS.personelDetail, { params }).then(res => {
        const d = res.data.data;
        if (!d) { toast('error', 'Veri bulunamadı'); return; }

        document.getElementById('detailContent').classList.remove('hidden');
        const p = d.personel;
        document.getElementById('detailAvatar').textContent = p.initials;
        document.getElementById('detailName').textContent = p.name;
        document.getElementById('detailDept').textContent = p.department;
        document.getElementById('detailPos').textContent = p.position;
        document.getElementById('detailEmail').textContent = p.email || '—';
        document.getElementById('detailPhone').textContent = p.phone || '—';
        document.getElementById('detailHireDate').textContent = p.hire_date || '—';

        // Today section
        const today = d.today;
        let todayHtml = '';
        if (today.shift) {
            todayHtml += `<div class="flex items-center gap-2 mb-3">
                <span class="text-xs text-gray-400">Vardiya:</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium" style="background: ${today.shift.color}15; color: ${today.shift.color}">${today.shift.name} (${today.shift.start}-${today.shift.end})</span>
            </div>`;
        }
        if (today.records && today.records.length) {
            todayHtml += `<div class="space-y-1.5">`;
            today.records.forEach(r => {
                const typeColors = { in: 'text-green-700 bg-green-50', out: 'text-red-700 bg-red-50', break_start: 'text-amber-700 bg-amber-50', break_end: 'text-blue-700 bg-blue-50' };
                const tc = typeColors[r.type] || 'text-gray-700 bg-gray-50';
                todayHtml += `<div class="flex items-center justify-between px-3 py-2 rounded-lg ${tc} text-xs">
                    <span class="font-semibold">${r.type_label}</span>
                    <span class="font-mono">${r.recorded_at}</span>
                    <span class="text-gray-400">${r.source}</span>
                </div>`;
            });
            todayHtml += `</div>`;
            todayHtml += `<div class="flex items-center gap-4 mt-3 pt-2 border-t border-gray-100 text-xs text-gray-500">
                <span>İlk Giriş: <strong class="text-gray-800">${today.first_in || '—'}</strong></span>
                <span>Son Çıkış: <strong class="text-gray-800">${today.last_out || '—'}</strong></span>
                <span>Toplam: <strong class="text-gray-800">${today.total_records} kayıt</strong></span>
            </div>`;
        } else {
            todayHtml = '<div class="text-center text-gray-400 py-4 text-sm">Bugün hiç kayıt yok</div>';
        }
        document.getElementById('detailToday').innerHTML = todayHtml;

        // Monthly section
        const m = d.monthly;
        if (m && m.total_work_hours !== undefined) {
            const eff = m.efficiency_pct || 0;
            const effColor = eff >= 80 ? 'text-emerald-600' : eff >= 50 ? 'text-amber-600' : 'text-red-600';
            document.getElementById('detailMonthlySummary').innerHTML = `
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Çalışma Günü</p>
                        <p class="text-lg font-black text-gray-800">${m.present_days || 0} / ${m.working_days_in_month || 0}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Toplam Saat</p>
                        <p class="text-lg font-black text-gray-800">${m.total_work_hours || 0} sa</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Fazla Mesai</p>
                        <p class="text-lg font-black text-blue-600">${m.total_overtime_hours || 0} sa</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Gecikme</p>
                        <p class="text-lg font-black text-red-600">${m.total_late_minutes || 0} dk</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 col-span-2">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Verimlilik</p>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full ${effColor.replace('text-', 'bg-')}" style="width: ${eff}%"></div>
                            </div>
                            <span class="text-lg font-black ${effColor}">%${eff}</span>
                        </div>
                    </div>
                </div>
                <div class="text-xs text-gray-400 mt-2">${m.month_name} · İzin: ${m.leave_days || 0} · Tatil: ${m.holiday_count || 0}</div>
            `;
        } else {
            document.getElementById('detailMonthlySummary').innerHTML = '<div class="text-center text-gray-400 py-4 text-sm">Aylık veri bulunamadı</div>';
        }

        // Daily detail table
        const dailyBody = document.getElementById('detailDailyBody');
        if (m && m.daily) {
            const days = Object.values(m.daily);
            if (days.length) {
                const efMap = {
                    present: { label: 'Mevcut', bg: 'bg-emerald-50 text-emerald-700' },
                    late: { label: 'Geç Geldi', bg: 'bg-amber-50 text-amber-700' },
                    overtime: { label: 'Fazla Mesai', bg: 'bg-blue-50 text-blue-700' },
                    absent: { label: 'Devamsız', bg: 'bg-red-50 text-red-700' },
                    incomplete: { label: 'Eksik', bg: 'bg-orange-50 text-orange-700' },
                    weekend: { label: 'Hafta Sonu', bg: 'bg-gray-100 text-gray-400' },
                    holiday: { label: 'Tatil', bg: 'bg-purple-50 text-purple-600' },
                    leave: { label: 'İzinli', bg: 'bg-yellow-50 text-yellow-700' },
                    partial: { label: 'Eksik Mesai', bg: 'bg-orange-50 text-orange-700' },
                };
                dailyBody.innerHTML = days.map(d => {
                    const em = efMap[d.status] || { label: d.status, bg: 'bg-gray-100 text-gray-600' };
                    return `<tr class="hover:bg-gray-50/50 transition-colors">
                        <td data-label="Gün" class="px-3 py-2">
                            <span class="text-xs font-medium text-gray-800">${d.day_name} ${d.date ? d.date.substring(8) : ''}</span>
                        </td>
                        <td data-label="Giriş" class="px-3 py-2 text-center font-mono text-xs ${d.check_in ? 'text-green-700' : 'text-gray-300'}">${d.check_in || '—'}</td>
                        <td data-label="Çıkış" class="px-3 py-2 text-center font-mono text-xs ${d.check_out ? 'text-red-600' : 'text-gray-300'}">${d.check_out || '—'}</td>
                        <td data-label="Çalışma" class="px-3 py-2 text-center text-xs font-semibold text-gray-800">${d.net_work_hours || '0'} sa</td>
                        <td data-label="Gecikme" class="px-3 py-2 text-center text-xs ${d.late_min > 0 ? 'text-red-600 font-semibold' : 'text-gray-400'}">${d.late_min > 0 ? d.late_min + 'dk' : '—'}</td>
                        <td data-label="Fazla Mesai" class="px-3 py-2 text-center text-xs ${d.overtime_min > 0 ? 'text-blue-600 font-semibold' : 'text-gray-400'}">${d.overtime_min > 0 ? Math.round(d.overtime_min/60*10)/10 + 'sa' : '—'}</td>
                        <td data-label="Durum" class="px-3 py-2 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold ${em.bg}">${em.label}</span>
                        </td>
                    </tr>`;
                }).join('');
                return;
            }
        }
        dailyBody.innerHTML = '<tr><td colspan="7" class="px-3 py-6 text-center text-gray-400 text-xs">Günlük detay bulunamadı</td></tr>';
    }).catch(e => {
        toast('error', 'Personel bilgileri yüklenemedi.');
    });
}

// ─── Monthly Overview ─────────────────────────────────────
function loadMonthlyOverview() {
    const params = {
        year: document.getElementById('monthlyYear').value,
        month: document.getElementById('monthlyMonth').value,
        department_id: document.getElementById('monthlyDept').value,
    };
    axios.get(PUANTAJ_URLS.monthlyOverview, { params }).then(res => {
        const data = res.data.data || [];
        const stats = res.data.stats || {};

        document.getElementById('monthlyAvgHours').textContent = stats.avg_work_hours ? stats.avg_work_hours + ' sa' : '—';
        document.getElementById('monthlyOvertime').textContent = stats.total_overtime ? stats.total_overtime + ' sa' : '—';
        document.getElementById('monthlyLate').textContent = stats.total_late ? stats.total_late + ' dk' : '—';
        document.getElementById('monthlyAbsent').textContent = stats.total_absent ? stats.total_absent + ' gün' : '—';

        const tbody = document.getElementById('monthlyTableBody');
        if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="9" class="px-4 py-12 text-center text-gray-400 text-sm">Veri bulunamadı</td></tr>';
            return;
        }
        tbody.innerHTML = data.map(s => `
            <tr class="hover:bg-gray-50/80 transition-colors">
                <td data-label="Personel" class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#02E0FB] to-cyan-500 text-white text-xs font-bold flex items-center justify-center shadow-sm">
                            ${(s.personel_name || '?').split(' ').map(n=>n[0]).join('')}
                        </div>
                        <span class="font-medium text-gray-800">${s.personel_name}</span>
                    </div>
                </td>
                <td data-label="Departman" class="px-4 py-3 text-xs text-gray-500">${s.department || '—'}</td>
                <td data-label="Çalışma Günü" class="px-4 py-3 text-center font-semibold text-gray-800">${s.present_days || 0}/${s.working_days_in_month || 0}</td>
                <td data-label="Toplam Saat" class="px-4 py-3 text-center font-semibold text-gray-800">${s.total_work_hours || 0} sa</td>
                <td data-label="Ort. Saat" class="px-4 py-3 text-center text-gray-600">${s.avg_work_hours || 0} sa</td>
                <td data-label="Fazla Mesai" class="px-4 py-3 text-center ${s.total_overtime_hours > 0 ? 'text-blue-600 font-semibold' : 'text-gray-400'}">${s.total_overtime_hours > 0 ? s.total_overtime_hours + ' sa' : '—'}</td>
                <td data-label="Gecikme" class="px-4 py-3 text-center ${s.total_late_minutes > 0 ? 'text-red-600 font-semibold' : 'text-gray-400'}">${s.total_late_minutes > 0 ? s.total_late_minutes + ' dk' : '—'}</td>
                <td data-label="Devamsızlık" class="px-4 py-3 text-center ${s.absent_days > 0 ? 'text-red-600 font-semibold' : 'text-gray-400'}">${s.absent_days || 0} gün</td>
                <td data-label="Verim" class="px-4 py-3 text-center">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold ${(s.efficiency_pct || 0) >= 80 ? 'bg-emerald-50 text-emerald-700' : (s.efficiency_pct || 0) >= 50 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700'}">%${s.efficiency_pct || 0}</span>
                </td>
            </tr>`).join('');
    });
}

// ─── Manual Entry ─────────────────────────────────────────
function openManualEntryModal() {
    document.getElementById('manualModal').classList.remove('hidden');
}

function closeManualModal() {
    document.getElementById('manualModal').classList.add('hidden');
}

function submitManualRecord() {
    const form = document.getElementById('manualForm');
    const data = Object.fromEntries(new FormData(form).entries());
    if (!data.personel_id || !data.type || !data.recorded_at) {
        toast('warning', 'Lütfen zorunlu alanları doldurun.');
        return;
    }
    axios.post(PUANTAJ_URLS.store, data).then(res => {
        closeManualModal();
        toast('success', res.data.message);
        loadLiveStatus();
        loadTodayStats();
    }).catch(e => toast('error', e.response?.data?.message || 'Kayıt başarısız'));
}

function closeModal() {
    document.getElementById('globalModal').classList.add('hidden');
}

// ─── Export ───────────────────────────────────────────────
function exportPuantaj() {
    const dateFrom = document.getElementById('dailyDate')?.value || new Date().toISOString().split('T')[0];
    const dateTo = dateFrom;
    window.open(PUANTAJ_URLS.export + '?date_from=' + dateFrom + '&date_to=' + dateTo, '_blank');
}

function exportPuantajPdf() {
    const year = document.getElementById('monthlyYear')?.value || new Date().getFullYear();
    const month = document.getElementById('monthlyMonth')?.value || (new Date().getMonth() + 1);
    const dept = document.getElementById('monthlyDept')?.value || '';
    window.open(PUANTAJ_URLS.exportPdf + '?year=' + year + '&month=' + month + '&department_id=' + dept, '_blank');
}

function exportPersonelPdf() {
    const id = document.getElementById('detailPersonel').value;
    if (!id) { toast('warning', 'Personel seçin'); return; }
    const year = document.getElementById('detailYear').value;
    const month = document.getElementById('detailMonth').value;
    window.open(PUANTAJ_URLS.exportPersonelPdf(id) + '?year=' + year + '&month=' + month, '_blank');
}
</script>
@endpush
