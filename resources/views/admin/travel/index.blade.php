@extends('layouts.app')
@section('title', 'Seyahat Talepleri')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#02E0FB]">Dashboard</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-800 font-medium">Seyahat Talepleri</span>
@endsection

@section('page_header')
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Seyahat Talepleri</h1>
        <p class="text-sm text-gray-500 mt-0.5">Personel seyahat taleplerini yönetin ve onaylayın.</p>
    </div>
    <div class="flex items-center gap-2">
        <div class="relative group">
            <button onclick="toggleExportMenu()"
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Dışa Aktar
            </button>
            <div id="exportMenu" class="hidden absolute right-0 mt-1 w-40 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
                <a href="{{ route('admin.travel.export.excel') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-t-lg">📊 Excel (CSV)</a>
                <a href="{{ route('admin.travel.export.pdf') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-b-lg">📄 PDF</a>
            </div>
        </div>
        @can('travel.request')
        <button onclick="openCreateModal()"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-[#02E0FB] rounded-lg hover:bg-cyan-400 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Yeni Talep
        </button>
        @endcan
    </div>
@endsection

@section('content')
<div x-data="travelApp()" x-init="init()">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 sm:p-3 bg-blue-50 rounded-lg">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                </div>
                <div>
                    <p class="text-xs sm:text-sm text-gray-500">Toplam</p>
                    <p id="kpiTotal" class="text-lg sm:text-2xl font-bold text-gray-900">—</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 sm:p-3 bg-yellow-50 rounded-lg">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs sm:text-sm text-gray-500">Bekleyen</p>
                    <p id="kpiPending" class="text-lg sm:text-2xl font-bold text-yellow-600">—</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 sm:p-3 bg-green-50 rounded-lg">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs sm:text-sm text-gray-500">Onaylı</p>
                    <p id="kpiApproved" class="text-lg sm:text-2xl font-bold text-green-600">—</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 sm:p-3 bg-blue-50 rounded-lg">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="text-xs sm:text-sm text-gray-500">Tamamlanan</p>
                    <p id="kpiCompleted" class="text-lg sm:text-2xl font-bold text-cyan-600">—</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-3 sm:p-4 border-b border-gray-100 space-y-3">
            <div class="flex flex-wrap items-center gap-2">
                <button data-quick="all" class="quick-btn px-3 py-1.5 text-xs font-medium rounded-full border transition-colors bg-[#02E0FB] text-white border-[#02E0FB]">Tümü</button>
                <button data-quick="week" class="quick-btn px-3 py-1.5 text-xs font-medium rounded-full border border-gray-200 text-gray-600 hover:bg-gray-50">Bu Hafta</button>
                <button data-quick="month" class="quick-btn px-3 py-1.5 text-xs font-medium rounded-full border border-gray-200 text-gray-600 hover:bg-gray-50">Bu Ay</button>
                <button data-quick="year" class="quick-btn px-3 py-1.5 text-xs font-medium rounded-full border border-gray-200 text-gray-600 hover:bg-gray-50">Bu Yıl</button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2 sm:gap-3">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                    <input type="text" id="searchInput" placeholder="Personel, yer, amaç..."
                        class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
                </div>
                <select id="filterPersonel" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30">
                    <option value="">Tüm Personel</option>
                    @foreach($personels as $p)
                    <option value="{{ $p->id }}">{{ $p->first_name }} {{ $p->last_name }}</option>
                    @endforeach
                </select>
                <select id="statusFilter" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30">
                    <option value="">Tüm Durumlar</option>
                    <option value="pending">Bekleyen</option>
                    <option value="approved">Onaylı</option>
                    <option value="rejected">Reddedilen</option>
                    <option value="completed">Tamamlanan</option>
                </select>
                <input type="date" id="filterDateFrom"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
                <input type="date" id="filterDateTo"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm travel-table">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wide hidden sm:table-header-group">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-left">#</th>
                        <th class="px-4 sm:px-6 py-3 text-left">Personel</th>
                        <th class="px-4 sm:px-6 py-3 text-left">Gidilecek Yer</th>
                        <th class="px-4 sm:px-6 py-3 text-left">Gidiş-Dönüş</th>
                        <th class="px-4 sm:px-6 py-3 text-left">Amaç</th>
                        <th class="px-4 sm:px-6 py-3 text-left">Durum</th>
                        <th class="px-4 sm:px-6 py-3 text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>

        <div id="pagination" class="px-4 sm:px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-2 text-sm text-gray-500">
            <span id="paginationInfo">-</span>
            <div id="paginationButtons" class="flex gap-2"></div>
        </div>
    </div>

    <div id="modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50" onclick="closeModal()"></div>
        <div class="absolute right-0 top-0 h-full w-full max-w-lg bg-white shadow-2xl flex flex-col">
            <div class="flex items-center justify-between px-4 sm:px-6 py-4 border-b border-gray-100">
                <h2 id="modalTitle" class="text-lg font-semibold text-gray-900">Yeni Seyahat Talebi</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="modalBody" class="flex-1 overflow-y-auto p-4 sm:p-6"></div>
            <div class="px-4 sm:px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                <button onclick="closeModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
                <button id="saveBtn" onclick="saveItem()" class="hidden px-4 py-2 text-sm text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-lg font-medium">Kaydet</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
@media (max-width: 640px) {
    .travel-table thead { display: none; }
    .travel-table tbody tr {
        display: block;
        padding: 1rem;
        border-bottom: 1px solid #f3f4f6;
    }
    .travel-table tbody tr td {
        display: flex;
        align-items: center;
        padding: 0.25rem 0;
        border: none;
        text-align: right;
        justify-content: space-between;
        gap: 0.5rem;
    }
    .travel-table tbody tr td:before {
        content: attr(data-label);
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        text-align: left;
    }
    .travel-table tbody tr td:last-child { justify-content: flex-end; }
}
</style>
<script>
let currentPage = 1;
let searchTimeout;
let personelList = [];
let personelSearchInit = false;

const API = {
    list:     '{{ route("admin.travel.list") }}',
    store:    '{{ route("admin.travel.store") }}',
    widgets:  '{{ route("admin.travel.widgets") }}',
    edit:     id => `/admin/travel/${id}/edit`,
    update:   id => `/admin/travel/${id}`,
    destroy:  id => `/admin/travel/${id}`,
    approve:  id => `/admin/travel/${id}/approve`,
    reject:   id => `/admin/travel/${id}/reject`,
    cancel:   id => `/admin/travel/${id}/cancel`,
    complete: id => `/admin/travel/${id}/complete`,
};

function loadKPIs() {
    axios.get(API.widgets).then(r => {
        document.getElementById('kpiTotal').textContent = r.data.total;
        document.getElementById('kpiPending').textContent = r.data.pending;
        document.getElementById('kpiApproved').textContent = r.data.approved;
        document.getElementById('kpiCompleted').textContent = r.data.completed;
    }).catch(() => {});
}

function loadData(page = 1) {
    currentPage = page;
    const params = {
        page,
        search:    document.getElementById('searchInput').value,
        personel_id: document.getElementById('filterPersonel').value,
        status:    document.getElementById('statusFilter').value,
        date_from: document.getElementById('filterDateFrom').value,
        date_to:   document.getElementById('filterDateTo').value,
    };

    axios.get(API.list, { params }).then(r => {
        const data = r.data;
        const tbody = document.getElementById('tableBody');
        const items = data.data || [];

        if (!items.length) {
            tbody.innerHTML = `<tr><td colspan="7" class="px-4 sm:px-6 py-12 text-center text-gray-400">
                <div class="flex flex-col items-center gap-2">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span class="text-sm">Henüz seyahat talebi bulunmuyor</span>
                </div>
            </td></tr>`;
            updatePagination(data);
            return;
        }

        tbody.innerHTML = items.map((t, i) => {
            const personelName = t.personel ? `${t.personel.first_name || ''} ${t.personel.last_name || ''}` : '-';
            const dates = t.departure_date
                ? `${formatDate(t.departure_date)} → ${formatDate(t.return_date)}`
                : '-';
            const statusClass = t.status === 'approved' ? 'bg-green-100 text-green-700'
                : t.status === 'rejected' ? 'bg-red-100 text-red-700'
                : t.status === 'completed' ? 'bg-blue-100 text-blue-700'
                : 'bg-yellow-100 text-yellow-700';

            let actions = `<div class="flex items-center justify-end gap-1">`;

            if (t.status === 'pending') {
                @can('travel.approve')
                actions += `<button onclick="approveTravel(${t.id})" class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg" title="Onayla">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </button>
                <button onclick="rejectTravel(${t.id})" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Reddet">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>`;
                @endcan
                @can('travel.request')
                actions += `<button onclick="cancelTravel(${t.id})" class="p-1.5 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg" title="İptal">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>`;
                @endcan
            }
            if (t.status === 'approved') {
                @can('travel.manage')
                actions += `<button onclick="completeTravel(${t.id})" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg" title="Tamamla">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </button>`;
                @endcan
            }
            @can('travel.manage')
            actions += `<button onclick="editItem(${t.id})" class="p-1.5 text-gray-400 hover:text-[#02E0FB] hover:bg-[#02E0FB]/10 rounded-lg transition" title="Düzenle">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </button>
            <button onclick="deleteTravel(${t.id})" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg" title="Sil">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>`;
            @endcan
            actions += `</div>`;

            return `<tr class="hover:bg-gray-50 transition">
                <td data-label="#" class="px-4 sm:px-6 py-4 text-gray-500">${(currentPage-1)*15 + i + 1}</td>
                <td data-label="Personel" class="px-4 sm:px-6 py-4 font-medium text-gray-900">${personelName}</td>
                <td data-label="Gidilecek Yer" class="px-4 sm:px-6 py-4 text-gray-600">${t.destination || '-'}</td>
                <td data-label="Gidiş-Dönüş" class="px-4 sm:px-6 py-4 text-gray-600">${dates}</td>
                <td data-label="Amaç" class="px-4 sm:px-6 py-4 text-gray-600 max-w-[200px] truncate">${t.purpose || '-'}</td>
                <td data-label="Durum" class="px-4 sm:px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                        ${t.status_label || t.status || '-'}
                    </span>
                </td>
                <td data-label="İşlemler" class="px-4 sm:px-6 py-4 text-right">${actions}</td>
            </tr>`;
        }).join('');

        updatePagination(data);
    }).catch(() => {});
}

function updatePagination(data) {
    document.getElementById('paginationInfo').textContent = `Toplam ${data.total} kayıt, ${data.current_page}/${data.last_page} sayfa`;
    const btns = document.getElementById('paginationButtons');
    let html = '';
    for (let i = 1; i <= data.last_page; i++) {
        html += `<button onclick="loadData(${i})" class="px-3 py-1 text-sm rounded-lg ${i === data.current_page ? 'bg-[#02E0FB] text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}">${i}</button>`;
    }
    btns.innerHTML = html;
}

function formatDate(dateStr) {
    try { return new Date(dateStr).toLocaleDateString('tr-TR'); } catch(e) { return dateStr; }
}

function initPersonelSearch(selectedId) {
    const input = document.getElementById('personelSearchInput');
    const hidden = document.getElementById('personelIdInput');
    const dropdown = document.getElementById('personelDropdown');
    if (!input) return;

    if (selectedId) {
        const p = personelList.find(x => x.id == selectedId);
        if (p) { input.value = p.name; hidden.value = p.id; }
    } else {
        hidden.value = '';
        input.value = '';
    }

    function render(filter) {
        const q = (filter || '').toLowerCase();
        const filtered = q ? personelList.filter(p => p.name.toLowerCase().includes(q)) : personelList;
        if (!filtered.length) {
            dropdown.innerHTML = '<div class="px-3 py-2 text-sm text-gray-400">Eşleşen personel bulunamadı</div>';
        } else {
            dropdown.innerHTML = filtered.map(p =>
                `<div class="px-3 py-2 text-sm text-gray-700 hover:bg-[#02E0FB]/10 cursor-pointer border-b border-gray-50 last:border-0" data-id="${p.id}">${p.name}</div>`
            ).join('');
        }
        dropdown.classList.remove('hidden');
    }

    input.addEventListener('input', () => render(input.value));
    input.addEventListener('focus', () => render(input.value));

    dropdown.addEventListener('click', e => {
        const item = e.target.closest('[data-id]');
        if (!item) return;
        hidden.value = item.dataset.id;
        input.value = item.textContent;
        dropdown.classList.add('hidden');
    });

    if (!personelSearchInit) {
        document.addEventListener('click', e => {
            const wrap = document.getElementById('personelSelectWrap');
            if (wrap && !wrap.contains(e.target)) {
                const dd = document.getElementById('personelDropdown');
                if (dd) dd.classList.add('hidden');
            }
        });
        personelSearchInit = true;
    }

    if (!selectedId) render('');
}

function setQuickFilter(period) {
    const from = document.getElementById('filterDateFrom');
    const to = document.getElementById('filterDateTo');
    const today = new Date();
    from.value = '';
    to.value = '';

    if (period === 'week') {
        const start = new Date(today); start.setDate(today.getDate() - today.getDay() + 1);
        const end = new Date(today); end.setDate(start.getDate() + 6);
        from.value = start.toISOString().split('T')[0];
        to.value = end.toISOString().split('T')[0];
    } else if (period === 'month') {
        const start = new Date(today.getFullYear(), today.getMonth(), 1);
        const end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        from.value = start.toISOString().split('T')[0];
        to.value = end.toISOString().split('T')[0];
    } else if (period === 'year') {
        const start = new Date(today.getFullYear(), 0, 1);
        const end = new Date(today.getFullYear(), 11, 31);
        from.value = start.toISOString().split('T')[0];
        to.value = end.toISOString().split('T')[0];
    }
    loadData(1);
}

document.querySelectorAll('.quick-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.quick-btn').forEach(b => {
            b.classList.remove('bg-[#02E0FB]', 'text-white', 'border-[#02E0FB]');
            b.classList.add('border-gray-200', 'text-gray-600');
        });
        this.classList.add('bg-[#02E0FB]', 'text-white', 'border-[#02E0FB]');
        this.classList.remove('border-gray-200', 'text-gray-600');
        setQuickFilter(this.dataset.quick);
    });
});

