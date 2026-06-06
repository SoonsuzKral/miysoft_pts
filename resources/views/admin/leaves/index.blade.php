@extends('layouts.app')
@section('title', 'İzin Yönetimi')
@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#02E0FB] transition-colors">Dashboard</a>
    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-800 font-medium">İzin Yönetimi</span>
@endsection
@section('page_header')
    <div>
        <h1 class="text-2xl font-bold text-gray-900">İzin Yönetimi</h1>
        <p class="text-sm text-gray-500 mt-0.5" id="statsSubtitle">Yükleniyor...</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.leave.types.index') }}"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
            <svg class="w-4 h-4 text-[#02E0FB]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h7"/></svg>
            İzin Türleri
        </a>
        <a href="{{ route('admin.leave.balances') }}"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
            <svg class="w-4 h-4 text-[#FA6001]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Bakiyeler
        </a>
        <button onclick="exportLeaves('excel')"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
            Excel
        </button>
        <button onclick="exportLeaves('pdf')"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            PDF
        </button>
        @can('leave.request')
        <button onclick="openCreateLeaveModal()"
            class="flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-[#02E0FB] to-cyan-500 hover:from-cyan-500 hover:to-[#02E0FB] rounded-xl shadow-md hover:shadow-lg transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Yeni Talep
        </button>
        @endcan
    </div>
@endsection
@section('content')

