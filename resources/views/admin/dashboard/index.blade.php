@extends('layouts.app')
@section('title', 'Dashboard')
@section('page_header')
    <div>
        <h1 class="text-2xl font-bold text-gray-900" id="greeting">Yükleniyor...</h1>
        <p class="text-sm text-gray-500 mt-0.5" id="clock">{{ now()->locale('tr')->isoFormat('dddd, D MMMM Y') }}</p>
    </div>
    <div class="flex items-center gap-3">
        <span id="lastRefresh" class="text-xs text-gray-400"></span>
        <button onclick="refreshDashboard()" class="p-2 text-gray-400 hover:text-[#02E0FB] hover:bg-[#02E0FB]/10 rounded-lg transition-colors" title="Yenile">
            <svg class="w-4 h-4" id="refreshIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
        </button>
    </div>
@endsection
@section('content')
<div id="alertBanner" class="mb-4 hidden">
    <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex items-center gap-3 text-sm">
        <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <span id="alertText"></span>
        <button onclick="this.parentElement.remove()" class="ml-auto text-amber-400 hover:text-amber-600">&times;</button>
    </div>
</div>

<div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-8 gap-3 mb-6" id="kpiGrid">
    <div class="kpi-card" data-target="total_personel" data-suffix="">
        <div class="flex items-center gap-2 mb-2">
            <div class="kpi-icon bg-cyan-50 text-[#02E0FB]"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
            <span class="kpi-label">Personel</span>
        </div>
        <p class="kpi-value" id="kpi-total">—</p>
        <div class="flex items-center gap-1.5"><span class="kpi-badge kpi-badge-green" id="kpi-hired">+0</span><span class="kpi-sub">bu ay</span></div>
        <svg class="sparkline" id="spark-total" width="100%" height="24" viewBox="0 0 120 24"></svg>
    </div>
    <div class="kpi-card" data-target="today_checkins" data-suffix="">
        <div class="flex items-center gap-2 mb-2">
            <div class="kpi-icon bg-green-50 text-green-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg></div>
            <span class="kpi-label">Giriş</span>
        </div>
        <p class="kpi-value" id="kpi-checkins">—</p>
        <div class="flex items-center gap-1.5"><span class="kpi-badge kpi-badge-red" id="kpi-absent">-0</span><span class="kpi-sub">devamsız</span></div>
        <svg class="sparkline" id="spark-checkins" width="100%" height="24" viewBox="0 0 120 24"></svg>
    </div>
    <div class="kpi-card" data-target="today_on_leave" data-suffix="">
        <div class="flex items-center gap-2 mb-2">
            <div class="kpi-icon bg-yellow-50 text-yellow-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
            <span class="kpi-label">İzinde</span>
        </div>
        <p class="kpi-value text-yellow-600" id="kpi-leave">—</p>
        <div class="flex items-center gap-1.5"><span class="kpi-badge" id="kpi-onleave-pct">%0</span><span class="kpi-sub">toplam</span></div>
    </div>
    <div class="kpi-card cursor-pointer" onclick="window.location='{{ route('admin.leave.index') }}'" data-target="total_pending" data-suffix="">
        <div class="flex items-center gap-2 mb-2">
            <div class="kpi-icon bg-orange-50 text-[#FA6001]"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <span class="kpi-label">Bekleyen</span>
            <span class="w-2 h-2 rounded-full bg-[#FA6001] animate-pulse ml-auto" id="pendingDot"></span>
        </div>
        <p class="kpi-value" id="kpi-pending">—</p>
        <div class="flex items-center gap-1.5"><span class="kpi-badge kpi-badge-orange" id="kpi-pending-amount">₺0</span><span class="kpi-sub">bekleyen</span></div>
    </div>
    <div class="kpi-card cursor-pointer" onclick="window.location='{{ route('admin.assets.index') }}'" data-target="available_assets" data-suffix="">
        <div class="flex items-center gap-2 mb-2">
            <div class="kpi-icon bg-blue-50 text-blue-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
            <span class="kpi-label">Envanter</span>
        </div>
        <p class="kpi-value text-blue-600" id="kpi-assets">—</p>
        <div class="flex items-center gap-1.5"><span class="kpi-badge kpi-badge-blue" id="kpi-assigned-pct">%0</span><span class="kpi-sub">zimmetli</span></div>
        <svg class="sparkline" id="spark-assets" width="100%" height="24" viewBox="0 0 120 24"></svg>
    </div>
    <div class="kpi-card" data-target="warranty_expiring_assets" data-suffix="">
        <div class="flex items-center gap-2 mb-2">
            <div class="kpi-icon bg-red-50 text-red-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
            <span class="kpi-label">Garanti</span>
        </div>
        <p class="kpi-value text-red-500" id="kpi-warranty">—</p>
        <div class="flex items-center gap-1.5"><span class="kpi-badge text-red-500 bg-red-50">30 gün</span><span class="kpi-sub">içinde</span></div>
    </div>
    <div class="kpi-card" data-target="today_overtime_hours" data-suffix="h">
        <div class="flex items-center gap-2 mb-2">
            <div class="kpi-icon bg-purple-50 text-purple-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <span class="kpi-label">Mesai</span>
        </div>
        <p class="kpi-value text-purple-600" id="kpi-overtime">—</p>
        <div class="flex items-center gap-1.5"><span class="kpi-badge kpi-badge-purple" id="kpi-overtime-pending">0</span><span class="kpi-sub">onay bekliyor</span></div>
    </div>
    <div class="kpi-card cursor-pointer" onclick="window.location='{{ route('admin.processes.index') }}'" data-target="active_processes" data-suffix="">
        <div class="flex items-center gap-2 mb-2">
            <div class="kpi-icon bg-pink-50 text-pink-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg></div>
            <span class="kpi-label">Süreç</span>
        </div>
        <p class="kpi-value text-pink-600" id="kpi-processes">—</p>
        <div class="flex items-center gap-1.5"><span class="kpi-badge kpi-badge-pink" id="kpi-process-pct">%0</span><span class="kpi-sub">tamamlanma</span></div>
    </div>