function openCreateModal() {
    axios.get('{{ route("admin.travel.create") }}').then(res => {
        document.getElementById('modalTitle').textContent = 'Yeni Seyahat Talebi';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modal').classList.remove('hidden');
        document.getElementById('saveBtn').classList.remove('hidden');
        personelList = res.data.personels || [];
        initPersonelSearch();
        document.getElementById('saveBtn').onclick = function() {
            const form = document.getElementById('travelForm');
            const data = Object.fromEntries(new FormData(form).entries());
            axios.post(API.store, data).then(r => { closeModal(); toast('success', r.data.message); loadData(); loadKPIs(); }).catch(e => { if (e.response?.data?.message) toast('error', e.response.data.message); });
        };
    });
}

function editItem(id) {
    axios.get(API.edit(id)).then(res => {
        document.getElementById('modalTitle').textContent = 'Seyahat Düzenle';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modal').classList.remove('hidden');
        document.getElementById('saveBtn').classList.remove('hidden');
        personelList = res.data.personels || [];
        initPersonelSearch(res.data.selected_id);
        document.getElementById('saveBtn').onclick = function() {
            const form = document.getElementById('travelForm');
            const data = Object.fromEntries(new FormData(form).entries());
            data._method = 'PUT';
            axios.post(API.update(id), data).then(r => { closeModal(); toast('success', r.data.message); loadData(); loadKPIs(); }).catch(e => { if (e.response?.data?.message) toast('error', e.response.data.message); });
        };
    });
}

