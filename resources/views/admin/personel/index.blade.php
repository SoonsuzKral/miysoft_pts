@extends('layouts.app')
@section('title', 'Personel Yönetimi')
@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#02E0FB] transition-colors">Dashboard</a>
    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-800 font-medium">Personel Yönetimi</span>
@endsection
@section('page_header')
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Personel Yönetimi</h1>
        <p class="text-sm text-gray-500 mt-0.5" id="statsSubtitle">Yükleniyor...</p>
    </div>
    <div class="flex items-center gap-2">
        <span id="lastRefresh" class="text-xs text-gray-400 hidden sm:inline"></span>
        <button onclick="refreshWidgets()" class="p-2 text-gray-400 hover:text-[#02E0FB] hover:bg-[#02E0FB]/10 rounded-lg transition-all" title="Yenile">
            <svg class="w-4 h-4" id="refreshIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        </button>
        @can('personel.export')
        <a href="{{ route('admin.personel.exportExcel') }}" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
            Excel
        </a>
        @endcan
        @can('personel.create')
        <button onclick="openCreateModal()" class="flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-[#02E0FB] to-cyan-500 hover:from-cyan-500 hover:to-[#02E0FB] rounded-xl shadow-md hover:shadow-lg transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Yeni Personel
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
.stat-card { transition: all .2s ease; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,.08); }
.widget-card { transition: all .2s ease; }
.widget-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,.06); }
.filter-card { transition: all .2s ease; }
.filter-card:focus-within { box-shadow: 0 0 0 2px rgba(2,224,251,.15); border-color: #02E0FB; }
@media (max-width: 640px) {
    .personel-table thead { display: none; }
    .personel-table tbody tr { display: flex; flex-direction: column; padding: 12px; border-bottom: 1px solid #f1f5f9; border-radius: 0; }
    .personel-table tbody tr:last-child { border-bottom: none; }
    .personel-table tbody td { display: flex; align-items: center; justify-content: space-between; padding: 6px 0; border: none; text-align: right; }
    .personel-table tbody td:before { content: attr(data-label); font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; }
    .personel-table tbody td:first-child { padding-top: 0; }
    .personel-table tbody td:last-child { padding-bottom: 0; }
    .personel-table tbody td[data-label="İşlemler"] { justify-content: flex-end; gap: 4px; }
}
</style>

{{-- Stats Hero --}}
<div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl p-6 mb-6 shadow-xl border border-gray-700/50 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-64 h-64 bg-[#02E0FB]/5 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-48 h-48 bg-cyan-500/5 rounded-full blur-3xl -ml-16 -mb-16 pointer-events-none"></div>
    <div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-8 gap-3" id="statsBar">
        <div class="stat-card bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 px-3 py-3 cursor-default"><p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Toplam</p><p class="text-xl font-black text-white">—</p></div>
        <div class="stat-card bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 px-3 py-3 cursor-default"><p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Aktif</p><p class="text-xl font-black text-emerald-400">—</p></div>
        <div class="stat-card bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 px-3 py-3 cursor-default"><p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">İzinde</p><p class="text-xl font-black text-yellow-400">—</p></div>
        <div class="stat-card bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 px-3 py-3 cursor-default"><p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Raporlu</p><p class="text-xl font-black text-orange-400">—</p></div>
        <div class="stat-card bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 px-3 py-3 cursor-default"><p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Kadın</p><p class="text-xl font-black text-pink-400">—</p></div>
        <div class="stat-card bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 px-3 py-3 cursor-default"><p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Erkek</p><p class="text-xl font-black text-cyan-400">—</p></div>
        <div class="stat-card bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 px-3 py-3 cursor-default"><p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Yeni (Ay)</p><p class="text-xl font-black text-emerald-400">—</p></div>
        <div class="stat-card bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 px-3 py-3 cursor-default"><p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Ort. Yaş</p><p class="text-xl font-black text-blue-400">—</p></div>
    </div>
</div>

{{-- Widget Row --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="widget-card bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-pink-50 flex items-center justify-center"><svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div>
                <h3 class="text-sm font-bold text-gray-800">Cinsiyet Dağılımı</h3>
            </div>
            <span class="text-[10px] font-semibold text-gray-400 bg-gray-50 px-2 py-1 rounded-full" id="genderTotal">—</span>
        </div>
        <div class="flex items-center gap-5">
            <div class="shrink-0" style="width:100px;height:100px;position:relative"><canvas id="genderChart"></canvas></div>
            <div class="flex-1 space-y-2.5 text-xs" id="genderLegend"></div>
        </div>
    </div>
    <div class="widget-card bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center"><svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></div>
            <h3 class="text-sm font-bold text-gray-800">Durum Dağılımı</h3>
        </div>
        <div id="statusList" class="space-y-3"></div>
    </div>
    <div class="widget-card bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-xl bg-emerald-50 flex items-center justify-center"><svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg></div>
            <h3 class="text-sm font-bold text-gray-800">Son Eklenen Personeller</h3>
        </div>
        <div id="recentPersonels" class="space-y-1">
            <div class="skeleton-row"><div class="skeleton-avatar"></div><div class="skeleton-lines"><div class="skeleton-line w-3/4"></div><div class="skeleton-line w-1/2"></div></div></div>
        </div>
    </div>
</div>

{{-- Filter + Table Card --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-6">
    <div class="px-5 pt-5 pb-3 border-b border-gray-50">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Departman</label>
                <select id="filterDept" class="filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
                    <option value="">Tümü</option>
                    @foreach($departments ?? [] as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Durum</label>
                <select id="filterStatus" class="filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
                    <option value="">Tümü</option>
                    <option value="active">Aktif</option>
                    <option value="terminated">Ayrılmış</option>
                    <option value="on_leave">İzinde</option>
                    <option value="suspended">Askıda</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Arama</label>
                <input type="text" id="filterSearch" placeholder="Ad, soyad veya e-posta..." class="filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
            </div>
            <div class="flex items-end gap-2">
                <button onclick="reloadTable()" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-[#02E0FB] to-cyan-500 hover:from-cyan-500 hover:to-[#02E0FB] rounded-xl shadow-sm transition-all">Filtrele</button>
                <button onclick="resetFilters()" class="px-3 py-2.5 text-sm text-gray-400 hover:text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-xl transition-all" title="Sıfırla">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm personel-table">
            <thead>
                <tr class="bg-gray-50/50">
                    <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Personel</th>
                    <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Departman</th>
                    <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Pozisyon</th>
                    <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">İşe Giriş</th>
                    <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Durum</th>
                    <th class="px-4 py-3.5 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">İşlemler</th>
                </tr>
            </thead>
            <tbody id="personelTableBody" class="divide-y divide-gray-50">
                <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400 text-sm">Yükleniyor...</td></tr>
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3.5 border-t border-gray-50 flex flex-col sm:flex-row items-center justify-between gap-2 bg-gray-50/30">
        <div class="text-xs text-gray-400 font-medium" id="tableInfo">— kayıt gösteriliyor</div>
        <div class="flex items-center gap-1.5" id="tablePagination"></div>
    </div>
</div>

{{-- Personel Detay Kartı --}}
<div id="personelCardArea" class="mt-5 hidden"></div>

{{-- Global Modal --}}
<div id="globalModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div id="modalBackdrop" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto border border-gray-100">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <h2 id="modalTitle" class="text-lg font-bold text-gray-900">Başlık</h2>
            <button id="modalClose" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
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
<script src="{{ asset('js/admin/personel.js') }}"></script>
<script>
const WIDGET_URL = '{{ route("admin.personel.widgets") }}';
let genderChart;

document.addEventListener('DOMContentLoaded', () => {
    if (window.PERSONEL_CONFIG) {
        window.PERSONEL_CONFIG.list = '{{ route("admin.personel.list") }}';
        window.PERSONEL_CONFIG.create = '{{ route("admin.personel.create") }}';
        window.PERSONEL_CONFIG.store = '{{ route("admin.personel.store") }}';
        window.PERSONEL_CONFIG.exportExcel = '{{ route("admin.personel.exportExcel") }}';
    }
    loadWidgets();
    document.getElementById('lastRefresh').textContent = 'Son: ' + new Date().toLocaleTimeString('tr-TR');
});

function loadWidgets() {
    axios.get(WIDGET_URL).then(res => {
        const d = res.data;
        const stats = document.getElementById('statsBar').children;
        if (stats.length >= 8) {
            stats[0].querySelector('.text-white').textContent = d.total;
            stats[1].querySelector('.text-emerald-400').textContent = d.active;
            stats[2].querySelector('.text-yellow-400').textContent = d.today_on_leave;
            stats[3].querySelector('.text-orange-400').textContent = d.today_sick;
            stats[4].querySelector('.text-pink-400').textContent = d.female;
            stats[5].querySelector('.text-cyan-400').textContent = d.male;
            stats[6].querySelector('.text-emerald-400').textContent = '+' + d.hired_this_month;
            stats[7].querySelector('.text-blue-400').textContent = d.avg_age;
        }
        document.getElementById('statsSubtitle').textContent = d.total + ' personel kaydı bulunuyor • ' + d.active + ' aktif';
        renderGenderChart(d.gender_stats);
        renderStatusList(d.status_stats);
        renderRecentPersonels(d.recent_personels);
    });
}

function refreshWidgets() {
    const icon = document.getElementById('refreshIcon');
    icon.classList.add('animate-spin');
    loadWidgets();
    document.getElementById('lastRefresh').textContent = 'Son: ' + new Date().toLocaleTimeString('tr-TR');
    setTimeout(() => icon.classList.remove('animate-spin'), 800);
}

function renderGenderChart(stats) {
    const canvas = document.getElementById('genderChart');
    if (!canvas) return;
    if (genderChart) genderChart.destroy();
    const labels = { M: 'Erkek', F: 'Kadın', other: 'Diğer' };
    const colors = { M: '#02E0FB', F: '#ec4899', other: '#94a3b8' };
    const filtered = (stats || []).filter(s => s.gender);
    if (!filtered.length) { canvas.parentElement.innerHTML = '<p class="text-center text-gray-400 text-sm py-4">Veri yok</p>'; return; }
    genderChart = new Chart(canvas, {
        type: 'doughnut',
        data: { labels: filtered.map(s => labels[s.gender] || s.gender), datasets: [{ data: filtered.map(s => s.count), backgroundColor: filtered.map(s => colors[s.gender] || '#94a3b8'), borderWidth: 0 }] },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '72%',
            plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(15,23,42,.9)', titleColor: '#fff', bodyColor: '#94a3b8', padding: 8, cornerRadius: 8 } }
        }
    });
    const legend = document.getElementById('genderLegend');
    const total = filtered.reduce((s, v) => s + v.count, 0);
    document.getElementById('genderTotal').textContent = total + ' kişi';
    legend.innerHTML = filtered.map(s =>
        '<div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:' + (colors[s.gender] || '#94a3b8') + '"></span>' +
        '<span class="text-gray-600">' + (labels[s.gender] || s.gender) + '</span>' +
        '<span class="font-bold text-gray-800 ml-auto">' + s.count + '</span>' +
        '<span class="text-gray-400">%' + Math.round(s.count/total*100) + '</span></div>'
    ).join('');
}

function renderStatusList(stats) {
    const el = document.getElementById('statusList');
    if (!stats?.length) { el.innerHTML = '<p class="text-center text-gray-400 text-sm py-4">Veri yok</p>'; return; }
    const total = stats.reduce((s, v) => s + v.count, 0) || 1;
    const colors = { active: '#10b981', on_leave: '#f59e0b', terminated: '#ef4444', suspended: '#8b5cf6' };
    const icons = { active: '✅', on_leave: '📅', terminated: '🚪', suspended: '⏸️' };
    const labels = { active: 'Aktif', on_leave: 'İzinde', terminated: 'Ayrılmış', suspended: 'Askıda' };
    const maxCount = Math.max(...stats.map(s => s.count), 1);
    el.innerHTML = stats.map(s =>
        '<div class="flex items-center gap-3">' +
            '<span class="text-sm shrink-0">' + (icons[s.status] || '📋') + '</span>' +
            '<span class="text-xs text-gray-600 w-16 font-medium shrink-0">' + (labels[s.status] || s.status) + '</span>' +
            '<div class="flex-1 h-2.5 bg-gray-100 rounded-full overflow-hidden">' +
                '<div class="h-full rounded-full transition-all duration-700" style="width:' + Math.round(s.count/maxCount*100) + '%;background:' + (colors[s.status] || '#94a3b8') + '"></div>' +
            '</div>' +
            '<span class="text-xs font-bold text-gray-800 w-8 text-right">' + s.count + '</span>' +
            '<span class="text-[10px] text-gray-400 w-10 text-right">%' + Math.round(s.count/total*100) + '</span>' +
        '</div>'
    ).join('');
}

function renderRecentPersonels(personels) {
    const el = document.getElementById('recentPersonels');
    if (!el) return;
    if (!personels?.length) { el.innerHTML = '<p class="text-center text-gray-400 py-4 text-sm">Henüz personel kaydı yok</p>'; return; }
    el.innerHTML = personels.map(p => {
        const initials = (p.first_name?.[0] || '') + (p.last_name?.[0] || '');
        return '<div class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 rounded-xl transition-all px-2 -mx-2">' +
            '<div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#FA6001] to-orange-400 text-white font-bold text-xs flex items-center justify-center shadow-sm shrink-0">' + initials + '</div>' +
            '<div class="flex-1 min-w-0"><p class="text-sm font-semibold text-gray-800 truncate">' + (p.first_name || '') + ' ' + (p.last_name || '') + '</p>' +
            '<p class="text-[11px] text-gray-400">' + (p.dept_name || '—') + (p.pos_title ? ' · ' + p.pos_title : '') + '</p></div>' +
            '<span class="text-[11px] text-gray-400 shrink-0 bg-gray-50 px-2 py-1 rounded-full">' + (p.created_at || '') + '</span></div>';
    }).join('');
}

function resetFilters() {
    document.getElementById('filterDept').value = '';
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterSearch').value = '';
    reloadTable();
}
</script>
@endpush