</div>

<div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-3 mb-6" id="pendingStrip"></div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-5">
    <div class="xl:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="font-semibold text-gray-800">Trend Analizi <span class="text-xs text-gray-400 font-normal">(6 Ay)</span></h3>
                <p class="text-xs text-gray-400 mt-0.5">Giriş kaydı, onaylı izin ve yeni işe alımlar</p>
            </div>
            <div class="flex items-center gap-4 text-xs">
                <label class="flex items-center gap-1.5 cursor-pointer"><input type="checkbox" class="rounded border-gray-300 text-[#02E0FB] focus:ring-[#02E0FB]" checked data-idx="0"><span class="w-3 h-1.5 rounded bg-[#02E0FB] inline-block"></span>Giriş</label>
                <label class="flex items-center gap-1.5 cursor-pointer"><input type="checkbox" class="rounded border-gray-300 text-[#FA6001] focus:ring-[#FA6001]" checked data-idx="1"><span class="w-3 h-1.5 rounded bg-[#FA6001] inline-block"></span>İzin</label>
                <label class="flex items-center gap-1.5 cursor-pointer"><input type="checkbox" class="rounded border-gray-300 text-green-500 focus:ring-green-500" checked data-idx="2"><span class="w-3 h-1.5 rounded bg-green-500 inline-block"></span>Alım</label>
            </div>
        </div>
        <div style="height: 240px; position: relative;"><canvas id="trendChart"></canvas></div>
    </div>
    <div class="space-y-5">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Departman Dağılımı</h3>
            <div style="height: 160px; position: relative;"><canvas id="deptChart"></canvas></div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-3">Cinsiyet Dağılımı</h3>
            <div class="flex items-center gap-4">
                <div style="width: 100px; height: 100px; position: relative;"><canvas id="genderChart"></canvas></div>
                <div id="genderLegend" class="flex-1 space-y-2 text-xs"></div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Son İzin Talepleri</h3>
            <a href="{{ route('admin.leave.index') }}" class="text-xs text-[#02E0FB] hover:underline font-medium">Tümünü Gör →</a>
        </div>
        <div id="recentLeaves" class="space-y-1">
            <div class="skeleton-row"><div class="skeleton-avatar"></div><div class="skeleton-lines"><div class="skeleton-line w-3/4"></div><div class="skeleton-line w-1/2"></div></div></div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Son Eklenen Personeller</h3>
            <a href="{{ route('admin.personel.index') }}" class="text-xs text-[#02E0FB] hover:underline font-medium">Tümünü Gör →</a>
        </div>
        <div id="recentPersonels" class="space-y-1">
            <div class="skeleton-row"><div class="skeleton-avatar"></div><div class="skeleton-lines"><div class="skeleton-line w-3/4"></div><div class="skeleton-line w-1/2"></div></div></div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-5 mb-5">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Bu Hafta Vardiya</h3>
        <div id="weeklyShifts" class="space-y-2">
            <div class="skeleton-row"><div class="skeleton-line w-full"></div></div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Yaklaşan Tatiller <span class="text-xs text-gray-400 font-normal">(30 Gün)</span></h3>
        <div id="upcomingHolidays" class="space-y-1">
            <div class="skeleton-row"><div class="skeleton-line w-full"></div></div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Doğum Günleri <span class="text-xs text-gray-400 font-normal">(30 Gün)</span></h3>
        <div id="upcomingBirthdays" class="space-y-1">
            <div class="skeleton-row"><div class="skeleton-line w-full"></div></div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Araç Durumu</h3>
        <div id="vehicleStats"><div class="flex items-center justify-center h-[120px]"><canvas id="vehicleChart"></canvas></div></div>
        <div id="vehicleLegend" class="flex flex-wrap items-center justify-center gap-3 mt-2 text-xs text-gray-500"></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-5 mb-5">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Hızlı İşlemler</h3>
        <div class="space-y-1">
            @foreach([
                ['admin.personel.create','👤','Yeni Personel','personel.create'],
                ['admin.leave.requests.create','📅','İzin Talebi','leave.request'],
                ['admin.attendance.index','⏰','Puantaj Girişi','attendance.create'],
                ['admin.assets.index','📦','Envanter','asset.view'],
                ['admin.advance.create','💰','Avans Talebi','advance.request'],
                ['admin.reports.index','📊','Rapor Oluştur','report.view'],
            ] as [$route,$icon,$label,$permission])
            @can($permission)
            <a href="{{ route($route) }}" class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-[#02E0FB]/5 hover:text-[#02E0FB] text-gray-600 text-sm transition-all group">
                <span class="text-lg">{{ $icon }}</span>
                <span class="font-medium">{{ $label }}</span>
                <svg class="w-3.5 h-3.5 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endcan
            @endforeach
        </div>
    </div>
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Son Aktiviteler</h3>
            <button onclick="loadActivity()" class="text-xs text-[#02E0FB] hover:underline font-medium">🔄 Yenile</button>
        </div>
        <div id="activityList" class="space-y-1 max-h-[320px] overflow-y-auto">
            <div class="skeleton-row"><div class="skeleton-avatar"></div><div class="skeleton-lines"><div class="skeleton-line w-3/4"></div><div class="skeleton-line w-1/2"></div></div></div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Aylık Özet</h3>
        <div id="monthlySummary" class="space-y-3">
            <div class="skeleton-row"><div class="skeleton-line w-full"></div></div>
        </div>
    </div>
</div>

<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5" id="miniStats">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 animate-pulse"><div class="bg-gray-100 h-4 rounded w-3/4 mb-2"></div><div class="bg-gray-100 h-6 rounded w-1/2"></div></div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 animate-pulse"><div class="bg-gray-100 h-4 rounded w-3/4 mb-2"></div><div class="bg-gray-100 h-6 rounded w-1/2"></div></div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 animate-pulse"><div class="bg-gray-100 h-4 rounded w-3/4 mb-2"></div><div class="bg-gray-100 h-6 rounded w-1/2"></div></div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 animate-pulse"><div class="bg-gray-100 h-4 rounded w-3/4 mb-2"></div><div class="bg-gray-100 h-6 rounded w-1/2"></div></div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const DASH_URLS = {
    widgets: '{{ route("admin.dashboard.widgets") }}',
    chart: '{{ route("admin.dashboard.chart") }}',
    activity: '{{ route("admin.dashboard.activity") }}',
    refresh: '{{ route("admin.dashboard.refresh") }}',
};
let trendChart, genderChart, deptChart, vehicleChart;

