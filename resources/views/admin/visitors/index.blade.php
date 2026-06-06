@extends('layouts.app')
@section('title', 'Ziyaretçi Yönetimi')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#02E0FB]">Dashboard</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-800 font-medium">Ziyaretçi Yönetimi</span>
@endsection

@section('page_header')
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Ziyaretçi Yönetimi</h1>
        <p class="text-sm text-gray-500 mt-0.5">Şirket ziyaretçi kayıtlarını ve giriş-çıkış işlemlerini yönetin.</p>
    </div>
    @can('visitor.create')
    <button onclick="openCreateModal()"
        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-[#02E0FB] rounded-lg hover:bg-cyan-400 transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Yeni Ziyaretçi
    </button>
    @endcan
@endsection

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-4">
        <div class="relative flex-1 max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
            <input type="text" id="searchInput" placeholder="Ziyaretçi ara..."
                class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
        </div>
        <select id="statusFilter" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30">
            <option value="">Tümü</option>
            <option value="active">Aktif</option>
            <option value="completed">Çıkış Yaptı</option>
        </select>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wide">
                <tr>
                    <th class="px-6 py-3 text-left">#</th>
                    <th class="px-6 py-3 text-left">Ad Soyad</th>
                    <th class="px-6 py-3 text-left">TC/Pasaport</th>
                    <th class="px-6 py-3 text-left">Ziyaret Edilen</th>
                    <th class="px-6 py-3 text-left">Giriş</th>
                    <th class="px-6 py-3 text-left">Çıkış</th>
                    <th class="px-6 py-3 text-right">İşlemler</th>
                </tr>
            </thead>
            <tbody id="tableBody" class="divide-y divide-gray-100">
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        <div class="flex flex-col items-center gap-2">
                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                            </svg>
                            <span class="text-sm">Henüz ziyaretçi kaydı bulunmuyor</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div id="pagination" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
        <span id="paginationInfo">-</span>
        <div id="paginationButtons" class="flex gap-2"></div>
    </div>
</div>

<div id="modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeModal()"></div>
    <div class="absolute right-0 top-0 h-full w-full max-w-lg bg-white shadow-2xl flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 id="modalTitle" class="text-lg font-semibold text-gray-900">Yeni Ziyaretçi</h2>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="modalBody" class="flex-1 overflow-y-auto p-6"></div>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
            <button onclick="closeModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
            <button id="saveBtn" onclick="saveVisitor()" class="hidden px-4 py-2 text-sm text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-lg font-medium">Kaydet</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentPage = 1;
let searchTimeout;

const API = {
    list:     '{{ route("admin.visitors.list") }}',
    store:    '{{ route("admin.visitors.store") }}',
    edit:     id => `/admin/visitors/${id}/edit`,
    update:   id => `/admin/visitors/${id}`,
    destroy:  id => `/admin/visitors/${id}`,
    checkin:  id => `/admin/visitors/${id}/checkin`,
    checkout: id => `/admin/visitors/${id}/checkout`,
    badge:    id => `/admin/visitors/${id}/badge`,
};

function loadData(page = 1) {
    currentPage = page;
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;

    fetch(`${API.list}?page=${page}&search=${search}&status=${status}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => {
        const tbody = document.getElementById('tableBody');
        const items = data.data || [];
        if (!items.length) {
            tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">
                <div class="flex flex-col items-center gap-2">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                    </svg>
                    <span>Henüz ziyaretçi kaydı bulunmuyor</span>
                </div>
            </td></tr>`;
            return;
        }
        tbody.innerHTML = items.map((v, i) => `<tr class="hover:bg-gray-50 transition">
            <td class="px-6 py-4 text-gray-500">${(currentPage-1)*15 + i + 1}</td>
            <td class="px-6 py-4 font-medium text-gray-900">${v.full_name || v.name || '-'}</td>
            <td class="px-6 py-4 text-gray-600">${v.id_number || '-'}</td>
            <td class="px-6 py-4 text-gray-600">${v.visited_person || '-'}</td>
            <td class="px-6 py-4 text-gray-600">${v.check_in ? new Date(v.check_in).toLocaleString('tr-TR') : '-'}</td>
            <td class="px-6 py-4 text-gray-600">${v.check_out ? new Date(v.check_out).toLocaleString('tr-TR') : '-'}</td>
            <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-1">
                    ${!v.check_in ? `
                    <button onclick="checkinVisitor(${v.id})" class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg" title="Giriş Yap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    </button>` : !v.check_out ? `
                    <button onclick="checkoutVisitor(${v.id})" class="p-1.5 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg" title="Çıkış Yap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>` : ''}
                    <button onclick="editItem(${v.id})" class="p-1.5 text-gray-400 hover:text-[#02E0FB] hover:bg-[#02E0FB]/10 rounded-lg transition" title="Düzenle">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button onclick="deleteVisitor(${v.id})" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg" title="Sil">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </td>
        </tr>`).join('');
        document.getElementById('paginationInfo').textContent = `Toplam ${data.total} kayıt, ${data.current_page}/${data.last_page} sayfa`;
    }).catch(() => {});
}

function openCreateModal() {
    axios.get('{{ route("admin.visitors.create") }}').then(res => {
        document.getElementById('modalTitle').textContent = 'Yeni Ziyaretçi';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modal').classList.remove('hidden');
        document.getElementById('saveBtn').classList.remove('hidden');
    });
}

function saveVisitor() {
    const form = document.getElementById('visitorForm');
    const data = Object.fromEntries(new FormData(form).entries());
    const isEdit = form.querySelector('input[name="_method"]');
    const url = isEdit ? API.update(document.getElementById('saveBtn').dataset.id) : API.store;
    if (isEdit) data._method = 'PUT';
    axios.post(url, data).then(r => { closeModal(); toast('success', r.data.message); loadData(); }).catch(e => { toast('error', Object.values(e.response.data.errors || {}).flat().join('\n')); });
}

function editItem(id) {
    axios.get(API.edit(id)).then(res => {
        document.getElementById('modalTitle').textContent = 'Ziyaretçi Düzenle';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modal').classList.remove('hidden');
        document.getElementById('saveBtn').classList.remove('hidden');
        document.getElementById('saveBtn').dataset.id = id;
    });
}

function deleteVisitor(id) {
    Swal.fire({ title: 'Emin misiniz?', text: 'Bu ziyaretçi kaydı silinecek', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Sil', cancelButtonText: 'İptal' }).then(r => {
        if (r.isConfirmed) axios.delete(API.destroy(id)).then(r => { toast('success', r.data.message); loadData(); });
    });
}

function checkinVisitor(id) {
    axios.post(API.checkin(id)).then(r => { toast('success', r.data.message); loadData(); });
}

function checkoutVisitor(id) {
    axios.post(API.checkout(id)).then(r => { toast('success', r.data.message); loadData(); });
}

function closeModal() {
    document.getElementById('modal').classList.add('hidden');
    document.getElementById('saveBtn').classList.add('hidden');
}

document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => loadData(1), 400);
});
document.getElementById('statusFilter').addEventListener('change', () => loadData(1));
</script>
@endpush
