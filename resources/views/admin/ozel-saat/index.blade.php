@extends('layouts.app')
@section('title', 'Özel Saat Yönetimi')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#02E0FB]">Dashboard</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-800 font-medium">Özel Saat</span>
@endsection

@section('page_header')
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Özel Saat Yönetimi</h1>
        <p class="text-sm text-gray-500 mt-0.5">Üst düzey personelin devam durumlarını yönetin.</p>
    </div>
    <button onclick="markAllToday()" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Bugünü Toplu İşaretle
    </button>
@endsection

@section('content')
<div class="mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        {{-- Tabs --}}
        <div class="flex items-center gap-1 p-4 pb-0 border-b border-gray-100">
            <button onclick="switchTab('special')" id="tabSpecial" class="px-4 py-2 text-sm font-medium rounded-lg bg-[#02E0FB] text-white transition-colors">Özel Personel</button>
            <button onclick="switchTab('all')" id="tabAll" class="px-4 py-2 text-sm font-medium rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">Tüm Personel</button>
            <button onclick="switchTab('report')" id="tabReport" class="px-4 py-2 text-sm font-medium rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">Aylık Rapor</button>
        </div>

        {{-- Özel Personel Listesi --}}
        <div id="viewSpecial">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-4 border-b border-gray-100 bg-gray-50/50">
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <input type="date" id="filterDate" value="{{ $today }}"
                        class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]">
                    <select id="filterStatus" class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]">
                        <option value="">Tümü</option>
                        <option value="present">Mevcut</option>
                        <option value="absent">İzinli/Gelmedi</option>
                        <option value="half_day">Yarım Gün</option>
                        <option value="none">İşaretlenmemiş</option>
                    </select>
                </div>
            </div>
            <div id="personelGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
                @forelse($specialPersonels as $p)
                <div class="relative rounded-2xl border p-5 transition-all hover:shadow-md"
                    x-data
                    style="border-color: {{ isset($todayRecords[$p->id]) ? ($todayRecords[$p->id]->status === 'present' ? '#22c55e' : ($todayRecords[$p->id]->status === 'half_day' ? '#f59e0b' : '#ef4444')) : '#e5e7eb' }}; border-width: 2px;">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#02E0FB] to-[#00b8d9] flex items-center justify-center text-white font-bold text-lg shrink-0">
                            {{ substr($p->first_name, 0, 1) }}{{ substr($p->last_name, 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-gray-900 text-sm truncate">{{ $p->full_name }}</h4>
                            <p class="text-xs text-gray-500 truncate">{{ $p->title ?? '—' }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $p->department?->name ?? '—' }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-1.5">
                            @php
                                $rec = $todayRecords[$p->id] ?? null;
                            @endphp
                            <select onchange="markAttendance({{ $p->id }}, this.value)"
                                class="text-xs border border-gray-200 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-[#02E0FB] {{ $rec ? ($rec->status === 'present' ? 'text-green-700 border-green-300 bg-green-50' : ($rec->status === 'half_day' ? 'text-amber-700 border-amber-300 bg-amber-50' : 'text-red-700 border-red-300 bg-red-50')) : 'text-gray-500' }}">
                                <option value="present" {{ $rec && $rec->status === 'present' ? 'selected' : '' }}>Mevcut</option>
                                <option value="absent" {{ $rec && $rec->status === 'absent' ? 'selected' : '' }}>İzinli</option>
                                <option value="half_day" {{ $rec && $rec->status === 'half_day' ? 'selected' : '' }}>Yarım Gün</option>
                            </select>
                            @if($rec && $rec->is_auto)
                                <span class="text-[10px] text-gray-400 italic">Otomatik</span>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full text-center py-16">
                    <svg class="w-16 h-16 mx-auto text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <h3 class="text-lg font-semibold text-gray-400 mb-1">Henüz özel saat personeli yok</h3>
                    <p class="text-sm text-gray-400">Tüm Personel sekmesinden bir personeli özel saat olarak işaretleyin.</p>
                    <button onclick="switchTab('all')" class="inline-block mt-4 px-6 py-2.5 bg-[#02E0FB] text-gray-900 rounded-xl text-sm font-semibold hover:bg-cyan-400 transition-colors">
                        Tüm Personel
                    </button>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Tüm Personel --}}
        <div id="viewAll" class="hidden">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-4 border-b border-gray-100 bg-gray-50/50">
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <input type="text" id="allSearch" placeholder="Personel ara..."
                        class="px-3 py-2 border border-gray-200 rounded-xl text-sm w-48 focus:outline-none focus:ring-2 focus:ring-[#02E0FB]">
                    <select id="allDepartment" class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]">
                        <option value="">Tüm Birimler</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                    <span id="allTotalCount" class="text-xs text-gray-400"></span>
                </div>
            </div>
            <div id="allPersonelList" class="divide-y divide-gray-100">
                <div class="text-center py-12 text-gray-400">Yükleniyor...</div>
            </div>
        </div>

        {{-- Aylık Rapor --}}
        <div id="viewReport" class="hidden">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-4 border-b border-gray-100 bg-gray-50/50">
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <input type="month" id="reportMonth" value="{{ now()->format('Y-m') }}"
                        class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]">
                    <input type="text" id="reportSearch" placeholder="Personel ara..."
                        class="px-3 py-2 border border-gray-200 rounded-xl text-sm w-44 focus:outline-none focus:ring-2 focus:ring-[#02E0FB]">
                    <select id="reportDepartment" class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]">
                        <option value="">Tüm Birimler</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div id="reportContent" class="overflow-x-auto p-4">
                <div class="text-center py-12 text-gray-400">Raporu görüntülemek için ay seçin.</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('filterDate').addEventListener('change', loadList);