document.addEventListener('DOMContentLoaded', () => {
    updateClock(); setInterval(updateClock, 60000);
    loadWidgets(); loadChartData(); loadActivity();
    document.getElementById('lastRefresh').textContent = 'Son: ' + new Date().toLocaleTimeString('tr-TR');
});

function updateClock() {
    const n = new Date(), h = n.getHours();
    const g = h < 6 ? 'İyi Geceler' : h < 13 ? 'Günaydın' : h < 18 ? 'Tünaydın' : 'İyi Akşamlar';
    document.getElementById('greeting').textContent = g + ', {{ auth()->user()->name }}';
    document.getElementById('clock').textContent = n.toLocaleDateString('tr-TR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
}

function loadWidgets() {
    axios.get(DASH_URLS.widgets).then(res => {
        const d = res.data;
        animateNumber('kpi-total', d.total_personel);
        animateNumber('kpi-checkins', d.today_checkins);
        animateNumber('kpi-leave', d.today_on_leave);
        animateNumber('kpi-pending', d.total_pending);
        animateNumber('kpi-assets', d.available_assets);
        animateNumber('kpi-warranty', d.warranty_expiring_assets);
        animateNumber('kpi-overtime', d.today_overtime_hours > 0 ? d.today_overtime_hours + 'h' : '0h', true);
        animateNumber('kpi-processes', d.active_processes);
        setText('kpi-absent', d.today_absent);
        setText('kpi-hired', '+' + d.hired_this_month);
        setText('kpi-onleave-pct', d.total_personel > 0 ? '%' + Math.round(d.today_on_leave / d.total_personel * 100) : '%0');
        setText('kpi-pending-amount', '₺' + Number(d.pending_amount).toLocaleString('tr-TR'));
        setText('kpi-overtime-pending', d.pending_overtime);
        if (d.total_assets > 0) setText('kpi-assigned-pct', '%' + Math.round(d.assigned_assets / d.total_assets * 100));
        if (d.total_processes > 0) setText('kpi-process-pct', '%' + Math.round(d.completed_processes / d.total_processes * 100));
        document.getElementById('pendingDot').style.display = d.total_pending > 0 ? 'block' : 'none';
        renderPendingStrip(d);
        renderDeptChart(d.department_stats);
        renderGenderChart(d.gender_stats);
        renderVehicleChart(d.vehicle_stats);
        renderRecentLeaves(d.recent_leaves);
        renderRecentPersonels(d.recent_personels);
        renderWeeklyShifts(d.weekly_shift_summary);
        renderUpcomingHolidays(d.upcoming_holidays);
        renderUpcomingBirthdays(d.upcoming_birthdays);
        renderMonthlySummary(d);
        renderMiniStats(d);
        renderSparklines(d);
        checkAlerts(d);
    });
}

function animateNumber(id, val, isString) {
    const el = document.getElementById(id);
    if (!el) return;
    if (isString) { el.textContent = val ?? '—'; return; }
    const target = parseInt(val) || 0;
    if (target === 0) { el.textContent = '0'; return; }
    const dur = Math.min(800, 300 + target * 20);
    const start = performance.now();
    function tick(now) {
        const p = Math.min(1, (now - start) / dur);
        const eased = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.round(eased * target);
        if (p < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
}

function setText(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val ?? '—';
}

function checkAlerts(d) {
    const banner = document.getElementById('alertBanner');
    const text = document.getElementById('alertText');
    const alerts = [];
    if (d.warranty_expiring_assets > 0) alerts.push('⚠️ ' + d.warranty_expiring_assets + ' adet envanterin garantisi 30 gün içinde sona eriyor.');
    if (d.total_pending > 0) alerts.push('⏳ ' + d.total_pending + ' adet bekleyen onayınız bulunuyor.');
    if (d.today_absent > 3) alerts.push('📊 Bugün ' + d.today_absent + ' kişi devamsız.');
    if (alerts.length) { banner.classList.remove('hidden'); text.textContent = alerts[0]; }
}

function renderPendingStrip(d) {
    const items = [
        { key: 'İzin', count: d.pending_leaves, route: '{{ route("admin.leave.index") }}', color: '#FA6001', bg: 'bg-orange-50' },
        { key: 'Avans', count: d.pending_advances, route: '{{ route("admin.advance.index") }}', color: '#f59e0b', bg: 'bg-yellow-50' },
        { key: 'Masraf', count: d.pending_expenses, route: '{{ route("admin.expense.index") }}', color: '#ef4444', bg: 'bg-red-50' },
        { key: 'Mesai', count: d.pending_overtime, route: '{{ route("admin.attendance.index") }}', color: '#8b5cf6', bg: 'bg-purple-50' },
        { key: 'Seyahat', count: d.pending_travel, route: '{{ route("admin.travel.index") }}', color: '#06b6d4', bg: 'bg-cyan-50' },
    ];
    document.getElementById('pendingStrip').innerHTML = items.map(i =>
        '<a href="' + i.route + '" class="flex items-center justify-between bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-3 hover:shadow-md hover:-translate-y-0.5 transition-all group">' +
        '<div><p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">' + i.key + '</p><p class="text-xl font-black text-gray-800 mt-0.5">' + i.count + '</p></div>' +
        '<div class="w-9 h-9 rounded-xl ' + i.bg + ' flex items-center justify-center shrink-0"><svg class="w-4 h-4" style="color:' + i.color + '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg></div></a>'
    ).join('');
}

function renderDeptChart(stats) {
    const canvas = document.getElementById('deptChart');
    if (!canvas) return;
    if (deptChart) deptChart.destroy();
    if (!stats?.length) { canvas.parentElement.innerHTML = '<p class="text-center text-gray-400 text-sm py-8">Departman verisi yok</p>'; return; }
    const colors = ['#02E0FB','#FA6001','#10b981','#8b5cf6','#f59e0b','#ec4899','#06b6d4','#84cc16'];
    const labels = stats.map(s => s.name).reverse();
    const data = stats.map(s => s.count).reverse();
    const bg = [...colors].reverse();
    deptChart = new Chart(canvas, {
        type: 'bar',
        data: { labels, datasets: [{ data, backgroundColor: bg, borderRadius: 4, borderSkipped: false }] },
        options: {
            indexAxis: 'y', responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(15,23,42,.9)', titleColor: '#fff', bodyColor: '#94a3b8', padding: 10, cornerRadius: 8, callbacks: { label: ctx => ' ' + ctx.raw + ' kişi' } } },
            scales: {
                x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' }, ticks: { color: '#94a3b8', font: { size: 10 }, stepSize: 1 } },
                y: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 10 } } }
            }
        }
    });
}

