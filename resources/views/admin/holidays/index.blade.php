@extends('layouts.app')

@section('title', 'Resmi Tatiller')

@section('page_header')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Resmi Tatil Yönetimi</h1>
        <p class="text-sm text-gray-500 mt-1">Yıllık resmi tatil takvimini yönetin.</p>
    </div>
    <div class="flex gap-2">
        <button onclick="seedHolidays()" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 border border-[#02E0FB]/40 text-[#02E0FB] font-semibold rounded-xl hover:bg-[#02E0FB]/5 transition-all text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Tatilleri Yükle
        </button>
        <button onclick="openAddModal()" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#02E0FB] hover:bg-[#00b8d9] text-gray-900 font-semibold rounded-xl shadow-md transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tatil Ekle
        </button>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Yıl Filtresi --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5">
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
            <label class="text-sm font-medium text-gray-700">Yıl Seç:</label>
            <select id="yearFilter" class="w-full sm:w-auto px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent" onchange="loadHolidays()">
                @for ($y = date('Y') - 2; $y <= date('Y') + 2; $y++)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    {{-- Tatil Tablosu --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="hidden sm:table-cell px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tatil Adı</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tarih</th>
                        <th class="hidden md:table-cell px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Gün</th>
                        <th class="hidden sm:table-cell px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tür</th>
                        <th class="px-4 sm:px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">İşlem</th>
                    </tr>
                </thead>
                <tbody id="holidaysBody" class="bg-white divide-y divide-gray-50">
                    <tr>
                        <td colspan="6" class="px-4 sm:px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-gray-300 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <p class="text-gray-400 text-sm">Yükleniyor...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="pagination" class="flex items-center justify-between px-4 sm:px-6 py-3 border-t border-gray-100"></div>
    </div>
</div>

{{-- Add/Edit Modal --}}
<div id="holidayModal" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 id="modalTitle" class="text-lg font-bold text-gray-900">Tatil Ekle</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="modalBody"></div>
        <div class="flex gap-3 mt-6">
            <button type="button" onclick="closeModal()" class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">İptal</button>
            <button type="button" onclick="saveHoliday()" class="flex-1 px-4 py-2.5 bg-[#02E0FB] hover:bg-[#00b8d9] text-gray-900 rounded-xl text-sm font-semibold transition-all">Kaydet</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentPage = 1;
let editingId = null;

function closeModal() {
    document.getElementById('holidayModal').classList.add('hidden');
    editingId = null;
}

function openAddModal() {
    editingId = null;
    document.getElementById('modalTitle').textContent = 'Tatil Ekle';
    document.getElementById('modalBody').innerHTML = `
        <form id="holidayForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tatil Adı <span class="text-red-500">*</span></label>
                <input type="text" name="name" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tarih <span class="text-red-500">*</span></label>
                <input type="date" name="date" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tür</label>
                <select name="type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
                    <option value="national">Resmi Tatil</option>
                    <option value="religious">Dini Bayram</option>
                    <option value="custom">Şirket Tatili</option>
                </select>
            </div>
        </form>
    `;
    document.getElementById('holidayModal').classList.remove('hidden');
}

function openEditModal(id) {
    editingId = id;
    document.getElementById('modalTitle').textContent = 'Tatil Düzenle';
    document.getElementById('modalBody').innerHTML = '<div class="text-center py-4 text-gray-400">Yükleniyor...</div>';
    document.getElementById('holidayModal').classList.remove('hidden');

    fetch(`/admin/holidays/${id}/edit`)
        .then(r => r.json())
        .then(res => {
            document.getElementById('modalBody').innerHTML = res.html;
        })
        .catch(() => {
            document.getElementById('modalBody').innerHTML = '<p class="text-red-500 text-center">Yüklenirken hata oluştu.</p>';
        });
}

function saveHoliday() {
    const form = document.getElementById('holidayForm');
    if (!form) return;
    const data = new FormData(form);
    const url = editingId ? `/admin/holidays/${editingId}` : '/admin/holidays';
    const method = editingId ? 'POST' : 'POST';

    if (editingId) {
        data.append('_method', 'PUT');
    }

    fetch(url, { method, body: data, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                closeModal();
                loadHolidays();
                showToast(res.message, 'success');
            } else {
                showToast(res.message || 'Hata oluştu.', 'error');
            }
        })
        .catch(() => showToast('Bir hata oluştu.', 'error'));
}

function deleteHoliday(id) {
    if (!confirm('Bu tatili silmek istediğinize emin misiniz?')) return;
    fetch(`/admin/holidays/${id}`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: new URLSearchParams({ _method: 'DELETE', _token: '{{ csrf_token() }}' })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            loadHolidays();
            showToast(res.message, 'success');
        }
    })
    .catch(() => showToast('Silinirken hata oluştu.', 'error'));
}