document.getElementById('filterStatus').addEventListener('change', loadList);

document.getElementById('allSearch').addEventListener('input', debounce(loadAllPersonels, 300));
document.getElementById('allDepartment').addEventListener('change', loadAllPersonels);

function debounce(fn, ms) {
    let timer;
    return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn(...args), ms); };
}

function loadList() {
    const date = document.getElementById('filterDate').value;
    const status = document.getElementById('filterStatus').value;
    const params = new URLSearchParams({ special_only: '1', date, per_page: '100' });

    axios.get('{{ route("admin.ozel-saat.list") }}?' + params).then(res => {
        const grid = document.getElementById('personelGrid');
        const items = res.data.data;

        const filtered = status ? items.filter(p => status === 'none' ? p.today_status === 'none' : p.today_status === status) : items;

        if (!filtered.length) {
            grid.innerHTML = '<div class="col-span-full text-center py-12 text-gray-400">Eşleşen personel bulunamadı.</div>';
            return;
        }

        grid.innerHTML = filtered.map(p => {
            const statusColors = {
                present: { border: '#22c55e', bg: 'bg-green-50', text: 'text-green-700', sel: 'border-green-300' },
                absent: { border: '#ef4444', bg: 'bg-red-50', text: 'text-red-700', sel: 'border-red-300' },
                half_day: { border: '#f59e0b', bg: 'bg-amber-50', text: 'text-amber-700', sel: 'border-amber-300' },
                none: { border: '#e5e7eb', bg: '', text: 'text-gray-500', sel: '' }
            };
            const sc = statusColors[p.today_status] || statusColors.none;
            const initial = (p.name || '??').split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();

            return `<div class="relative rounded-2xl border-2 p-5 transition-all hover:shadow-md" style="border-color: ${sc.border}">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#02E0FB] to-[#00b8d9] flex items-center justify-center text-white font-bold text-lg shrink-0">${initial}</div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-gray-900 text-sm truncate">${p.name}</h4>
                        <p class="text-xs text-gray-500 truncate">${p.title || '—'}</p>
                        <p class="text-xs text-gray-400 truncate">${p.department || '—'}</p>
                    </div>
                    <div class="flex flex-col items-end gap-1.5">
                        <select onchange="markAttendance(${p.id}, this.value)"
                            class="text-xs border rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-[#02E0FB] ${sc.bg} ${sc.text} ${sc.sel}">
                            <option value="present" ${p.today_status === 'present' ? 'selected' : ''}>Mevcut</option>
                            <option value="absent" ${p.today_status === 'absent' ? 'selected' : ''}>İzinli</option>
                            <option value="half_day" ${p.today_status === 'half_day' ? 'selected' : ''}>Yarım Gün</option>
                        </select>
                        ${p.today_is_auto ? '<span class="text-[10px] text-gray-400 italic">Otomatik</span>' : ''}
                    </div>
                </div>
            </div>`;
        }).join('');
    });
}

function loadAllPersonels() {
    const search = document.getElementById('allSearch').value;
    const departmentId = document.getElementById('allDepartment').value;
    const params = new URLSearchParams({ per_page: '200', search, department_id: departmentId });

    axios.get('{{ route("admin.ozel-saat.list") }}?' + params).then(res => {
        const container = document.getElementById('allPersonelList');
        const items = res.data.data;
        document.getElementById('allTotalCount').textContent = res.data.total + ' personel';

        if (!items.length) {
            container.innerHTML = '<div class="text-center py-12 text-gray-400">Personel bulunamadı.</div>';
            return;
        }

        container.innerHTML = items.map(p => `
            <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-[#02E0FB] to-[#00b8d9] flex items-center justify-center text-white font-bold text-xs shrink-0">
                        ${(p.name || '??').split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase()}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">${p.name}</p>
                        <p class="text-xs text-gray-400 truncate">${p.title || '—'}${p.department ? ' · ' + p.department : ''}</p>
                    </div>
                </div>
                <button onclick="toggleSpecialHours(${p.id}, this)"
                    class="shrink-0 px-3 py-1.5 text-xs font-medium rounded-lg transition-colors ${p.is_special_hours ? 'bg-red-50 text-red-600 border border-red-200 hover:bg-red-100' : 'bg-[#02E0FB]/10 text-[#02E0FB] border border-[#02E0FB]/30 hover:bg-[#02E0FB]/20'}">
                    ${p.is_special_hours ? 'Özel Saatten Çıkar' : 'Özel Saat Yap'}
                </button>
            </div>
        `).join('');
    });
}