function renderGenderChart(stats) {
    const canvas = document.getElementById('genderChart');
    if (!canvas) return;
    if (genderChart) genderChart.destroy();
    const labels = { M: 'Erkek', F: 'Kadın', other: 'Diğer' };
    const colors = { M: '#02E0FB', F: '#ec4899', other: '#94a3b8' };
    const filtered = (stats || []).filter(s => s.gender);
    if (!filtered.length) { canvas.parentElement.innerHTML = '<p class="text-center text-gray-400 text-sm">Veri yok</p>'; return; }
    genderChart = new Chart(canvas, {
        type: 'doughnut',
        data: { labels: filtered.map(s => labels[s.gender] || s.gender), datasets: [{ data: filtered.map(s => s.count), backgroundColor: filtered.map(s => colors[s.gender] || '#94a3b8'), borderWidth: 0 }] },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '75%',
            plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(15,23,42,.9)', titleColor: '#fff', bodyColor: '#94a3b8', padding: 8, cornerRadius: 8 } }
        }
    });
    const legend = document.getElementById('genderLegend');
    const total = filtered.reduce((s, v) => s + v.count, 0);
    legend.innerHTML = filtered.map(s => '<div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:' + (colors[s.gender] || '#94a3b8') + '"></span><span class="text-gray-600">' + (labels[s.gender] || s.gender) + '</span><span class="font-semibold text-gray-800 ml-auto">' + s.count + '</span><span class="text-gray-400">(' + Math.round(s.count/total*100) + '%)</span></div>').join('');
}

