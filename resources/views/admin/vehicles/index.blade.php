@extends('layouts.app')
@section('title', 'Araç Yönetimi')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#02E0FB]">Dashboard</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-800 font-medium">Araç Yönetimi</span>
@endsection

@section('page_header')
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Araç Yönetimi</h1>
        <p class="text-sm text-gray-500 mt-0.5">Araç filosu, yakıt kayıtları ve kullanım takibi</p>
    </div>
    <div class="flex items-center gap-2">
        <div class="relative group">
            <button onclick="toggleExportMenu()"
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Dışa Aktar
            </button>
            <div id="exportMenu" class="hidden absolute right-0 mt-1 w-40 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
                <a href="{{ route('admin.vehicles.export.excel') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-t-lg">📊 Excel (CSV)</a>
                <a href="{{ route('admin.vehicles.export.pdf') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-b-lg">📄 PDF</a>
            </div>
        </div>
        @can('vehicle.manage')
        <button onclick="openCreateModal()"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-[#02E0FB] rounded-lg hover:bg-cyan-400 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Yeni Araç
        </button>
        @endcan
    </div>
@endsection

@section('content')
<div x-data="vehicleApp()" x-init="init()">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 sm:p-3 bg-blue-50 rounded-lg">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
                <div>
                    <p class="text-xs sm:text-sm text-gray-500">Toplam Araç</p>
                    <p id="kpiTotal" class="text-lg sm:text-2xl font-bold text-gray-900">—</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 sm:p-3 bg-green-50 rounded-lg">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs sm:text-sm text-gray-500">Aktif</p>
                    <p id="kpiActive" class="text-lg sm:text-2xl font-bold text-green-600">—</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 sm:p-3 bg-yellow-50 rounded-lg">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs sm:text-sm text-gray-500">Bakımda</p>
                    <p id="kpiMaintenance" class="text-lg sm:text-2xl font-bold text-yellow-600">—</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 sm:p-3 bg-red-50 rounded-lg">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </div>
                <div>
                    <p class="text-xs sm:text-sm text-gray-500">Hizmet Dışı</p>
                    <p id="kpiOutOfService" class="text-lg sm:text-2xl font-bold text-red-600">—</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="border-b border-gray-100">
            <div class="flex">
                <button data-tab="vehicles" class="tab-btn px-4 sm:px-6 py-3 text-sm font-medium border-b-2 border-[#02E0FB] text-[#02E0FB]">🚗 Araçlar</button>
                <button data-tab="fuel" class="tab-btn px-4 sm:px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent">⛽ Yakıt</button>
                <button data-tab="usage" class="tab-btn px-4 sm:px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent">📋 Kullanım</button>
            </div>
        </div>

        <div id="tabVehicles" class="tab-content">
            <div class="p-3 sm:p-4 border-b border-gray-100 space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                        <input type="text" id="searchInput" placeholder="Plaka, marka, model ara..."
                            class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
                    </div>
                    <select id="statusFilter" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30">
                        <option value="">Tüm Durumlar</option>
                        <option value="active">Aktif</option>
                        <option value="maintenance">Bakımda</option>
                        <option value="out_of_service">Hizmet Dışı</option>
                    </select>
                    <select id="fuelTypeFilter" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30">
                        <option value="">Tüm Yakıt</option>
                        <option value="benzin">Benzin</option>
                        <option value="dizel">Dizel</option>
                        <option value="lpg">LPG</option>
                        <option value="elektrik">Elektrik</option>
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm vehicle-table">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wide hidden sm:table-header-group">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left">#</th>
                            <th class="px-4 sm:px-6 py-3 text-left">Araç</th>
                            <th class="px-4 sm:px-6 py-3 text-left">Marka</th>
                            <th class="px-4 sm:px-6 py-3 text-left">KM</th>
                            <th class="px-4 sm:px-6 py-3 text-left">Durum</th>
                            <th class="px-4 sm:px-6 py-3 text-left">Personel</th>
                            <th class="px-4 sm:px-6 py-3 text-left">Sigorta</th>
                            <th class="px-4 sm:px-6 py-3 text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody id="vehicleTableBody" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </div>

        <div id="tabFuel" class="tab-content hidden">
            <div class="p-3 sm:p-4 border-b border-gray-100 space-y-3">
                <div class="flex flex-wrap gap-2">
                    @can('vehicle.manage')
                    <button onclick="openFuelModal()" class="px-3 py-1.5 text-xs font-medium text-white bg-[#02E0FB] rounded-lg hover:bg-cyan-400">+ Yakıt Ekle</button>
                    @endcan
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3">
                    <select id="fuelVehicleFilter" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        <option value="">Tüm Araçlar</option>
                        @foreach($vehicles as $v)
                        <option value="{{ $v->id }}">{{ $v->plate }} - {{ $v->brand }} {{ $v->model }}</option>
                        @endforeach
                    </select>
                    <input type="date" id="fuelDateFrom" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                    <input type="date" id="fuelDateTo" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wide hidden sm:table-header-group">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left">Tarih</th>
                            <th class="px-4 sm:px-6 py-3 text-left">Araç</th>
                            <th class="px-4 sm:px-6 py-3 text-right">KM</th>
                            <th class="px-4 sm:px-6 py-3 text-right">Litre</th>
                            <th class="px-4 sm:px-6 py-3 text-right">Birim Fiyat</th>
                            <th class="px-4 sm:px-6 py-3 text-right">Toplam</th>
                            <th class="px-4 sm:px-6 py-3 text-left">İstasyon</th>
                            <th class="px-4 sm:px-6 py-3 text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody id="fuelTableBody" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </div>

        <div id="tabUsage" class="tab-content hidden">
            <div class="p-3 sm:p-4 border-b border-gray-100 space-y-3">
                <div class="flex flex-wrap gap-2">
                    @can('vehicle.manage')
                    <button onclick="openUsageModal()" class="px-3 py-1.5 text-xs font-medium text-white bg-[#02E0FB] rounded-lg hover:bg-cyan-400">+ Kullanım Ekle</button>
                    @endcan
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 sm:gap-3">
                    <select id="usageVehicleFilter" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        <option value="">Tüm Araçlar</option>
                        @foreach($vehicles as $v)
                        <option value="{{ $v->id }}">{{ $v->plate }} - {{ $v->brand }} {{ $v->model }}</option>
                        @endforeach
                    </select>
                    <select id="usageStatusFilter" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        <option value="">Tüm Durumlar</option>
                        <option value="active">Aktif</option>
                        <option value="completed">Tamamlandı</option>
                        <option value="cancelled">İptal</option>
                    </select>
                    <input type="date" id="usageDateFrom" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                    <input type="date" id="usageDateTo" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wide hidden sm:table-header-group">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left">Tarih</th>
                            <th class="px-4 sm:px-6 py-3 text-left">Araç</th>
                            <th class="px-4 sm:px-6 py-3 text-left">Personel</th>
                            <th class="px-4 sm:px-6 py-3 text-left">Güzergah</th>
                            <th class="px-4 sm:px-6 py-3 text-right">KM</th>
                            <th class="px-4 sm:px-6 py-3 text-left">Durum</th>
                            <th class="px-4 sm:px-6 py-3 text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody id="usageTableBody" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </div>

        <div id="pagination" class="px-4 sm:px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-2 text-sm text-gray-500">
            <span id="paginationInfo">-</span>
            <div id="paginationButtons" class="flex gap-2"></div>
        </div>
    </div>

    <div id="vehicleModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50" onclick="closeVehicleModal()"></div>
        <div class="absolute right-0 top-0 h-full w-full max-w-2xl bg-white shadow-2xl flex flex-col">
            <div class="flex items-center justify-between px-4 sm:px-6 py-4 border-b border-gray-100">
                <h2 id="vehicleModalTitle" class="text-lg font-semibold text-gray-900">Yeni Araç</h2>
                <button onclick="closeVehicleModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="vehicleModalBody" class="flex-1 overflow-y-auto p-4 sm:p-6"></div>
            <div class="px-4 sm:px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                <button onclick="closeVehicleModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
                <button id="vehicleSaveBtn" onclick="saveVehicle()" class="hidden px-4 py-2 text-sm text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-lg font-medium">Kaydet</button>
            </div>
        </div>
    </div>

    <div id="fuelModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50" onclick="closeFuelModal()"></div>
        <div class="absolute right-0 top-0 h-full w-full max-w-lg bg-white shadow-2xl flex flex-col">
            <div class="flex items-center justify-between px-4 sm:px-6 py-4 border-b border-gray-100">
                <h2 id="fuelModalTitle" class="text-lg font-semibold text-gray-900">Yakıt Kaydı</h2>
                <button onclick="closeFuelModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="fuelModalBody" class="flex-1 overflow-y-auto p-4 sm:p-6"></div>
            <div class="px-4 sm:px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                <button onclick="closeFuelModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
                <button id="fuelSaveBtn" onclick="saveFuel()" class="hidden px-4 py-2 text-sm text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-lg font-medium">Kaydet</button>
            </div>
        </div>
    </div>

    <div id="usageModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50" onclick="closeUsageModal()"></div>
        <div class="absolute right-0 top-0 h-full w-full max-w-lg bg-white shadow-2xl flex flex-col">
            <div class="flex items-center justify-between px-4 sm:px-6 py-4 border-b border-gray-100">
                <h2 id="usageModalTitle" class="text-lg font-semibold text-gray-900">Kullanım Kaydı</h2>
                <button onclick="closeUsageModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="usageModalBody" class="flex-1 overflow-y-auto p-4 sm:p-6"></div>
            <div class="px-4 sm:px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                <button onclick="closeUsageModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
                <button id="usageSaveBtn" onclick="saveUsage()" class="hidden px-4 py-2 text-sm text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-lg font-medium">Kaydet</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
@media (max-width: 640px) {
    .vehicle-table thead { display: none; }
    .vehicle-table tbody tr {
        display: block;
        padding: 0.75rem;
        border-bottom: 1px solid #f3f4f6;
    }
    .vehicle-table tbody tr td {
        display: flex;
        align-items: center;
        padding: 0.2rem 0;
        border: none;
        text-align: right;
        justify-content: space-between;
        gap: 0.5rem;
    }
    .vehicle-table tbody tr td:before {
        content: attr(data-label);
        font-weight: 600;
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #6b7280;
        text-align: left;
    }
}
</style>
<script>
let currentPage = 1;
let activeTab = 'vehicles';
let searchTimeout;
let personelList = [];
let personelSearchInit = false;

const API = {
    vehicles: {
        list:    '{{ route("admin.vehicles.list") }}',
        store:   '{{ route("admin.vehicles.store") }}',
        widgets: '{{ route("admin.vehicles.widgets") }}',
        create:  '{{ route("admin.vehicles.create") }}',
        edit:    id => `/admin/vehicles/${id}/edit`,
        update:  id => `/admin/vehicles/${id}`,
        destroy: id => `/admin/vehicles/${id}`,
    },
    fuel: {
        list:    '{{ route("admin.vehicles.fuel.list") }}',
        store:   '{{ route("admin.vehicles.fuel.store") }}',
        update:  id => `/admin/vehicles/fuel/${id}`,
        destroy: id => `/admin/vehicles/fuel/${id}`,
        widgets: '{{ route("admin.vehicles.fuel.widgets") }}',
    },
    usage: {
        list:     '{{ route("admin.vehicles.usage.list") }}',
        store:    '{{ route("admin.vehicles.usage.store") }}',
        update:   id => `/admin/vehicles/usage/${id}`,
        complete: id => `/admin/vehicles/usage/${id}/complete`,
        destroy:  id => `/admin/vehicles/usage/${id}`,
    },
};

function loadKPIs() {
    axios.get(API.vehicles.widgets).then(r => {
        document.getElementById('kpiTotal').textContent = r.data.total;
        document.getElementById('kpiActive').textContent = r.data.active;
        document.getElementById('kpiMaintenance').textContent = r.data.maintenance;
        document.getElementById('kpiOutOfService').textContent = r.data.outOfService;
    });
}

function loadData(page) {
    if (activeTab === 'vehicles') loadVehicles(page);
    else if (activeTab === 'fuel') loadFuel(page);
    else if (activeTab === 'usage') loadUsage(page);
}

function loadVehicles(page = 1) {
    currentPage = page;
    const params = {
        page,
        search: document.getElementById('searchInput')?.value || '',
        status: document.getElementById('statusFilter')?.value || '',
        fuel_type: document.getElementById('fuelTypeFilter')?.value || '',
    };

    axios.get(API.vehicles.list, { params }).then(r => {
        const data = r.data;
        const tbody = document.getElementById('vehicleTableBody');
        const items = data.data || [];

        if (!items.length) {
            tbody.innerHTML = `<tr><td colspan="8" class="px-4 sm:px-6 py-12 text-center text-gray-400">
                <div class="flex flex-col items-center gap-2">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    <span class="text-sm">Henüz araç bulunmuyor</span>
                </div>
            </td></tr>`;
            updatePagination(data);
            return;
        }

        tbody.innerHTML = items.map((v, i) => {
            const statusClass = v.status === 'active' ? 'bg-green-100 text-green-700' : v.status === 'maintenance' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700';
            const expiring = (v.insurance_expiring || v.traffic_expiring) ? '<span class="text-red-500 ml-1" title="Süresi geçmiş">⚠️</span>' : '';
            return `<tr class="hover:bg-gray-50 transition">
                <td data-label="#" class="px-4 sm:px-6 py-4 text-gray-500">${(currentPage-1)*15 + i + 1}</td>
                <td data-label="Araç" class="px-4 sm:px-6 py-4"><div class="font-medium text-gray-900">${v.plate || '-'}</div><div class="text-xs text-gray-400">${v.model || ''}</div></td>
                <td data-label="Marka" class="px-4 sm:px-6 py-4 text-gray-600">${v.brand || '-'}</td>
                <td data-label="KM" class="px-4 sm:px-6 py-4 text-gray-600">${v.current_km ? Number(v.current_km).toLocaleString('tr-TR') : '-'}</td>
                <td data-label="Durum" class="px-4 sm:px-6 py-4"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">${v.status_label || v.status}</span></td>
                <td data-label="Personel" class="px-4 sm:px-6 py-4 text-gray-600">${v.assigned_personel || '-'}</td>
                <td data-label="Sigorta" class="px-4 sm:px-6 py-4 text-gray-600">${v.insurance_date ? new Date(v.insurance_date).toLocaleDateString('tr-TR') : '-'}${expiring}</td>
                <td data-label="İşlemler" class="px-4 sm:px-6 py-4 text-right"><div class="flex items-center justify-end gap-1">
                    <button onclick="editVehicle(${v.id})" class="p-1.5 text-gray-400 hover:text-[#02E0FB] hover:bg-[#02E0FB]/10 rounded-lg" title="Düzenle">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button onclick="deleteVehicle(${v.id})" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg" title="Sil">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div></td>
            </tr>`;
        }).join('');
        updatePagination(data);
    });
}

function loadFuel(page = 1) {
    currentPage = page;
    const params = {
        page,
        vehicle_id: document.getElementById('fuelVehicleFilter')?.value || '',
        date_from: document.getElementById('fuelDateFrom')?.value || '',
        date_to: document.getElementById('fuelDateTo')?.value || '',
    };

    axios.get(API.fuel.list, { params }).then(r => {
        const data = r.data;
        const tbody = document.getElementById('fuelTableBody');
        const items = data.data || [];

        if (!items.length) {
            tbody.innerHTML = `<tr><td colspan="8" class="px-4 sm:px-6 py-12 text-center text-gray-400"><span class="text-sm">Henüz yakıt kaydı bulunmuyor</span></td></tr>`;
            updatePagination(data);
            return;
        }

        tbody.innerHTML = items.map(f => {
            const vehicleName = f.vehicle ? `${f.vehicle.plate} ${f.vehicle.brand}` : '-';
            return `<tr class="hover:bg-gray-50 transition">
                <td data-label="Tarih" class="px-4 sm:px-6 py-3 text-gray-600">${f.date ? new Date(f.date).toLocaleDateString('tr-TR') : '-'}</td>
                <td data-label="Araç" class="px-4 sm:px-6 py-3 text-gray-900 font-medium">${vehicleName}</td>
                <td data-label="KM" class="px-4 sm:px-6 py-3 text-gray-600 text-right">${f.km ? Number(f.km).toLocaleString('tr-TR') : '-'}</td>
                <td data-label="Litre" class="px-4 sm:px-6 py-3 text-gray-600 text-right">${f.liters} L</td>
                <td data-label="Birim Fiyat" class="px-4 sm:px-6 py-3 text-gray-600 text-right">${Number(f.unit_price).toFixed(3)} ₺</td>
                <td data-label="Toplam" class="px-4 sm:px-6 py-3 text-gray-900 font-medium text-right">${Number(f.total_cost).toLocaleString('tr-TR')} ₺</td>
                <td data-label="İstasyon" class="px-4 sm:px-6 py-3 text-gray-600">${f.station || '-'}</td>
                <td data-label="İşlemler" class="px-4 sm:px-6 py-3 text-right"><div class="flex items-center justify-end gap-1">
                    <button onclick="deleteFuel(${f.id})" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg" title="Sil">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div></td>
            </tr>`;
        }).join('');
        updatePagination(data);
    });
}

function loadUsage(page = 1) {
    currentPage = page;
    const params = {
        page,
        vehicle_id: document.getElementById('usageVehicleFilter')?.value || '',
        status: document.getElementById('usageStatusFilter')?.value || '',
        date_from: document.getElementById('usageDateFrom')?.value || '',
        date_to: document.getElementById('usageDateTo')?.value || '',
    };

    axios.get(API.usage.list, { params }).then(r => {
        const data = r.data;
        const tbody = document.getElementById('usageTableBody');
        const items = data.data || [];

        if (!items.length) {
            tbody.innerHTML = `<tr><td colspan="7" class="px-4 sm:px-6 py-12 text-center text-gray-400"><span class="text-sm">Henüz kullanım kaydı bulunmuyor</span></td></tr>`;
            updatePagination(data);
            return;
        }

        tbody.innerHTML = items.map(u => {
            const vehicleName = u.vehicle ? `${u.vehicle.plate}` : '-';
            const personelName = u.personel ? `${u.personel.first_name} ${u.personel.last_name}` : '-';
            const route = [u.origin, u.destination].filter(Boolean).join(' → ') || '-';
            const km = u.start_km && u.end_km ? (Number(u.end_km) - Number(u.start_km)).toLocaleString('tr-TR') : '-';
            const statusClass = u.status === 'active' ? 'bg-green-100 text-green-700' : u.status === 'completed' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700';
            const date = u.start_date ? new Date(u.start_date).toLocaleDateString('tr-TR') : '-';
            return `<tr class="hover:bg-gray-50 transition">
                <td data-label="Tarih" class="px-4 sm:px-6 py-3 text-gray-600">${date}</td>
                <td data-label="Araç" class="px-4 sm:px-6 py-3 text-gray-900 font-medium">${vehicleName}</td>
                <td data-label="Personel" class="px-4 sm:px-6 py-3 text-gray-600">${personelName}</td>
                <td data-label="Güzergah" class="px-4 sm:px-6 py-3 text-gray-600 max-w-[200px] truncate">${route}</td>
                <td data-label="KM" class="px-4 sm:px-6 py-3 text-gray-600 text-right">${km}</td>
                <td data-label="Durum" class="px-4 sm:px-6 py-3"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">${u.status_label || u.status}</span></td>
                <td data-label="İşlemler" class="px-4 sm:px-6 py-3 text-right"><div class="flex items-center justify-end gap-1">
                    ${u.status === 'active' ? `<button onclick="completeUsage(${u.id})" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg" title="Tamamla"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></button>` : ''}
                    <button onclick="deleteUsage(${u.id})" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg" title="Sil"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div></td>
            </tr>`;
        }).join('');
        updatePagination(data);
    });
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

document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('border-[#02E0FB]', 'text-[#02E0FB]');
            b.classList.add('border-transparent', 'text-gray-500');
        });
        this.classList.add('border-[#02E0FB]', 'text-[#02E0FB]');
        this.classList.remove('border-transparent', 'text-gray-500');

        document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
        activeTab = this.dataset.tab;
        document.getElementById('tab' + activeTab.charAt(0).toUpperCase() + activeTab.slice(1)).classList.remove('hidden');
        loadData(1);
    });
});

document.getElementById('searchInput')?.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => loadVehicles(1), 400);
});
['statusFilter', 'fuelTypeFilter'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', () => loadVehicles(1));
});
['fuelVehicleFilter', 'fuelDateFrom', 'fuelDateTo'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', () => loadFuel(1));
});
['usageVehicleFilter', 'usageStatusFilter', 'usageDateFrom', 'usageDateTo'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', () => loadUsage(1));
});

// ─── Vehicle CRUD ────────────────────────────────────────────────────

function openCreateModal() {
    axios.get(API.vehicles.create).then(res => {
        document.getElementById('vehicleModalTitle').textContent = 'Yeni Araç';
        document.getElementById('vehicleModalBody').innerHTML = res.data.html;
        document.getElementById('vehicleModal').classList.remove('hidden');
        document.getElementById('vehicleSaveBtn').classList.remove('hidden');
        personelList = res.data.personels || [];
        initPersonelSearch();
    });
}

function editVehicle(id) {
    axios.get(API.vehicles.edit(id)).then(res => {
        document.getElementById('vehicleModalTitle').textContent = 'Araç Düzenle';
        document.getElementById('vehicleModalBody').innerHTML = res.data.html;
        document.getElementById('vehicleModal').classList.remove('hidden');
        document.getElementById('vehicleSaveBtn').classList.remove('hidden');
        document.getElementById('vehicleSaveBtn').dataset.id = id;
        personelList = res.data.personels || [];
        initPersonelSearch(res.data.selected_id);
    });
}

function saveVehicle() {
    const form = document.getElementById('vehicleForm');
    const data = Object.fromEntries(new FormData(form).entries());
    const id = document.getElementById('vehicleSaveBtn').dataset.id;
    const url = id ? API.vehicles.update(id) : API.vehicles.store;
    if (id) data._method = 'PUT';
    axios.post(url, data).then(r => { closeVehicleModal(); toast('success', r.data.message); loadVehicles(); loadKPIs(); }).catch(e => { toast('error', Object.values(e.response?.data?.errors || {}).flat().join('\n') || e.response?.data?.message); });
}

function deleteVehicle(id) {
    Swal.fire({ title: 'Emin misiniz?', text: 'Bu araç silinecek', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Sil', cancelButtonText: 'İptal' }).then(r => {
        if (r.isConfirmed) axios.delete(API.vehicles.destroy(id)).then(r => { toast('success', r.data.message); loadVehicles(); loadKPIs(); });
    });
}

function closeVehicleModal() {
    document.getElementById('vehicleModal').classList.add('hidden');
    document.getElementById('vehicleSaveBtn').classList.add('hidden');
    delete document.getElementById('vehicleSaveBtn').dataset.id;
}

// ─── Fuel CRUD ───────────────────────────────────────────────────────

function openFuelModal() {
    const vehicles = @json($vehicles);
    const html = `<form id="fuelForm" class="space-y-4">
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Araç <span class="text-red-500">*</span></label>
        <select name="vehicle_id" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30"><option value="">Seçiniz</option>
        ${vehicles.map(v => `<option value="${v.id}">${v.plate} - ${v.brand} ${v.model}</option>`).join('')}</select></div>
        <div class="grid grid-cols-2 gap-3">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Tarih <span class="text-red-500">*</span></label>
            <input type="date" name="date" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">KM</label>
            <input type="number" step="1" name="km" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
        </div>
        <div class="grid grid-cols-3 gap-3">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Litre <span class="text-red-500">*</span></label>
            <input type="number" step="0.01" name="liters" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Birim Fiyat (₺) <span class="text-red-500">*</span></label>
            <input type="number" step="0.001" name="unit_price" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Toplam (₺) <span class="text-red-500">*</span></label>
            <input type="number" step="0.01" name="total_cost" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Yakıt Türü</label>
            <select name="fuel_type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"><option value="">Seçiniz</option>
            <option value="benzin">Benzin</option><option value="dizel">Dizel</option><option value="lpg">LPG</option><option value="elektrik">Elektrik</option></select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">İstasyon</label>
            <input type="text" name="station" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
        </div>
        <div><label class="flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="full_refill" value="1" checked> Tam Dolum</label></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Notlar</label>
        <textarea name="notes" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></textarea></div>
    </form>`;
    document.getElementById('fuelModalTitle').textContent = 'Yakıt Kaydı Ekle';
    document.getElementById('fuelModalBody').innerHTML = html;
    document.getElementById('fuelModal').classList.remove('hidden');
    document.getElementById('fuelSaveBtn').classList.remove('hidden');
}

function saveFuel() {
    const form = document.getElementById('fuelForm');
    const data = Object.fromEntries(new FormData(form).entries());
    data.full_refill = data.full_refill === '1' ? 1 : 0;
    axios.post(API.fuel.store, data).then(r => { closeFuelModal(); toast('success', r.data.message); loadFuel(); }).catch(e => { toast('error', Object.values(e.response?.data?.errors || {}).flat().join('\n')); });
}

function deleteFuel(id) {
    Swal.fire({ title: 'Emin misiniz?', text: 'Bu yakıt kaydı silinecek', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Sil', cancelButtonText: 'İptal' }).then(r => {
        if (r.isConfirmed) axios.delete(API.fuel.destroy(id)).then(r => { toast('success', r.data.message); loadFuel(); });
    });
}

function closeFuelModal() {
    document.getElementById('fuelModal').classList.add('hidden');
    document.getElementById('fuelSaveBtn').classList.add('hidden');
}

// ─── Usage CRUD ──────────────────────────────────────────────────────

function openUsageModal() {
    const vehicles = @json($vehicles);
    const personels = @json($personels);
    const html = `<form id="usageForm" class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Araç <span class="text-red-500">*</span></label>
            <select name="vehicle_id" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"><option value="">Seçiniz</option>
            ${vehicles.map(v => `<option value="${v.id}">${v.plate} - ${v.brand} ${v.model}</option>`).join('')}</select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Personel / Şoför</label>
            <select name="personel_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"><option value="">Seçiniz</option>
            ${personels.map(p => `<option value="${p.id}">${p.first_name} ${p.last_name}</option>`).join('')}</select></div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Başlangıç Tarihi <span class="text-red-500">*</span></label>
            <input type="date" name="start_date" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Bitiş Tarihi</label>
            <input type="date" name="end_date" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Başlangıç KM</label>
            <input type="number" step="1" name="start_km" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Bitiş KM</label>
            <input type="number" step="1" name="end_km" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Çıkış Yeri</label>
            <input type="text" name="origin" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Varış Yeri</label>
            <input type="text" name="destination" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
        </div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Kullanım Amacı</label>
        <textarea name="purpose" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></textarea></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Notlar</label>
        <textarea name="notes" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></textarea></div>
    </form>`;
    document.getElementById('usageModalTitle').textContent = 'Kullanım Kaydı Ekle';
    document.getElementById('usageModalBody').innerHTML = html;
    document.getElementById('usageModal').classList.remove('hidden');
    document.getElementById('usageSaveBtn').classList.remove('hidden');
}

function saveUsage() {
    const form = document.getElementById('usageForm');
    const data = Object.fromEntries(new FormData(form).entries());
    axios.post(API.usage.store, data).then(r => { closeUsageModal(); toast('success', r.data.message); loadUsage(); }).catch(e => { toast('error', Object.values(e.response?.data?.errors || {}).flat().join('\n')); });
}

function completeUsage(id) {
    Swal.fire({ title: 'Kullanım Tamamlansın mı?', icon: 'question', showCancelButton: true, confirmButtonColor: '#02E0FB', confirmButtonText: 'Tamamla', cancelButtonText: 'İptal' }).then(r => {
        if (r.isConfirmed) axios.post(API.usage.complete(id)).then(r => { toast('success', r.data.message); loadUsage(); });
    });
}

function deleteUsage(id) {
    Swal.fire({ title: 'Emin misiniz?', text: 'Bu kullanım kaydı silinecek', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Sil', cancelButtonText: 'İptal' }).then(r => {
        if (r.isConfirmed) axios.delete(API.usage.destroy(id)).then(r => { toast('success', r.data.message); loadUsage(); });
    });
}

function closeUsageModal() {
    document.getElementById('usageModal').classList.add('hidden');
    document.getElementById('usageSaveBtn').classList.add('hidden');
}

// ─── Personel Search ─────────────────────────────────────────────────

function initPersonelSearch(selectedId) {
    const input = document.getElementById('personelSearchInput');
    const hidden = document.getElementById('personelIdInput');
    const dropdown = document.getElementById('personelDropdown');
    if (!input) return;

    if (selectedId) {
        const p = personelList.find(x => x.id == selectedId);
        if (p) { input.value = p.name; hidden.value = p.id; }
    } else {
        hidden.value = ''; input.value = '';
    }

    function render(filter) {
        const q = (filter || '').toLowerCase();
        const filtered = q ? personelList.filter(p => p.name.toLowerCase().includes(q)) : personelList;
        dropdown.innerHTML = !filtered.length
            ? '<div class="px-3 py-2 text-sm text-gray-400">Eşleşen personel bulunamadı</div>'
            : filtered.map(p => `<div class="px-3 py-2 text-sm text-gray-700 hover:bg-[#02E0FB]/10 cursor-pointer border-b border-gray-50 last:border-0" data-id="${p.id}">${p.name}</div>`).join('');
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
            if (wrap && !wrap.contains(e.target)) document.getElementById('personelDropdown')?.classList.add('hidden');
        });
        personelSearchInit = true;
    }
    if (!selectedId) render('');
}

// ─── Export ─────────────────────────────────────────────────────────

function toggleExportMenu() {
    document.getElementById('exportMenu')?.classList.toggle('hidden');
}
document.addEventListener('click', function(e) {
    const menu = document.getElementById('exportMenu');
    if (menu && !e.target.closest('.relative.group')) menu.classList.add('hidden');
});

// ─── Init ──────────────────────────────────────────────────────────

function init() {
    loadKPIs();
    loadVehicles(1);
}
</script>
@endpush