function toggleSpecialHours(personelId, btn) {
    btn.disabled = true;
    btn.textContent = 'İşleniyor...';
    axios.post('{{ route("admin.ozel-saat.toggle") }}', { personel_id: personelId })
        .then(res => {
            toast('success', res.data.message);
            loadAllPersonels();
            loadList();
        })
        .catch(() => {
            toast('error', 'İşlem başarısız');
            btn.disabled = false;
            btn.textContent = 'Tekrar Dene';
        });
}

function markAttendance(personelId, status) {
    const date = document.getElementById('filterDate').value;
    axios.post('{{ route("admin.ozel-saat.mark") }}', { personel_id: personelId, date, status })
        .then(res => toast('success', res.data.message))
        .catch(() => toast('error', 'Güncelleme başarısız'));
}

function markAllToday() {
    if (!confirm('Tüm özel saat personelini bugün için "mevcut" olarak işaretle?')) return;
    axios.post('{{ route("admin.ozel-saat.mark-all") }}')
        .then(res => { toast('success', res.data.message); loadList(); })
        .catch(() => toast('error', 'Toplu işaretleme başarısız'));
}

function switchTab(tab) {
    document.getElementById('viewSpecial').classList.toggle('hidden', tab !== 'special');
    document.getElementById('viewAll').classList.toggle('hidden', tab !== 'all');
    document.getElementById('viewReport').classList.toggle('hidden', tab !== 'report');

    [['tabSpecial', 'special'], ['tabAll', 'all'], ['tabReport', 'report']].forEach(([id, t]) => {
        document.getElementById(id).className = t === tab
            ? 'px-4 py-2 text-sm font-medium rounded-lg bg-[#02E0FB] text-white transition-colors'
            : 'px-4 py-2 text-sm font-medium rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors';
    });

    if (tab === 'special') loadList();
    if (tab === 'all') loadAllPersonels();
    if (tab === 'report') loadReport();
}

document.getElementById('reportSearch').addEventListener('input', debounce(loadReport, 300));
document.getElementById('reportDepartment').addEventListener('change', loadReport);
document.getElementById('reportMonth').addEventListener('change', loadReport);

function loadReport() {
    const month = document.getElementById('reportMonth').value;
    const search = document.getElementById('reportSearch').value;
    const departmentId = document.getElementById('reportDepartment').value;
    const content = document.getElementById('reportContent');
    content.innerHTML = '<div class="text-center py-8 text-gray-400">Yükleniyor...</div>';

    axios.get('{{ route("admin.ozel-saat.report") }}', { params: { month, search, department_id: departmentId } }).then(res => {
        const { report, daysInMonth, month: m } = res.data;
        if (!report.length) {
            content.innerHTML = '<div class="text-center py-12 text-gray-400">Bu ay için veri bulunamadı.</div>';
            return;
        }

        let html = '<table class="w-full text-sm"><thead><tr class="bg-gray-50 border-b border-gray-100">';
        html += '<th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Personel</th>';
        for (let d = 1; d <= daysInMonth; d++) {
            const isWeekend = new Date(m + '-' + String(d).padStart(2, '0')).getDay();
            html += `<th class="px-2 py-2 text-center text-xs font-semibold ${isWeekend === 0 || isWeekend === 6 ? 'text-red-300' : 'text-gray-500'} uppercase">${d}</th>`;
        }
        html += '<th class="px-2 py-2 text-center text-xs font-semibold text-green-600 uppercase">M</th>';
        html += '<th class="px-2 py-2 text-center text-xs font-semibold text-red-600 uppercase">İ</th>';
        html += '<th class="px-2 py-2 text-center text-xs font-semibold text-amber-600 uppercase">Y</th>';
        html += '</tr></thead><tbody>';

        report.forEach(r => {
            html += '<tr class="border-b border-gray-50 hover:bg-gray-50/50">';
            html += `<td class="px-3 py-2 font-medium text-gray-800 text-xs">${r.personel.full_name}</td>`;
            r.daily.forEach(d => {
                const cls = !d ? 'bg-gray-50' : d.status === 'present' ? 'bg-green-100' : d.status === 'absent' ? 'bg-red-100' : 'bg-amber-100';
                const icon = !d ? '−' : d.status === 'present' ? '✓' : d.status === 'absent' ? '✗' : '½';
                html += `<td class="px-2 py-2 text-center text-xs font-bold ${cls}">${icon}</td>`;
            });
            html += `<td class="px-2 py-2 text-center text-xs font-bold text-green-700">${r.present}</td>`;
            html += `<td class="px-2 py-2 text-center text-xs font-bold text-red-700">${r.absent}</td>`;
            html += `<td class="px-2 py-2 text-center text-xs font-bold text-amber-700">${r.half}</td>`;
            html += '</tr>';
        });

        html += '</tbody></table>';
        content.innerHTML = html;
    });
}

function toast(type, msg) {
    const el = document.createElement('div');
    el.className = 'fixed top-4 right-4 z-50 px-5 py-3 rounded-xl shadow-lg text-sm font-medium transition-all ' + (type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200');
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 300); }, 3000);
}
</script>
@endpush