function renderVehicleChart(stats) {
    const canvas = document.getElementById('vehicleChart');
    if (!canvas) return;
    if (vehicleChart) vehicleChart.destroy();
    if (!stats?.length) { document.getElementById('vehicleStats').innerHTML = '<p class="text-center text-gray-400 text-sm py-8">Araç verisi yok</p>'; return; }
    const colorMap = { active: '#10b981', maintenance: '#f59e0b', out_of_service: '#ef4444', retired: '#94a3b8' };
    const labels = { active: 'Aktif', maintenance: 'Bakımda', out_of_service: 'Hizmet Dışı', retired: 'Emekli' };
    vehicleChart = new Chart(canvas, {
        type: 'doughnut',
        data: { labels: stats.map(s => labels[s.status] || s.status), datasets: [{ data: stats.map(s => s.count), backgroundColor: stats.map(s => colorMap[s.status] || '#94a3b8'), borderWidth: 0 }] },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '70%',
            plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(15,23,42,.9)', titleColor: '#fff', bodyColor: '#94a3b8', padding: 8, cornerRadius: 8 } }
        }
    });
    const legend = document.getElementById('vehicleLegend');
    const total = stats.reduce((s, v) => s + v.count, 0);
    legend.innerHTML = stats.map(s => '<span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full inline-block" style="background:' + (colorMap[s.status] || '#94a3b8') + '"></span>' + (labels[s.status] || s.status) + ': ' + s.count + '</span>').join('');
}