function loadHolidays(page = 1) {
    currentPage = page;
    const year = document.getElementById('yearFilter').value;
    const body = document.getElementById('holidaysBody');
    body.innerHTML = `
        <tr><td colspan="6" class="px-4 sm:px-6 py-12 text-center">
            <div class="flex flex-col items-center gap-3">
                <svg class="w-12 h-12 text-gray-300 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <p class="text-gray-400 text-sm">Yükleniyor...</p>
            </div>
        </td></tr>
    `;

    fetch(`/admin/holidays/list?year=${year}&page=${page}&per_page=50`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        const days = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];
        const typeMap = { national: 'Resmi', religious: 'Dini', custom: 'Şirket' };

        if (!res.data?.length) {
            body.innerHTML = `
                <tr><td colspan="6" class="px-4 sm:px-6 py-12 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-gray-400 text-sm">Bu yıl için tatil bulunamadı.</p>
                        <button onclick="seedHolidays()" class="text-[#02E0FB] text-sm font-medium hover:underline">Türkiye Resmi Tatillerini Yükle</button>
                    </div>
                </td></tr>
            `;
            document.getElementById('pagination').innerHTML = '';
            return;
        }

        body.innerHTML = res.data.map((h, i) => {
            const d = new Date(h.date + 'T00:00:00');
            const dayName = days[d.getDay()];
            const type = h.is_national ? 'Resmi' : (h.company_id ? 'Şirket' : 'Dini');
            const typeClass = type === 'Resmi' ? 'bg-[#02E0FB]/10 text-[#00b8d9]' : (type === 'Dini' ? 'bg-purple-50 text-purple-600' : 'bg-amber-50 text-amber-600');
            return `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="hidden sm:table-cell px-4 sm:px-6 py-3 sm:py-4 text-sm text-gray-500">${(currentPage - 1) * 50 + i + 1}</td>
                    <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm font-medium text-gray-900">${h.name}</td>
                    <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm text-gray-600 whitespace-nowrap">${h.date}</td>
                    <td class="hidden md:table-cell px-4 sm:px-6 py-3 sm:py-4 text-sm text-gray-600">${dayName}</td>
                    <td class="hidden sm:table-cell px-4 sm:px-6 py-3 sm:py-4"><span class="px-2.5 py-1 rounded-full text-xs font-semibold ${typeClass}">${type}</span></td>
                    <td class="px-4 sm:px-6 py-3 sm:py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button onclick="openEditModal(${h.id})" class="text-gray-400 hover:text-[#02E0FB] text-xs font-medium">Düzenle</button>
                            <button onclick="deleteHoliday(${h.id})" class="text-red-400 hover:text-red-600 text-xs font-medium">Sil</button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        const pag = document.getElementById('pagination');
        if (res.last_page <= 1) { pag.innerHTML = ''; return; }
        pag.innerHTML = `
            <span class="text-sm text-gray-500">Toplam ${res.total} kayıt</span>
            <div class="flex gap-1">
                ${Array.from({length: res.last_page}, (_, i) => `
                    <button onclick="loadHolidays(${i + 1})" class="px-3 py-1 text-sm rounded-lg ${currentPage === i + 1 ? 'bg-[#02E0FB] text-gray-900 font-semibold' : 'text-gray-600 hover:bg-gray-100'}">${i + 1}</button>
                `).join('')}
            </div>
        `;
    })
    .catch(() => {
        body.innerHTML = `<tr><td colspan="6" class="px-4 sm:px-6 py-12 text-center text-red-500 text-sm">Yüklenirken hata oluştu.</td></tr>`;
    });
}

function seedHolidays() {
    const year = document.getElementById('yearFilter').value;
    if (!confirm(`${year} yılı Türkiye resmi tatillerini yüklemek istediğinize emin misiniz?`)) return;

    fetch('/admin/holidays/seed', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ _token: '{{ csrf_token() }}', year: parseInt(year) })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            loadHolidays();
            showToast(res.message, 'success');
        }
    })
    .catch(() => showToast('Yüklenirken hata oluştu.', 'error'));
}

function showToast(msg, type) {
    const el = document.createElement('div');
    el.className = `fixed top-4 right-4 z-50 px-5 py-3 rounded-xl shadow-lg text-sm font-medium transition-all duration-300 ${type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'}`;
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 300); }, 3000);
}

document.addEventListener('DOMContentLoaded', () => loadHolidays());
</script>
@endpush
