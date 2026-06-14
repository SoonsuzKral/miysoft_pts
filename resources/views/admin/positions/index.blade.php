@extends('layouts.app')

@section('title', 'Pozisyonlar')

@section('page_header')
<div>
    <h1 class="text-2xl font-bold text-gray-900">Pozisyon Yönetimi</h1>
    <p class="text-sm text-gray-500 mt-1">Şirket içi unvan ve pozisyonları yönetin.</p>
</div>
<button onclick="openModal()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#02E0FB] hover:bg-[#00b8d9] text-gray-900 font-semibold rounded-xl shadow-md transition-all">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Yeni Pozisyon
</button>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-5 border-b border-gray-100">
        <div class="flex gap-3">
            <input type="text" id="searchInput" placeholder="Pozisyon ara..." class="w-64 px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent" oninput="filterTable()">
            <select id="deptFilter" class="px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent" onchange="filterTable()">
                <option value="">Tüm Departmanlar</option>
            </select>
        </div>
    </div>
    <table class="min-w-full divide-y divide-gray-100">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pozisyon Adı</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Departman</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Personel Sayısı</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Durum</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">İşlem</th>
            </tr>
        </thead>
        <tbody id="positionsBody" class="bg-white divide-y divide-gray-50">
            <tr>
                <td colspan="5" class="px-6 py-16 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="text-gray-400 text-sm">Henüz pozisyon eklenmemiş.</p>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

{{-- Add/Edit Modal --}}
<div id="positionModal" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-gray-900">Pozisyon Ekle / Düzenle</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="positionForm">
            @csrf
            <input type="hidden" name="id" id="positionId">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pozisyon Adı <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="positionTitle" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent" placeholder="Örn: Yazılım Geliştirici">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Departman</label>
                    <select name="department_id" id="positionDept" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                        <option value="">Seçiniz...</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Açıklama</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent" placeholder="Pozisyon açıklaması..."></textarea>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_active" id="positionActive" value="1" checked class="w-4 h-4 text-[#02E0FB] border-gray-300 rounded focus:ring-[#02E0FB]">
                    <label for="positionActive" class="text-sm text-gray-700">Aktif</label>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="closeModal()" class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">İptal</button>
                <button type="submit" class="flex-1 px-4 py-2.5 bg-[#02E0FB] hover:bg-[#00b8d9] text-gray-900 rounded-xl text-sm font-semibold transition-all">Kaydet</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openModal(data = null) {
    document.getElementById('positionId').value = data?.id ?? '';
    document.getElementById('positionTitle').value = data?.title ?? '';
    document.getElementById('positionModal').classList.remove('hidden');
}
function closeModal() { document.getElementById('positionModal').classList.add('hidden'); }
function filterTable() { /* DataTables integration */ }

document.addEventListener('DOMContentLoaded', function() {
    axios.get('/admin/positions?format=json').then(res => {
        if (res.data?.data) renderPositions(res.data.data);
    }).catch(() => {});
});

function renderPositions(positions) {
    const body = document.getElementById('positionsBody');
    if (!positions.length) return;
    body.innerHTML = positions.map(p => `
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4 text-sm font-medium text-gray-900">${p.title}</td>
            <td class="px-6 py-4 text-sm text-gray-600">${p.department?.name ?? '-'}</td>
            <td class="px-6 py-4 text-sm text-gray-600">${p.personels_count ?? 0}</td>
            <td class="px-6 py-4">
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold ${p.is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600'}">
                    ${p.is_active ? 'Aktif' : 'Pasif'}
                </span>
            </td>
            <td class="px-6 py-4 text-right flex justify-end gap-2">
                <button onclick="openModal(${JSON.stringify(p).replace(/"/g, '&quot;')})" class="text-[#02E0FB] hover:text-[#00b8d9] text-xs font-medium">Düzenle</button>
                <button class="text-red-400 hover:text-red-600 text-xs font-medium">Sil</button>
            </td>
        </tr>
    `).join('');
}
</script>
@endpush