function loadChartData() {
    axios.get(DASH_URLS.chart).then(res => {
        const { labels, ciData, lvData, nhData } = res.data;
        if (trendChart) trendChart.destroy();
        const ctx = document.getElementById('trendChart');
        if (!ctx) return;
        trendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    { label: 'Giriş Kaydı', data: ciData, borderColor: '#02E0FB', backgroundColor: 'rgba(2,224,251,.12)', borderWidth: 2.5, fill: true, tension: 0.4, pointBackgroundColor: '#02E0FB', pointRadius: 3, pointHoverRadius: 5 },
                    { label: 'Onaylı İzin', data: lvData, borderColor: '#FA6001', backgroundColor: 'rgba(250,96,1,.08)', borderWidth: 2.5, fill: false, tension: 0.4, pointBackgroundColor: '#FA6001', pointRadius: 3, pointHoverRadius: 5 },
                    { label: 'Yeni İşe Alım', data: nhData, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,.08)', borderWidth: 2.5, fill: false, tension: 0.4, pointBackgroundColor: '#10b981', pointRadius: 3, pointHoverRadius: 5 }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(15,23,42,.9)', titleColor: '#fff', bodyColor: '#94a3b8', padding: 12, cornerRadius: 10 } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' }, ticks: { color: '#94a3b8', font: { size: 11 } } },
                    x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 11 } } }
                }
            }
        });
        document.querySelectorAll('#trendChart + .flex input').forEach(cb => cb.addEventListener('change', function() {
            const idx = parseInt(this.dataset.idx);
            trendChart.setDatasetVisibility(idx, this.checked);
            trendChart.update();
        }));
    });
}

function loadActivity() {
    axios.get(DASH_URLS.activity, { params: { limit: 10 } }).then(res => {
        const list = document.getElementById('activityList');
        if (!res.data.data.length) { list.innerHTML = '<p class="text-center text-gray-400 py-6 text-sm">Aktivite kaydı bulunamadı</p>'; return; }
        const icons = { personel: '\uD83D\uDC64', leave: '\uD83D\uDCC5', asset: '\uD83D\uDCE6', advance: '\uD83D\uDCB0', expense: '\uD83E\uDDFE', visitor: '\uD83D\uDC65', vehicle: '\uD83D\uDE97', user: '\uD83D\uDC64' };
        list.innerHTML = res.data.data.map(a => {
            const icon = Object.entries(icons).find(([k]) => (a.model_type||'').toLowerCase().includes(k))?.[1] ?? '\uD83D\uDCCB';
            return '<div class="flex items-start gap-3 py-2.5 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 rounded-lg transition-colors px-2 -mx-2">' +
                '<div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-base shrink-0">' + icon + '</div>' +
                '<div class="flex-1 min-w-0"><p class="text-sm font-medium text-gray-700 truncate">' + a.action + '</p><p class="text-xs text-gray-400">' + (a.model_type || '') + (a.model_id ? ' #' + a.model_id : '') + '</p></div>' +
                '<span class="text-xs text-gray-300 shrink-0" title="' + a.time_full + '">' + a.time + '</span></div>';
        }).join('');
    }).catch(() => {});
}

function renderRecentLeaves(leaves) {
    const el = document.getElementById('recentLeaves');
    if (!el) return;
    if (!leaves?.length) { el.innerHTML = '<p class="text-center text-gray-400 py-4 text-sm">Henüz izin talebi yok</p>'; return; }
    const scMap = { pending: { label: 'Bekliyor', bg: 'bg-yellow-100', text: 'text-yellow-700' }, approved: { label: 'Onaylandı', bg: 'bg-green-100', text: 'text-green-700' }, rejected: { label: 'Reddedildi', bg: 'bg-red-100', text: 'text-red-700' }, cancelled: { label: 'İptal', bg: 'bg-gray-100', text: 'text-gray-600' } };
    el.innerHTML = leaves.map(l => {
        const sc = scMap[l.status] || { label: l.status, bg: 'bg-gray-100', text: 'text-gray-600' };
        const initials = (l.first_name?.[0] || '') + (l.last_name?.[0] || '');
        return '<div class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 rounded-lg transition-colors px-2 -mx-2">' +
            '<div class="w-8 h-8 rounded-full bg-[#02E0FB]/15 text-[#02E0FB] font-bold text-xs flex items-center justify-center shrink-0">' + initials + '</div>' +
            '<div class="flex-1 min-w-0"><p class="text-sm font-semibold text-gray-800 truncate">' + (l.first_name || '') + ' ' + (l.last_name || '') + '</p><p class="text-xs text-gray-400">' + (l.leave_type_name || '—') + ' · ' + (l.total_days || 0) + ' gün</p></div>' +
            '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold shrink-0 ' + sc.bg + ' ' + sc.text + '">' + sc.label + '</span></div>';
    }).join('');
}

