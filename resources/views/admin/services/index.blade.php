@extends('layouts.app')
@section('title', 'Hizmet Yönetimi')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#02E0FB]">Dashboard</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-800 font-medium">Hizmet Yönetimi</span>
@endsection

@section('page_header')
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Hizmet Yönetimi</h1>
        <p class="text-sm text-gray-500 mt-0.5">Şirket hizmet ve aboneliklerini yönetin.</p>
    </div>
    @can('service.create')
    <button onclick="openCreateModal()"
        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-[#02E0FB] rounded-lg hover:bg-cyan-400 transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Yeni Hizmet
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
            <input type="text" id="searchInput" placeholder="Hizmet ara..."
                class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wide">
                <tr>
                    <th class="px-6 py-3 text-left">#</th>
                    <th class="px-6 py-3 text-left">Hizmet Adı</th>
                    <th class="px-6 py-3 text-left">Açıklama</th>
                    <th class="px-6 py-3 text-left">Durum</th>
                    <th class="px-6 py-3 text-left">Oluşturma</th>
                    <th class="px-6 py-3 text-right">İşlemler</th>
                </tr>
            </thead>
            <tbody id="tableBody" class="divide-y divide-gray-100">
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                        <div class="flex flex-col items-center gap-2">
                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span class="text-sm">Henüz hizmet eklenmemiş</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div id="pagination" class="px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-2 text-sm text-gray-500">
        <span id="paginationInfo">-</span>
        <div id="paginationButtons" class="flex gap-2"></div>
    </div>
</div>

<div id="modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeModal()"></div>
    <div class="absolute right-0 top-0 h-full w-full max-w-lg bg-white shadow-2xl flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 id="modalTitle" class="text-lg font-semibold text-gray-900">Yeni Hizmet</h2>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="modalBody" class="flex-1 overflow-y-auto p-6"></div>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
            <button onclick="closeModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
            <button id="saveBtn" onclick="saveService()" class="hidden px-4 py-2 text-sm text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-lg font-medium">Kaydet</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentPage = 1;
let searchTimeout;

const API = {
    list:    '{{ route("admin.services.list") }}',
    store:   '{{ route("admin.services.store") }}',
    edit:    id => `/admin/services/${id}/edit`,
    update:  id => `/admin/services/${id}`,
    destroy: id => `/admin/services/${id}`,
};

function loadData(page = 1) {
    currentPage = page;
    const search = document.getElementById('searchInput').value;

    fetch(`${API.list}?page=${page}&search=${search}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => {
        const tbody = document.getElementById('tableBody');
        const items = data.data || [];
        if (!items.length) {
            tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">
                <div class="flex flex-col items-center gap-2">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span>Henüz hizmet eklenmemiş</span>
                </div>
            </td></tr>`;
            return;
        }
        tbody.innerHTML = items.map((s, i) => `<tr class="hover:bg-gray-50 transition">
            <td class="px-6 py-4 text-gray-500">${(currentPage-1)*15 + i + 1}</td>
            <td class="px-6 py-4 font-medium text-gray-900">${s.name || '-'}</td>
            <td class="px-6 py-4 text-gray-600 max-w-[300px] truncate">${s.description || '-'}</td>
            <td class="px-6 py-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    ${s.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'}">
                    ${s.is_active ? 'Aktif' : 'Pasif'}
                </span>
            </td>
            <td class="px-6 py-4 text-gray-500 text-xs">${s.created_at ? new Date(s.created_at).toLocaleDateString('tr-TR') : '-'}</td>
            <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-1">
                    <button onclick="editItem(${s.id})" class="p-1.5 text-gray-400 hover:text-[#02E0FB] hover:bg-[#02E0FB]/10 rounded-lg transition" title="Düzenle">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button onclick="deleteService(${s.id})" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg" title="Sil">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </td>
        </tr>`).join('');
        document.getElementById('paginationInfo').textContent = `Toplam ${data.total} kayıt, ${data.current_page}/${data.last_page} sayfa`;
    }).catch(() => {});
}

function openCreateModal() {
    axios.get('{{ route("admin.services.create") }}').then(res => {
        document.getElementById('modalTitle').textContent = 'Yeni Hizmet';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modal').classList.remove('hidden');
        document.getElementById('saveBtn').classList.remove('hidden');
    });
}

function saveService() {
    const form = document.getElementById('serviceForm');
    const data = Object.fromEntries(new FormData(form).entries());
    data.is_active = data.is_active ? 1 : 0;
    const isEdit = form.querySelector('input[name="_method"]');
    const url = isEdit ? API.update(document.getElementById('saveBtn').dataset.id) : API.store;
    if (isEdit) data._method = 'PUT';
    axios.post(url, data).then(r => { closeModal(); toast('success', r.data.message); loadData(); }).catch(e => { toast('error', Object.values(e.response.data.errors || {}).flat().join('\n')); });
}

function editItem(id) {
    axios.get(API.edit(id)).then(res => {
        document.getElementById('modalTitle').textContent = 'Hizmet Düzenle';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modal').classList.remove('hidden');
        document.getElementById('saveBtn').classList.remove('hidden');
        document.getElementById('saveBtn').dataset.id = id;
    });
}

function deleteService(id) {
    Swal.fire({ title: 'Emin misiniz?', text: 'Bu hizmet silinecek', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Sil', cancelButtonText: 'İptal' }).then(r => {
        if (r.isConfirmed) axios.delete(API.destroy(id)).then(r => { toast('success', r.data.message); loadData(); });
    });
}

function closeModal() {
    document.getElementById('modal').classList.add('hidden');
    document.getElementById('saveBtn').classList.add('hidden');
}

document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => loadData(1), 400);
});
</script>
@endpush
