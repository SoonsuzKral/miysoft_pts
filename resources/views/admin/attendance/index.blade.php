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
        <p class="text-sm text-gray-500 mt-0.5" id="attStatsSubtitle">Yükleniyor...</p>
    </div>
    <div class="flex items-center gap-2">
        @can('attendance.export')
        <button onclick="exportAttendance()"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
            Excel İndir
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
.att-kpi-card { transition: all .2s ease; background: #fff; border: 1px solid #e5e7eb; }
.att-kpi-card:hover { transform: translateY(-2px); border-color: #02E0FB; box-shadow: 0 4px 12px rgba(2,224,251,.15); }
.att-filter-card { transition: all .2s ease; }
.att-filter-card:focus-within { box-shadow: 0 0 0 2px rgba(2,224,251,.15); border-color: #02E0FB; }
.qf-btn { transition: all .15s ease; }
@media (max-width: 640px) {
    .att-table thead { display: none; }
    .att-table tbody tr { display: flex; flex-direction: column; padding: 12px; border-bottom: 1px solid #f1f5f9; }
    .att-table tbody tr:last-child { border-bottom: none; }
    .att-table tbody td { display: flex; align-items: center; justify-content: space-between; padding: 6px 0; border: none; text-align: right; }
    .att-table tbody td:before { content: attr(data-label); font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; }
    .att-table tbody td:first-child { padding-top: 0; }
    .att-table tbody td:last-child { padding-bottom: 0; }
}
</style>

{{-- KPI Hero --}}
<div class="bg-gradient-to-br from-white via-gray-50 to-gray-100 rounded-2xl p-4 sm:p-6 mb-6 shadow-sm border border-gray-200/80 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-64 h-64 bg-[#02E0FB]/5 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-48 h-48 bg-cyan-500/5 rounded-full blur-3xl -ml-16 -mb-16 pointer-events-none"></div>
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
        @foreach([
            ['id'=>'kpi-present','label'=>'Bugün Mevcut','icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z','color'=>'text-emerald-600','bg'=>'bg-emerald-100','valueColor'=>'text-emerald-600'],
            ['id'=>'kpi-late','label'=>'Geç Gelen','icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z','color'=>'text-amber-600','bg'=>'bg-amber-100','valueColor'=>'text-amber-600'],
            ['id'=>'kpi-absent','label'=>'Devamsız','icon'=>'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z','color'=>'text-red-600','bg'=>'bg-red-100','valueColor'=>'text-red-600'],
            ['id'=>'kpi-incomplete','label'=>'Eksik Kayıt','icon'=>'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z','color'=>'text-orange-600','bg'=>'bg-orange-100','valueColor'=>'text-orange-600'],
            ['id'=>'kpi-overtime','label'=>'Fazla Mesai Yapan','icon'=>'M13 10V3L4 14h7v7l9-11h-7z','color'=>'text-blue-600','bg'=>'bg-blue-100','valueColor'=>'text-blue-600'],
            ['id'=>'kpi-total','label'=>'Toplam Kayıt','icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2','color'=>'text-gray-600','bg'=>'bg-gray-100','valueColor'=>'text-gray-800'],
        ] as $card)
        <div class="att-kpi-card rounded-xl px-4 py-4 cursor-default transition-all">
            <div class="flex items-center justify-between mb-2">
                <p class="text-[10px] font-semibold uppercase tracking-wider {{ $card['color'] }}">{{ $card['label'] }}</p>
                <div class="w-8 h-8 rounded-xl {{ $card['bg'] }} flex items-center justify-center">
                    <svg class="w-4 h-4 {{ $card['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/></svg>
                </div>
            </div>
            <p class="text-2xl font-black {{ $card['valueColor'] }}" id="{{ $card['id'] }}">—</p>
        </div>
        @endforeach
    </div>
</div>

{{-- Tab Navigation --}}
<div class="flex flex-wrap gap-1 border-b border-gray-200 mb-5">
    <button onclick="setAttTab('log')" id="atab-log" class="px-4 py-2.5 text-sm font-medium border-b-2 border-[#02E0FB] text-[#02E0FB] transition-all">Giriş/Çıkış Logu</button>
    <button onclick="setAttTab('daily')" id="atab-daily" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-all">Günlük Özet</button>
    <button onclick="setAttTab('monthly')" id="atab-monthly" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-all">Aylık Özet</button>
    <button onclick="setAttTab('stats')" id="atab-stats" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-all">İstatistik</button>
</div>

{{-- Quick Filters --}}
<div id="att-quick-filters" class="flex flex-wrap items-center gap-2 mb-4">
    <button onclick="setAttQuickFilter('today')" class="qf-btn px-3 py-1.5 text-xs font-medium rounded-full bg-[#02E0FB] text-white border border-[#02E0FB] transition-all" data-filter="today">Bugün</button>
    <button onclick="setAttQuickFilter('this_week')" class="qf-btn px-3 py-1.5 text-xs font-medium rounded-full border border-gray-200 text-gray-600 hover:border-[#02E0FB] hover:text-[#02E0FB] transition-all" data-filter="this_week">Bu Hafta</button>
    <button onclick="setAttQuickFilter('this_month')" class="qf-btn px-3 py-1.5 text-xs font-medium rounded-full border border-gray-200 text-gray-600 hover:border-[#02E0FB] hover:text-[#02E0FB] transition-all" data-filter="this_month">Bu Ay</button>
    <select id="attMonth" onchange="setAttTab(currentAttTab)" class="ml-2 text-xs border border-gray-200 rounded-lg px-2 py-1.5 bg-white focus:outline-none focus:border-[#02E0FB]">
        @foreach(range(1,12) as $m)
            <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>{{ \Carbon\Carbon::create(null, $m)->locale('tr')->monthName }}</option>
        @endforeach
    </select>
    <select id="attYear" onchange="setAttTab(currentAttTab)" class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 bg-white focus:outline-none focus:border-[#02E0FB]">
        @for($y = now()->year; $y >= now()->year - 2; $y--)
            <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
        @endfor
    </select>
</div>

{{-- Filters --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-5">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <div>
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Personel</label>
            <select id="attPersonel" class="att-filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
                <option value="">Tümü</option>
                @foreach($personels as $p)
                    <option value="{{ $p->id }}">{{ $p->first_name }} {{ $p->last_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Başlangıç</label>
            <input type="date" id="attDateFrom" value="{{ today()->toDateString() }}" class="att-filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
        </div>
        <div>
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Bitiş</label>
            <input type="date" id="attDateTo" value="{{ today()->toDateString() }}" class="att-filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
        </div>
        <div id="logTypeFilter">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Tür</label>
            <select id="attType" class="att-filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
                <option value="">Tümü</option>
                <option value="in">Giriş</option>
                <option value="out">Çıkış</option>
                <option value="break_start">Mola Başlangıç</option>
                <option value="break_end">Mola Bitiş</option>
            </select>
        </div>
        <div class="flex items-end">
            <button onclick="loadAttendance()" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-[#02E0FB] to-cyan-500 hover:from-cyan-500 hover:to-[#02E0FB] rounded-xl shadow-sm transition-all">Filtrele</button>
        </div>
    </div>
</div>

{{-- LOG TAB --}}
<div id="view-log">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm att-table">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Personel</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Tür</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Kayıt Zamanı</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Kaynak</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Not</th>
                        <th class="px-4 py-3.5 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">İşlem</th>
                    </tr>
                </thead>
                <tbody id="logTableBody" class="divide-y divide-gray-50">
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400 text-sm">Yükleniyor...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3.5 border-t border-gray-50 flex flex-col sm:flex-row items-center justify-between gap-2 bg-gray-50/30">
            <div class="text-xs text-gray-400 font-medium" id="logTableInfo">—</div>
            <div id="logPagination" class="flex items-center gap-1.5"></div>
        </div>
    </div>
</div>

{{-- DAILY SUMMARY TAB --}}
<div id="view-daily" class="hidden">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm att-table">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Personel</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Giriş</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Çıkış</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Net Mesai</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Gecikme</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Fazla Mesai</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Durum</th>
                    </tr>
                </thead>
                <tbody id="dailyTableBody" class="divide-y divide-gray-50">
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 text-sm">Tarih seçip filtreleyiniz...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MONTHLY SUMMARY TAB --}}
<div id="view-monthly" class="hidden">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm att-table">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Personel</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Toplam Gün</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Toplam Saat</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Gecikme</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Fazla Mesai</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Devamsızlık</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Ort. Mesai</th>
                    </tr>
                </thead>
                <tbody id="monthlyTableBody" class="divide-y divide-gray-50">
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 text-sm">Yükleniyor...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- STATISTICS TAB --}}
<div id="view-stats" class="hidden">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-800 mb-4">Günlük Durum Dağılımı</h3>
            <canvas id="attStatusChart" height="250"></canvas>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-800 mb-4">Aylık Mesai Trendi</h3>
            <canvas id="attHourChart" height="250"></canvas>
        </div>
    </div>
</div>

{{-- Manual Entry Modal --}}
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ATT_URLS = {
    list:    '{{ route("admin.attendance.list") }}',
    daily:   '{{ route("admin.attendance.daily-summary") }}',
    monthly: '{{ route("admin.attendance.monthly-summary") }}',
    store:   '{{ route("admin.attendance.record") }}',
    export:  '{{ route("admin.attendance.export") }}',
    destroy: id => `/admin/attendance/${id}`,
};

let currentAttTab = 'log';
let attStatusChart = null;
let attHourChart = null;

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('attStatsSubtitle').textContent = 'Bugünkü puantaj durumu';
    loadAttendance();
    loadAttKpis();
});

// ─── Tab Switching ───────────────────────────────────────

function setAttTab(tab) {
    ['log','daily','monthly','stats'].forEach(t => {
        const view = document.getElementById('view-' + t);
        if (view) view.classList.toggle('hidden', t !== tab);
        const btn = document.getElementById('atab-' + t);
        if (btn) {
            btn.classList.toggle('border-[#02E0FB]', t === tab);
            btn.classList.toggle('text-[#02E0FB]', t === tab);
            btn.classList.toggle('border-transparent', t !== tab);
            btn.classList.toggle('text-gray-500', t !== tab);
        }
    });
    document.getElementById('logTypeFilter').classList.toggle('hidden', tab !== 'log');
    document.getElementById('att-quick-filters').classList.toggle('hidden', tab === 'stats');
    currentAttTab = tab;
    if (tab === 'stats') { loadAttCharts(); return; }
    loadAttendance();
}

// ─── Quick Filters ───────────────────────────────────────

function setAttQuickFilter(range) {
    document.querySelectorAll('#att-quick-filters .qf-btn').forEach(b => {
        b.classList.toggle('bg-[#02E0FB]', b.dataset.filter === range);
        b.classList.toggle('text-white', b.dataset.filter === range);
        b.classList.toggle('border-[#02E0FB]', b.dataset.filter === range);
        b.classList.toggle('bg-white', b.dataset.filter !== range);
        b.classList.toggle('text-gray-600', b.dataset.filter !== range);
        b.classList.toggle('border-gray-200', b.dataset.filter !== range);
    });
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const d = String(now.getDate()).padStart(2, '0');
    let from = '', to = '';
    if (range === 'today') { from = to = `${y}-${m}-${d}`; }
    else if (range === 'this_week') {
        const day = now.getDay() || 7;
        const mon = new Date(now); mon.setDate(now.getDate() - day + 1);
        const sun = new Date(mon); sun.setDate(mon.getDate() + 6);
        from = mon.toISOString().split('T')[0]; to = sun.toISOString().split('T')[0];
    } else if (range === 'this_month') {
        from = `${y}-${m}-01`; to = `${y}-${m}-31`;
    }
    document.getElementById('attDateFrom').value = from;
    document.getElementById('attDateTo').value = to;
    loadAttendance();
}

// ─── Filters ─────────────────────────────────────────────

function getFilters() {
    return {
        personel_id: document.getElementById('attPersonel').value,
        date_from:   document.getElementById('attDateFrom').value,
        date_to:     document.getElementById('attDateTo').value,
        type:        currentAttTab === 'log' ? document.getElementById('attType').value : undefined,
    };
}

function loadAttendance(page) {
    if (currentAttTab === 'daily') loadDailySummary();
    else if (currentAttTab === 'monthly') loadMonthlySummary();
    else loadLog(page);
}

// ─── KPI ─────────────────────────────────────────────────

function loadAttKpis() {
    const today = new Date().toISOString().split('T')[0];
    axios.get(ATT_URLS.daily, { params: { date: today } }).then(res => {
        const data = res.data.data || [];
        const present = data.filter(s => s.status === 'present').length;
        const late = data.filter(s => s.status === 'late').length;
        const absent = data.filter(s => s.status === 'absent').length;
        const incomplete = data.filter(s => s.status === 'incomplete').length;
        const overtime = data.filter(s => s.overtime_minutes > 0).length;
        document.getElementById('kpi-present').textContent = present;
        document.getElementById('kpi-late').textContent = late;
        document.getElementById('kpi-absent').textContent = absent;
        document.getElementById('kpi-incomplete').textContent = incomplete;
        document.getElementById('kpi-overtime').textContent = overtime;
        document.getElementById('kpi-total').textContent = data.length;
        document.getElementById('attStatsSubtitle').textContent = `Bugün ${data.length} personel, ${present} mevcut, ${late} geç kaldı`;
    });
}

// ─── LOG VIEW ────────────────────────────────────────────

function loadLog(page = 1) {
    axios.get(ATT_URLS.list, { params: { ...getFilters(), page, per_page: 25 } }).then(res => renderLogTable(res.data));
}

const typeConfig = {
    in:          { label: 'Giriş',           bg: 'bg-green-100',  text: 'text-green-700',  dot: 'bg-green-500' },
    out:         { label: 'Çıkış',           bg: 'bg-red-100',    text: 'text-red-700',    dot: 'bg-red-500' },
    break_start: { label: 'Mola Başlangıç', bg: 'bg-yellow-100', text: 'text-yellow-700', dot: 'bg-yellow-500' },
    break_end:   { label: 'Mola Bitiş',     bg: 'bg-blue-100',   text: 'text-blue-700',   dot: 'bg-blue-500' },
};
const sourceConfig = {
    web:       { label: 'Web',      color: 'text-blue-600' },
    mobile:    { label: 'Mobil',    color: 'text-purple-600' },
    biometric: { label: 'Biyometrik', color: 'text-green-600' },
    manual:    { label: 'Manuel',   color: 'text-orange-600' },
};

function renderLogTable(data) {
    const tbody = document.getElementById('logTableBody');
    if (!data.data?.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="px-4 py-12 text-center text-gray-400 text-sm">Kayıt bulunamadı</td></tr>`;
        document.getElementById('logTableInfo').textContent = '0 kayıt';
        return;
    }
    tbody.innerHTML = data.data.map(r => {
        const tc = typeConfig[r.type] || { label: r.type, bg: 'bg-gray-100', text: 'text-gray-600', dot: 'bg-gray-400' };
        const sc = sourceConfig[r.source] || { label: r.source, color: 'text-gray-500' };
        return `<tr class="hover:bg-gray-50/80 transition-colors group">
            <td data-label="Personel" class="px-4 py-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#02E0FB] to-cyan-500 text-white text-xs font-bold flex items-center justify-center shadow-sm">
                        ${(r.personel_name || '?').split(' ').map(n=>n[0]).join('')}
                    </div>
                    <span class="font-medium text-gray-800 text-sm">${r.personel_name || '—'}</span>
                </div>
            </td>
            <td data-label="Tür" class="px-4 py-3">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ${tc.bg} ${tc.text}">
                    <span class="w-1.5 h-1.5 rounded-full ${tc.dot}"></span>
                    ${tc.label}
                </span>
            </td>
            <td data-label="Zaman" class="px-4 py-3 font-mono text-sm text-gray-800 font-medium">${r.recorded_at}</td>
            <td data-label="Kaynak" class="px-4 py-3">
                <span class="text-xs font-medium ${sc.color}">${sc.label}</span>
                ${r.is_manual ? '<span class="ml-1 text-xs text-orange-400">[MANUEL]</span>' : ''}
            </td>
            <td data-label="Not" class="px-4 py-3 text-gray-500 text-xs max-w-xs truncate">${r.note || '—'}</td>
            <td data-label="İşlem" class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-0.5 opacity-70 group-hover:opacity-100 transition-opacity">
                    <button onclick="openCorrectModal(${r.id})" class="p-1.5 text-gray-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-all" title="Düzelt">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button onclick="confirmDelete(ATT_URLS.destroy(${r.id}), () => loadLog())" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all" title="Sil">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
    document.getElementById('logTableInfo').textContent = `${data.total} kayıttan ${Math.min(data.data.length, 25)} gösteriliyor`;
    renderLogPagination(data);
}

function renderLogPagination(data) {
    const el = document.getElementById('logPagination');
    if (!el) return;
    const tp = data.pages || 1;
    let page = 1;
    const m = document.getElementById('logPagination');
    const params = new URLSearchParams(window.location.search);
    page = parseInt(params.get('page')) || 1;
    let h = '';
    if (page > 1) h += `<button onclick="loadLog(${page-1})" class="px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors">‹</button>`;
    for (let i = Math.max(1, page-2); i <= Math.min(tp, page+2); i++) {
        h += i === page
            ? `<span class="px-2.5 py-1.5 text-xs bg-[#02E0FB] text-white rounded-lg font-semibold shadow-sm">${i}</span>`
            : `<button onclick="loadLog(${i})" class="px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors">${i}</button>`;
    }
    if (page < tp) h += `<button onclick="loadLog(${page+1})" class="px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors">›</button>`;
    el.innerHTML = h;
}

// ─── DAILY SUMMARY ───────────────────────────────────────

function loadDailySummary() {
    const date = document.getElementById('attDateFrom').value;
    axios.get(ATT_URLS.daily, { params: { date, personel_id: document.getElementById('attPersonel').value } }).then(res => {
        const tbody = document.getElementById('dailyTableBody');
        const data = res.data.data || [];
        if (!data.length) {
            tbody.innerHTML = `<tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 text-sm">Bu tarihe ait kayıt bulunamadı</td></tr>`;
            return;
        }
        const statusConfig = {
            present:    { label: 'Mevcut',     bg: 'bg-emerald-50 text-emerald-700' },
            late:       { label: 'Geç Geldi',  bg: 'bg-amber-50 text-amber-700' },
            overtime:   { label: 'Fazla Mesai',bg: 'bg-blue-50 text-blue-700' },
            incomplete: { label: 'Eksik Kayıt',bg: 'bg-orange-50 text-orange-700' },
            absent:     { label: 'Devamsız',   bg: 'bg-red-50 text-red-700' },
        };
        tbody.innerHTML = data.map(s => {
            const sc = statusConfig[s.status] || { label: s.status, bg: 'bg-gray-100 text-gray-600' };
            return `<tr class="hover:bg-gray-50/80 transition-colors">
                <td data-label="Personel" class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#02E0FB] to-cyan-500 text-white text-xs font-bold flex items-center justify-center shadow-sm">
                            ${(s.personel_name || '?').split(' ').map(n=>n[0]).join('')}
                        </div>
                        <span class="font-medium text-gray-800">${s.personel_name}</span>
                    </div>
                </td>
                <td data-label="Giriş" class="px-4 py-3 text-center font-mono font-semibold ${s.check_in ? 'text-green-700' : 'text-gray-300'}">${s.check_in || '—'}</td>
                <td data-label="Çıkış" class="px-4 py-3 text-center font-mono font-semibold ${s.check_out ? 'text-red-600' : 'text-gray-300'}">${s.check_out || '—'}</td>
                <td data-label="Mesai" class="px-4 py-3 text-center font-semibold text-gray-800">${s.net_work_hours} sa</td>
                <td data-label="Gecikme" class="px-4 py-3 text-center ${s.late_minutes > 0 ? 'text-red-600 font-semibold' : 'text-gray-400'}">${s.late_minutes > 0 ? s.late_minutes + ' dk' : '—'}</td>
                <td data-label="Fazla Mesai" class="px-4 py-3 text-center ${s.overtime_minutes > 0 ? 'text-blue-600 font-semibold' : 'text-gray-400'}">${s.overtime_minutes > 0 ? Math.round(s.overtime_minutes/60*10)/10 + ' sa' : '—'}</td>
                <td data-label="Durum" class="px-4 py-3 text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ${sc.bg}">${sc.label}</span>
                    ${!s.is_pair_complete ? '<span class="ml-1 text-xs text-orange-400" title="Eşleşmemiş giriş/çıkış">⚠</span>' : ''}
                </td>
            </tr>`;
        }).join('');
    });
}

// ─── MONTHLY SUMMARY ─────────────────────────────────────

function loadMonthlySummary() {
    const year = document.getElementById('attYear')?.value || new Date().getFullYear();
    const month = document.getElementById('attMonth')?.value || (new Date().getMonth() + 1);
    const personelId = document.getElementById('attPersonel').value;
    axios.get(ATT_URLS.monthly, { params: { year, month, personel_id: personelId } }).then(res => {
        const tbody = document.getElementById('monthlyTableBody');
        const data = res.data.data || [];
        if (!data.length) {
            tbody.innerHTML = `<tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 text-sm">Bu aya ait kayıt bulunamadı</td></tr>`;
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
                <td data-label="Toplam Gün" class="px-4 py-3 text-center font-semibold text-gray-800">${s.total_work_days || 0}</td>
                <td data-label="Toplam Saat" class="px-4 py-3 text-center font-semibold text-gray-800">${s.total_work_hours || '0'} sa</td>
                <td data-label="Gecikme" class="px-4 py-3 text-center ${s.total_late_minutes > 0 ? 'text-red-600 font-semibold' : 'text-gray-400'}">${s.total_late_minutes > 0 ? s.total_late_minutes + ' dk' : '—'}</td>
                <td data-label="Fazla Mesai" class="px-4 py-3 text-center ${s.total_overtime_minutes > 0 ? 'text-blue-600 font-semibold' : 'text-gray-400'}">${s.total_overtime_minutes > 0 ? Math.round(s.total_overtime_minutes/60*10)/10 + ' sa' : '—'}</td>
                <td data-label="Devamsızlık" class="px-4 py-3 text-center ${s.absent_days > 0 ? 'text-red-600 font-semibold' : 'text-gray-400'}">${s.absent_days || 0} gün</td>
                <td data-label="Ort. Mesai" class="px-4 py-3 text-center font-semibold text-gray-800">${s.avg_work_hours || '0'} sa</td>
            </tr>`).join('');
    });
}

// ─── CHARTS ──────────────────────────────────────────────

function loadAttCharts() {
    if (attStatusChart) { attStatusChart.destroy(); attStatusChart = null; }
    if (attHourChart) { attHourChart.destroy(); attHourChart = null; }
    const today = new Date().toISOString().split('T')[0];
    axios.get(ATT_URLS.daily, { params: { date: today } }).then(res => {
        const data = res.data.data || [];
        const present = data.filter(s => s.status === 'present').length;
        const late = data.filter(s => s.status === 'late').length;
        const absent = data.filter(s => s.status === 'absent').length;
        const incomplete = data.filter(s => s.status === 'incomplete').length;
        const overtime = data.filter(s => s.status === 'overtime').length;
        const ctx = document.getElementById('attStatusChart');
        if (ctx) {
            attStatusChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Mevcut','Geç Geldi','Devamsız','Eksik Kayıt','Fazla Mesai'],
                    datasets: [{ data: [present, late, absent, incomplete, overtime], backgroundColor: ['#10b981','#f59e0b','#ef4444','#f97316','#3b82f6'], borderWidth: 2, borderColor: '#fff' }]
                },
                options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { padding: 15, usePointStyle: true, font: { size: 11 } } } } }
            });
        }
    });
    const year = document.getElementById('attYear')?.value || new Date().getFullYear();
    const month = document.getElementById('attMonth')?.value || (new Date().getMonth() + 1);
    const monthNames = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];
    const promises = Array.from({length:12}, (_, i) => {
        return axios.get(ATT_URLS.monthly, { params: { year, month: i+1, per_page: 100 } }).then(r => {
            const d = r.data.data || [];
            return d.reduce((s, p) => s + (parseFloat(p.total_work_hours) || 0), 0);
        }).catch(() => 0);
    });
    Promise.all(promises).then(hours => {
        const ctx = document.getElementById('attHourChart');
        if (ctx) {
            attHourChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: monthNames,
                    datasets: [{
                        label: 'Toplam Mesai (saat)',
                        data: hours,
                        backgroundColor: '#02E0FB',
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }
    });
}

// ─── Manual Entry ────────────────────────────────────────

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
    axios.post(ATT_URLS.store, data).then(res => {
        closeManualModal();
        toast('success', res.data.message);
        loadAttendance();
        loadAttKpis();
    }).catch(e => toast('error', e.response?.data?.message || 'Kayıt başarısız'));
}

function openCorrectModal(id) {
    document.getElementById('modalTitle').textContent = 'Kaydı Düzelt';
    document.getElementById('modalBody').innerHTML = `
        <form id="correctForm" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Yeni Tarih/Saat <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="recorded_at" required class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#02E0FB] focus:ring-1 focus:ring-[#02E0FB]/20 transition-all">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Not</label>
                <textarea name="note" rows="2" placeholder="Düzeltme gerekçesi..." class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#02E0FB] focus:ring-1 focus:ring-[#02E0FB]/20 transition-all"></textarea>
            </div>
        </form>`;
    document.getElementById('modalFooter').innerHTML = `
        <button onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">İptal</button>
        <button onclick="submitCorrect(${id})" class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-[#FA6001] to-orange-500 hover:from-orange-500 hover:to-[#FA6001] rounded-xl shadow-sm transition-all">Güncelle</button>`;
    document.getElementById('globalModal').classList.remove('hidden');
}

function submitCorrect(id) {
    const form = document.getElementById('correctForm');
    const data = Object.fromEntries(new FormData(form).entries());
    if (!data.recorded_at) { toast('warning', 'Tarih/saat zorunludur.'); return; }
    axios.patch(ATT_URLS.destroy(id).replace(`/${id}`, `/${id}/correct`), data).then(res => {
        closeModal();
        toast('success', res.data.message);
        loadLog();
    }).catch(e => toast('error', e.response?.data?.message || 'Düzeltme başarısız'));
}

function closeModal() {
    document.getElementById('globalModal').classList.add('hidden');
}

// ─── Export ──────────────────────────────────────────────

function exportAttendance() {
    const params = new URLSearchParams({
        date_from: document.getElementById('attDateFrom').value,
        date_to: document.getElementById('attDateTo').value,
        personel_id: document.getElementById('attPersonel').value,
    });
    window.open(ATT_URLS.export + '?' + params.toString(), '_blank');
}
</script>
@endpush