function renderRecentPersonels(personels) {
    const el = document.getElementById('recentPersonels');
    if (!el) return;
    if (!personels?.length) { el.innerHTML = '<p class="text-center text-gray-400 py-4 text-sm">Henüz personel kaydı yok</p>'; return; }
    el.innerHTML = personels.map(p => {
        const initials = (p.first_name?.[0] || '') + (p.last_name?.[0] || '');
        return '<div class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 rounded-lg transition-colors px-2 -mx-2">' +
            '<div class="w-8 h-8 rounded-full bg-[#FA6001]/15 text-[#FA6001] font-bold text-xs flex items-center justify-center shrink-0">' + initials + '</div>' +
            '<div class="flex-1 min-w-0"><p class="text-sm font-semibold text-gray-800 truncate">' + (p.first_name || '') + ' ' + (p.last_name || '') + '</p><p class="text-xs text-gray-400">' + (p.department_name || '—') + ' · ' + (p.position_title || '—') + '</p></div>' +
            '<span class="text-xs text-gray-400 shrink-0">' + (p.hire_date || '—') + '</span></div>';
    }).join('');
}

function renderWeeklyShifts(shifts) {
    const el = document.getElementById('weeklyShifts');
    if (!el) return;
    if (!shifts?.length) { el.innerHTML = '<p class="text-center text-gray-400 py-4 text-sm">Bu hafta vardiya ataması yok</p>'; return; }
    const colors = ['#02E0FB', '#FA6001', '#10b981', '#8b5cf6'];
    const max = Math.max(...shifts.map(s => s.count), 1);
    el.innerHTML = shifts.map((s, i) =>
        '<div class="flex items-center gap-3 py-1.5"><span class="text-sm text-gray-600 w-24 truncate font-medium">' + s.name + '</span>' +
        '<div class="flex-1 h-5 bg-gray-100 rounded-full overflow-hidden"><div class="h-5 rounded-full transition-all duration-700" style="width:' + Math.round(s.count/max*100) + '%;background:' + colors[i % colors.length] + '"></div></div>' +
        '<span class="text-sm font-bold text-gray-800 w-8 text-right">' + s.count + '</span></div>'
    ).join('');
}

function renderUpcomingHolidays(holidays) {
    const el = document.getElementById('upcomingHolidays');
    if (!el) return;
    if (!holidays?.length) { el.innerHTML = '<p class="text-center text-gray-400 py-4 text-sm">Önümüzdeki 30 günde tatil bulunmuyor</p>'; return; }
    el.innerHTML = holidays.map(h =>
        '<div class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 rounded-lg transition-colors px-2 -mx-2">' +
        '<div class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center shrink-0"><svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>' +
        '<div class="flex-1"><p class="text-sm font-semibold text-gray-800">' + h.name + '</p><p class="text-xs text-gray-400">' + (h.type || 'Resmi Tatil') + '</p></div>' +
        '<span class="text-xs font-semibold text-gray-500 shrink-0">' + h.date + '</span></div>'
    ).join('');
}

function renderUpcomingBirthdays(birthdays) {
    const el = document.getElementById('upcomingBirthdays');
    if (!el) return;
    if (!birthdays?.length) { el.innerHTML = '<p class="text-center text-gray-400 py-4 text-sm">Yaklaşan doğum günü yok</p>'; return; }
    el.innerHTML = birthdays.map(b => {
        const nextBD = new Date(b.birth_date);
        nextBD.setFullYear(new Date().getFullYear());
        if (nextBD < new Date()) nextBD.setFullYear(nextBD.getFullYear() + 1);
        const daysLeft = Math.ceil((nextBD - new Date()) / (1000*60*60*24));
        return '<div class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 rounded-lg transition-colors px-2 -mx-2">' +
            '<div class="w-8 h-8 rounded-full bg-pink-50 flex items-center justify-center shrink-0"><svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0A2.701 2.701 0 001.5 15.546M21 15.546V11a2 2 0 00-2-2h-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4H3a2 2 0 00-2 2v4.546"/></svg></div>' +
            '<div class="flex-1"><p class="text-sm font-semibold text-gray-800 truncate">' + b.first_name + ' ' + b.last_name + '</p><p class="text-xs text-gray-400">' + daysLeft + ' gün kaldı</p></div>' +
            '<span class="text-xs font-semibold text-pink-500 shrink-0">' + (new Date(nextBD).toLocaleDateString('tr-TR', { day: 'numeric', month: 'short' })) + '</span></div>';
    }).join('');
}