<style>
.animate-scale-in { animation: scaleIn .25s ease-out; }
@keyframes scaleIn { from { opacity: 0; transform: scale(0.95) translateY(-10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
.animate-slide-in { animation: slideIn .3s ease-out; }
@keyframes slideIn { from { opacity: 0; transform: translateX(40px); } to { opacity: 1; transform: translateX(0); } }
.kpi-card { transition: all .2s ease; }
.kpi-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,.08); }
.filter-card { transition: all .2s ease; }
.filter-card:focus-within { box-shadow: 0 0 0 2px rgba(2,224,251,.15); border-color: #02E0FB; }

/* light mode KPI cards */
.kpi-light-card { background: #fff; border: 1px solid #e5e7eb; }
.kpi-light-card:hover { border-color: #02E0FB; box-shadow: 0 4px 12px rgba(2,224,251,.15); }

@media (max-width: 640px) {
    .leave-table thead { display: none; }
    .leave-table tbody tr { display: flex; flex-direction: column; padding: 12px; border-bottom: 1px solid #f1f5f9; }
    .leave-table tbody tr:last-child { border-bottom: none; }
    .leave-table tbody td { display: flex; align-items: center; justify-content: space-between; padding: 6px 0; border: none; text-align: right; }
    .leave-table tbody td:before { content: attr(data-label); font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; }
    .leave-table tbody td:first-child { padding-top: 0; }
    .leave-table tbody td:last-child { padding-bottom: 0; }
    .leave-table tbody td[data-label="İşlemler"] { justify-content: flex-end; gap: 2px; }
    .kpi-hero-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>

{{-- KPI Hero - Light mode friendly & responsive --}}
<div class="bg-gradient-to-br from-white via-gray-50 to-gray-100 rounded-2xl p-4 sm:p-6 mb-6 shadow-sm border border-gray-200/80 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-64 h-64 bg-[#02E0FB]/5 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-48 h-48 bg-cyan-500/5 rounded-full blur-3xl -ml-16 -mb-16 pointer-events-none"></div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 kpi-hero-grid">
        @foreach([
            ['id'=>'kpi-pending','label'=>'Bekleyen','icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z','color'=>'text-amber-600','bg'=>'bg-amber-100','textlabel'=>'text-amber-600','valueColor'=>'text-amber-600'],
            ['id'=>'kpi-approved','label'=>'Onaylanan (Bu Ay)','icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z','color'=>'text-emerald-600','bg'=>'bg-emerald-100','textlabel'=>'text-emerald-600','valueColor'=>'text-emerald-600'],
            ['id'=>'kpi-rejected','label'=>'Reddedilen','icon'=>'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z','color'=>'text-red-600','bg'=>'bg-red-100','textlabel'=>'text-red-600','valueColor'=>'text-red-600'],
            ['id'=>'kpi-cancelled','label'=>'İptal Edilen','icon'=>'M6 18L18 6M6 6l12 12','color'=>'text-gray-500','bg'=>'bg-gray-100','textlabel'=>'text-gray-500','valueColor'=>'text-gray-600'],
            ['id'=>'kpi-total-pending','label'=>'Toplam Talep','icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2','color'=>'text-blue-600','bg'=>'bg-blue-100','textlabel'=>'text-blue-600','valueColor'=>'text-blue-600'],
            ['id'=>'kpi-used-days','label'=>'Toplam Kullanılan Gün','icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z','color'=>'text-purple-600','bg'=>'bg-purple-100','textlabel'=>'text-purple-600','valueColor'=>'text-purple-600'],
            ['id'=>'kpi-avg-days','label'=>'Ort. İzin Süresi','icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z','color'=>'text-cyan-600','bg'=>'bg-cyan-100','textlabel'=>'text-cyan-600','valueColor'=>'text-cyan-600'],
            ['id'=>'kpi-balance-alert','label'=>'Düşük Bakiye Uyarısı','icon'=>'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z','color'=>'text-orange-600','bg'=>'bg-orange-100','textlabel'=>'text-orange-600','valueColor'=>'text-orange-600'],
        ] as $card)
        <div class="kpi-card kpi-light-card rounded-xl px-4 py-4 cursor-default transition-all">
            <div class="flex items-center justify-between mb-2">
                <p class="text-[10px] font-semibold uppercase tracking-wider {{ $card['textlabel'] }}">{{ $card['label'] }}</p>
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
    <button onclick="setLeaveTab('list')" id="ltab-list" class="px-4 py-2.5 text-sm font-medium border-b-2 border-[#02E0FB] text-[#02E0FB] transition-all">Talepler</button>
    <button onclick="setLeaveTab('calendar')" id="ltab-calendar" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-all">Takvim</button>
    <button onclick="setLeaveTab('chart')" id="ltab-chart" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-all">İstatistik</button>
</div>

{{-- QUICK FILTERS --}}
<div id="list-filters" class="flex flex-wrap items-center gap-2 mb-4">
    <button onclick="setQuickFilter('all')" class="qf-btn px-3 py-1.5 text-xs font-medium rounded-full border transition-all" data-filter="all">Tümü</button>
    <button onclick="setQuickFilter('this_week')" class="qf-btn px-3 py-1.5 text-xs font-medium rounded-full border border-gray-200 text-gray-600 hover:border-[#02E0FB] hover:text-[#02E0FB] transition-all" data-filter="this_week">Bu Hafta</button>
    <button onclick="setQuickFilter('this_month')" class="qf-btn px-3 py-1.5 text-xs font-medium rounded-full border border-gray-200 text-gray-600 hover:border-[#02E0FB] hover:text-[#02E0FB] transition-all" data-filter="this_month">Bu Ay</button>
    <button onclick="setQuickFilter('this_year')" class="qf-btn px-3 py-1.5 text-xs font-medium rounded-full border border-gray-200 text-gray-600 hover:border-[#02E0FB] hover:text-[#02E0FB] transition-all" data-filter="this_year">Bu Yıl</button>
    <select id="filterYear" onchange="applyDateFilter()" class="ml-2 text-xs border border-gray-200 rounded-lg px-2 py-1.5 bg-white focus:outline-none focus:border-[#02E0FB]">
        @for($y = now()->year; $y >= now()->year - 3; $y--)
            <option value="{{ $y }}">{{ $y }}</option>
        @endfor
    </select>
</div>

{{-- Filtreler --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-5">
    <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
        <div>
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Personel</label>
            <select id="filterPersonel" class="filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
                <option value="">Tümü</option>
                @foreach($personels as $p)
                    <option value="{{ $p->id }}">{{ $p->first_name }} {{ $p->last_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">İzin Türü</label>
            <select id="filterLeaveType" class="filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
                <option value="">Tümü</option>
                @foreach($leaveTypes as $lt)
                    <option value="{{ $lt->id }}">{{ $lt->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Durum</label>
            <select id="filterStatus" class="filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
                <option value="">Tümü</option>
                <option value="pending">Bekleyen</option>
                <option value="approved">Onaylanan</option>
                <option value="rejected">Reddedilen</option>
                <option value="cancelled">İptal</option>
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Başlangıç</label>
            <input type="date" id="filterDateFrom" class="filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
        </div>
        <div>
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Bitiş</label>
            <input type="date" id="filterDateTo" class="filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
        </div>
        <div>
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Arama</label>
            <input type="text" id="filterSearch" placeholder="Personel ara..." class="filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
        </div>
    </div>
</div>

{{-- TALEPLER TAB --}}
<div id="view-list">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm leave-table">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Personel / İzin Türü</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Tarih Aralığı</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Gün</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Durum</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Onaylayan</th>
                        <th class="px-4 py-3.5 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">İşlemler</th>
                    </tr>
                </thead>
                <tbody id="leaveTableBody" class="divide-y divide-gray-50">
                    <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400 text-sm">Yükleniyor...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3.5 border-t border-gray-50 flex items-center justify-between bg-gray-50/30">
            <div class="text-xs text-gray-400 font-medium" id="leaveTableInfo">—</div>
            <div id="leavePagination" class="flex items-center gap-1.5"></div>
        </div>
    </div>
</div>

{{-- TAKVİM TAB --}}
<div id="view-calendar" class="hidden">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4">
        <div class="flex flex-wrap items-center gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Personel</label>
                <select id="calPersonelFilter" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#02E0FB]" onchange="calendar?.refetchEvents()">
                    <option value="">Tümü</option>
                    @foreach($personels as $p)
                        <option value="{{ $p->id }}">{{ $p->first_name }} {{ $p->last_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">İzin Türü</label>
                <select id="calTypeFilter" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#02E0FB]" onchange="calendar?.refetchEvents()">
                    <option value="">Tümü</option>
                    @foreach($leaveTypes as $lt)
                        <option value="{{ $lt->id }}">{{ $lt->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <div id="leaveCalendar" style="min-height: 550px;"></div>
    </div>
</div>

{{-- İSTATİSTİK TAB --}}
<div id="view-chart" class="hidden">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-800 mb-4">İzin Türü Dağılımı</h3>
            <canvas id="leaveTypeChart" height="250"></canvas>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-800 mb-4">Aylık İzin Trendi</h3>
            <canvas id="leaveTrendChart" height="250"></canvas>
        </div>
    </div>
</div>

{{-- Onay/Ret Modal --}}
<div id="approvalModal" class="hidden fixed inset-0 z-[110] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeApprovalModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 p-6 border border-gray-100 animate-scale-in">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900" id="approvalModalTitle">İzin Onayla</h3>
            <button onclick="closeApprovalModal()" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="approvalModalContent"></div>
        <div class="flex justify-end gap-3 mt-5 pt-4 border-t border-gray-100">
            <button onclick="closeApprovalModal()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">İptal</button>
            <button id="approvalModalBtn" class="px-5 py-2 text-sm font-semibold text-white rounded-xl shadow-sm transition-all"></button>
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.11/locales/tr.global.min.js"></script>
<script src="{{ asset('js/admin/leave.js') }}"></script>
<script>
document.body.dataset.page = 'leaves';

// Override KPI loader — tek API çağrısı ile tüm KPI'ları doldur
window.loadKpiCounts = function() {
    const year = new Date().getFullYear();
    axios.get('{{ route("admin.leave.list") }}', { params: { per_page: 500, date_from: year + '-01-01', date_to: year + '-12-31' } })
        .then(res => {
            const all = res.data.data || [];
            const total = res.data.total || all.length;
            const statusCount = (s) => all.filter(r => r.status === s).length;
            const approved = all.filter(r => r.status === 'approved');
            const totalUsedDays = approved.reduce((s, r) => s + (Number(r.total_days) || 0), 0);
            const avgDays = approved.length > 0 && totalUsedDays > 0 ? (totalUsedDays / approved.length).toFixed(1) : '0';

            document.getElementById('kpi-pending').textContent = statusCount('pending');
            document.getElementById('kpi-approved').textContent = statusCount('approved');
            document.getElementById('kpi-rejected').textContent = statusCount('rejected');
            document.getElementById('kpi-cancelled').textContent = statusCount('cancelled');
            document.getElementById('kpi-total-pending').textContent = total;
            document.getElementById('kpi-used-days').textContent = totalUsedDays + ' gün';
            document.getElementById('kpi-avg-days').textContent = avgDays + ' gün';
            document.getElementById('kpi-balance-alert').textContent = statusCount('pending') > 0 ? statusCount('pending') + ' adet' : 'Yok';
        });
};

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('statsSubtitle').textContent = 'Tüm izin talepleri';
});

// ─── Tab Switching ──────────────────────────────────────

function setLeaveTab(tab) {
    ['list','calendar','chart'].forEach(t => {
        const view = document.getElementById('view-' + t);
        if (view) view.classList.toggle('hidden', t !== tab);
        const btn = document.getElementById('ltab-' + t);
        if (btn) {
            btn.classList.toggle('border-[#02E0FB]', t === tab);
            btn.classList.toggle('text-[#02E0FB]', t === tab);
            btn.classList.toggle('border-transparent', t !== tab);
            btn.classList.toggle('text-gray-500', t !== tab);
        }
    });
    document.getElementById('list-filters').classList.toggle('hidden', tab !== 'list');
    if (tab === 'calendar' && !window.leaveCalendar) initLeaveCalendar();
    if (tab === 'chart') loadLeaveCharts();
}

// ─── Quick Filters ──────────────────────────────────────

function setQuickFilter(range) {
    document.querySelectorAll('.qf-btn').forEach(b => {
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
    const today = `${y}-${m}-${d}`;

    let from = '', to = '';
    if (range === 'this_week') {
        const day = now.getDay() || 7;
        const mon = new Date(now);
        mon.setDate(now.getDate() - day + 1);
        const sun = new Date(mon);
        sun.setDate(mon.getDate() + 6);
        from = mon.toISOString().split('T')[0];
        to = sun.toISOString().split('T')[0];
    } else if (range === 'this_month') {
        from = `${y}-${m}-01`;
        to = today;
    } else if (range === 'this_year') {
        from = `${y}-01-01`;
        to = `${y}-12-31`;
    }
    document.getElementById('filterDateFrom').value = from;
    document.getElementById('filterDateTo').value = to;
    loadLeaveRequests();
}

function applyDateFilter() {
    loadLeaveRequests();
}

// ─── FullCalendar ───────────────────────────────────────

let leaveCalendar = null;

function initLeaveCalendar() {
    const el = document.getElementById('leaveCalendar');
    if (!el) return;
    leaveCalendar = new FullCalendar.Calendar(el, {
        locale: 'tr',
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek',
        },
        height: 600,
        editable: false,
        eventSources: [{
            url: '{{ route("admin.leave.calendar") }}',
            extraParams: () => ({
                personel_id: document.getElementById('calPersonelFilter')?.value || '',
                leave_type_id: document.getElementById('calTypeFilter')?.value || '',
            }),
            failure: () => toast('error', 'Takvim yüklenemedi.'),
        }],
        eventClick: info => {
            const e = info.event;
            Swal.fire({
                title: e.title,
                html: `
                    <div class="text-left text-sm space-y-2">
                        <p><span class="text-gray-400">Tarih:</span> <strong>${e.startStr}</strong></p>
                        <p><span class="text-gray-400">İzin Türü:</span> <strong>${e.extendedProps.leave_type || '—'}</strong></p>
                        <p><span class="text-gray-400">Gün:</span> <strong>${e.extendedProps.total_days || '—'}</strong></p>
                        <p><span class="text-gray-400">Durum:</span> <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">Onaylı</span></p>
                    </div>`,
                confirmButtonText: 'Kapat',
                confirmButtonColor: '#6B7280',
            });
        },
    });
    leaveCalendar.render();
}

// ─── Chart.js ───────────────────────────────────────────

let leaveTypeChart = null;
let leaveTrendChart = null;

function loadLeaveCharts() {
    if (leaveTypeChart) { leaveTypeChart.destroy(); leaveTypeChart = null; }
    if (leaveTrendChart) { leaveTrendChart.destroy(); leaveTrendChart = null; }

    const year = document.getElementById('filterYear')?.value || new Date().getFullYear();

    axios.get('{{ route("admin.leave.list") }}', { params: { per_page: 500, date_from: year + '-01-01', date_to: year + '-12-31' } })
        .then(res => {
            const data = res.data.data || [];
            const typeMap = {};
            const monthMap = {};
            for (let m = 0; m < 12; m++) monthMap[m] = 0;

            data.forEach(r => {
                const tn = r.leave_type?.name || 'Bilinmeyen';
                typeMap[tn] = (typeMap[tn] || 0) + 1;
                if (r.start_date) {
                    const mo = new Date(r.start_date).getMonth();
                    monthMap[mo] = (monthMap[mo] || 0) + 1;
                }
            });

            const colors = ['#02E0FB','#FA6001','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4'];

            const typeCtx = document.getElementById('leaveTypeChart');
            if (typeCtx && Object.keys(typeMap).length) {
                leaveTypeChart = new Chart(typeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(typeMap),
                        datasets: [{
                            data: Object.values(typeMap),
                            backgroundColor: Object.keys(typeMap).map((_, i) => colors[i % colors.length]),
                            borderWidth: 2,
                            borderColor: '#fff',
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'bottom', labels: { padding: 15, usePointStyle: true, font: { size: 11 } } }
                        }
                    }
                });
            }

            const trendCtx = document.getElementById('leaveTrendChart');
            if (trendCtx) {
                const monthNames = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];
                leaveTrendChart = new Chart(trendCtx, {
                    type: 'bar',
                    data: {
                        labels: monthNames,
                        datasets: [{
                            label: 'İzin Talebi',
                            data: Array.from({length:12}, (_,i) => monthMap[i] || 0),
                            backgroundColor: '#02E0FB',
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { stepSize: 1 } }
                        }
                    }
                });
            }
        }).catch(() => {});
}

</script>
@endpush