function deleteTravel(id) {
    Swal.fire({ title: 'Emin misiniz?', text: 'Bu seyahat talebi silinecek', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Sil', cancelButtonText: 'İptal' }).then(r => {
        if (r.isConfirmed) axios.delete(API.destroy(id)).then(r => { toast('success', r.data.message); loadData(); loadKPIs(); });
    });
}

function approveTravel(id) {
    Swal.fire({ title: 'Seyahati Onayla', icon: 'question', showCancelButton: true, confirmButtonColor: '#02E0FB', confirmButtonText: 'Onayla', cancelButtonText: 'İptal' }).then(r => {
        if (r.isConfirmed) axios.post(API.approve(id)).then(r => { toast('success', r.data.message); loadData(); loadKPIs(); });
    });
}

function rejectTravel(id) {
    Swal.fire({ title: 'Seyahati Reddet', input: 'textarea', inputPlaceholder: 'Ret gerekçesi...', showCancelButton: true, confirmButtonColor: '#FA6001', confirmButtonText: 'Reddet', cancelButtonText: 'İptal', inputValidator: v => !v && 'Gerekçe zorunludur!' }).then(r => {
        if (r.isConfirmed) axios.post(API.reject(id), { reason: r.value }).then(r => { toast('success', r.data.message); loadData(); loadKPIs(); });
    });
}