function renderMonthlySummary(d) {
    const el = document.getElementById('monthlySummary');
    if (!el) return;
    const net = d.hired_this_month - d.terminated_this_month;
    el.innerHTML =
        '<div class="flex items-center justify-between py-1.5"><span class="text-xs text-gray-500">İşe Alım</span><span class="text-sm font-bold text-green-600">+' + d.hired_this_month + '</span></div>' +
        '<div class="flex items-center justify-between py-1.5"><span class="text-xs text-gray-500">İşten Çıkış</span><span class="text-sm font-bold text-red-500">-' + d.terminated_this_month + '</span></div>' +
        '<div class="h-px bg-gray-100 my-1.5"></div>' +
        '<div class="flex items-center justify-between py-1.5"><span class="text-xs text-gray-500">Net Değişim</span><span class="text-sm font-bold ' + (net >= 0 ? 'text-green-600' : 'text-red-500') + '">' + (net > 0 ? '+' : '') + net + '</span></div>' +
        '<div class="flex items-center justify-between py-1.5"><span class="text-xs text-gray-500">Toplam Personel</span><span class="text-sm font-bold text-gray-800">' + d.total_personel + '</span></div>' +
        '<div class="flex items-center justify-between py-1.5"><span class="text-xs text-gray-500">Aktif Süreç</span><span class="text-sm font-bold text-pink-600">' + d.active_processes + '</span></div>' +
        '<div class="flex items-center justify-between py-1.5"><span class="text-xs text-gray-500">Ziyaretçi (Bu Ay)</span><span class="text-sm font-bold text-cyan-600">' + d.visitor_this_month + '</span></div>' +
        '<div class="flex items-center justify-between py-1.5"><span class="text-xs text-gray-500">Aktif Duyuru</span><span class="text-sm font-bold text-gray-800">' + (d.active_announcements || 0) + '</span></div>' +
        '<div class="flex items-center justify-between py-1.5"><span class="text-xs text-gray-500">Aktif Anket</span><span class="text-sm font-bold text-gray-800">' + (d.active_polls || 0) + '</span></div>';
}

function renderMiniStats(d) {
    const el = document.getElementById('miniStats');
    if (!el) return;
    el.innerHTML =
        '<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 hover:shadow-md transition-all"><p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">Toplam Envanter</p><p class="text-xl font-black text-gray-900">' + (d.total_assets || 0) + '</p></div>' +
        '<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 hover:shadow-md transition-all"><p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">Aktif Hizmet</p><p class="text-xl font-black text-gray-900">' + (d.active_services || 0) + '</p></div>' +
        '<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 hover:shadow-md transition-all"><p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">Duyuru/Anket</p><p class="text-xl font-black text-gray-900">' + ((d.active_announcements || 0) + (d.active_polls || 0)) + '</p></div>' +
        '<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 hover:shadow-md transition-all"><p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">İzin (Bugün)</p><p class="text-xl font-black text-yellow-600">' + (d.today_on_leave || 0) + '</p></div>';
}

function renderSparklines(d) {
    ['total','checkins','assets'].forEach(key => {
        const svg = document.getElementById('spark-' + key);
        if (!svg) return;
        const data = key === 'total' ? [d.total_personel, d.hired_this_month, d.total_terminated, d.total_personel] :
                     key === 'checkins' ? [d.today_checkins, d.today_absent, d.today_on_leave, d.today_checkins] :
                     [d.available_assets, d.assigned_assets, d.warranty_expiring_assets, d.total_assets];
        const max = Math.max(...data, 1);
        const pts = data.map((v, i) => ({ x: i * (120 / (data.length - 1 || 1)), y: 22 - (v / max) * 18 }));
        svg.innerHTML = '<polyline fill="none" stroke="' + (key === 'checkins' ? '#10b981' : key === 'assets' ? '#3b82f6' : '#02E0FB') + '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" points="' + pts.map(p => p.x + ',' + p.y).join(' ') + '"/>' +
            pts.map(p => '<circle cx="' + p.x + '" cy="' + p.y + '" r="2" fill="' + (key === 'checkins' ? '#10b981' : key === 'assets' ? '#3b82f6' : '#02E0FB') + '"/>').join('');
    });
}

function refreshDashboard() {
    const icon = document.getElementById('refreshIcon');
    icon.classList.add('animate-spin');
    axios.post(DASH_URLS.refresh).then(() => {
        loadWidgets(); loadChartData(); loadActivity();
        document.getElementById('lastRefresh').textContent = 'Son: ' + new Date().toLocaleTimeString('tr-TR');
        setTimeout(() => icon.classList.remove('animate-spin'), 800);
    });
}
</script>
@endpush