function cancelTravel(id) {
    Swal.fire({ title: 'Talebi İptal Et', text: 'Bu seyahat talebi iptal edilecek', icon: 'warning', showCancelButton: true, confirmButtonColor: '#f59e0b', confirmButtonText: 'İptal Et', cancelButtonText: 'Vazgeç' }).then(r => {
        if (r.isConfirmed) axios.post(API.cancel(id)).then(r => { toast('success', r.data.message); loadData(); loadKPIs(); });
    });
}

function completeTravel(id) {
    Swal.fire({ title: 'Tamamla', text: 'Seyahat tamamlandı olarak işaretlensin mi?', icon: 'question', showCancelButton: true, confirmButtonColor: '#02E0FB', confirmButtonText: 'Tamamla', cancelButtonText: 'İptal' }).then(r => {
        if (r.isConfirmed) axios.post(API.complete(id)).then(r => { toast('success', r.data.message); loadData(); loadKPIs(); });
    });
}

function closeModal() {
    document.getElementById('modal').classList.add('hidden');
    document.getElementById('saveBtn').classList.add('hidden');
}

function toggleExportMenu() {
    const menu = document.getElementById('exportMenu');
    menu.classList.toggle('hidden');
}

document.addEventListener('click', function(e) {
    const menu = document.getElementById('exportMenu');
    if (menu && !e.target.closest('.relative.group')) menu.classList.add('hidden');
});

document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { loadData(1); }, 400);
});
['filterPersonel', 'statusFilter', 'filterDateFrom', 'filterDateTo'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', () => loadData(1));
});

function init() {
    loadKPIs();
    loadData(1);
}
</script>
@endpush