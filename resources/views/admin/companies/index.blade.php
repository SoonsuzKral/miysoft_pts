@extends('layouts.app')
@section('title', 'Şirket & Departman Yönetimi')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#02E0FB]">Dashboard</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-800 font-medium">Şirket & Departman</span>
@endsection

@section('page_header')
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Şirket & Departman Yönetimi</h1>
        <p class="text-sm text-gray-500 mt-0.5">Şirketleri, departmanları, pozisyonları ve personel atamalarını yönetin.</p>
    </div>
@endsection

@section('content')

<div x-data="{ tab: 'companies' }" class="space-y-4">
    {{-- Tabs --}}
    <div class="border-b border-gray-200">
        <div class="flex gap-1 overflow-x-auto pb-0.5">
            @php
                $tabs = [
                    ['companies',    'Şirketler',    'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z'],
                    ['departments',  'Departmanlar', 'M3 7h18M3 12h18M3 17h18'],
                    ['positions',    'Pozisyonlar',  'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                    ['personels',    'Personeller',  'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                    ['orgchart',     'Organizasyon', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                ];
            @endphp
            @foreach($tabs as $t)
            <button type="button" @click="tab = '{{ $t[0] }}'; initTab('{{ $t[0] }}')"
                :class="tab === '{{ $t[0] }}' ? 'border-[#02E0FB] text-[#02E0FB] bg-[#02E0FB]/5' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                class="flex items-center gap-2 px-3 py-2.5 text-xs sm:text-sm font-medium border-b-2 transition-all rounded-t-lg whitespace-nowrap">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $t[2] }}"/></svg>
                {{ $t[1] }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- TAB 1: SIRKETLER --}}
    <div x-show="tab === 'companies'" x-cloak>
        <div class="bg-white rounded-xl border border-gray-100 p-3 sm:p-4 mb-4 shadow-sm">
            <div class="flex flex-col sm:flex-row gap-2 sm:items-end sm:flex-wrap">
                <div class="flex-1 min-w-0 sm:max-w-[200px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Arama</label>
                    <input type="text" id="searchCompany" placeholder="Şirket adı..." class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#02E0FB]">
                </div>
                <div class="w-full sm:w-auto sm:min-w-[130px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Durum</label>
                    <select id="filterCompanyStatus" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#02E0FB]">
                        <option value="">Tümü</option>
                        <option value="active">Aktif</option>
                        <option value="trial">Deneme</option>
                        <option value="inactive">Pasif</option>
                        <option value="suspended">Askıda</option>
                    </select>
                </div>
                <div class="flex gap-2 sm:items-end">
                    <button type="button" onclick="loadCompanies()" class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors whitespace-nowrap">Filtrele</button>
                    @can('company.manage')
                    <button type="button" onclick="openCreateCompanyModal()"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-[#02E0FB] rounded-lg hover:bg-cyan-400 transition-colors shadow-sm whitespace-nowrap">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span class="hidden sm:inline">Yeni Şirket</span>
                        <span class="sm:hidden">Ekle</span>
                    </button>
                    @endcan
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Şirket</th>
                            <th class="hidden sm:table-cell px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Alan Adı</th>
                            <th class="hidden md:table-cell px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Şehir</th>
                            <th class="px-3 sm:px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Personel</th>
                            <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Durum</th>
                            <th class="px-3 sm:px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody id="companyTableBody" class="divide-y divide-gray-50">
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Yükleniyor...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="px-3 sm:px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                <div class="text-xs text-gray-500" id="companyTableInfo">—</div>
                <div id="companyTablePagination" class="flex gap-1"></div>
            </div>
        </div>
    </div>

    {{-- TAB 2: DEPARTMANLAR --}}
    <div x-show="tab === 'departments'" x-cloak>
        <div class="bg-white rounded-xl border border-gray-100 p-3 sm:p-4 mb-4 shadow-sm">
            <div class="flex flex-col sm:flex-row gap-2 sm:items-end sm:flex-wrap">
                <div class="flex-1 min-w-0 sm:max-w-[200px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Arama</label>
                    <input type="text" id="searchDept" placeholder="Departman adı..." class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#02E0FB]">
                </div>
                <div class="w-full sm:w-auto sm:min-w-[150px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Üst Departman</label>
                    <select id="filterParent" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#02E0FB]">
                        <option value="">Tümü</option>
                        <option value="root">Yalnızca Kök</option>
                    </select>
                </div>
                <div class="flex gap-2 sm:items-end">
                    <button type="button" onclick="loadDepartments()" class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors whitespace-nowrap">Filtrele</button>
                    <label class="flex items-center gap-1.5 cursor-pointer whitespace-nowrap">
                        <input type="checkbox" id="deptActiveOnly" class="w-4 h-4 rounded text-[#02E0FB]" onchange="loadDepartments()">
                        <span class="text-xs text-gray-600">Aktif</span>
                    </label>
                    @can('department.create')
                    <button type="button" onclick="openCreateDeptModal()"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-[#02E0FB] rounded-lg hover:bg-cyan-400 transition-colors shadow-sm whitespace-nowrap">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span class="hidden sm:inline">Yeni Departman</span>
                        <span class="sm:hidden">Ekle</span>
                    </button>
                    @endcan
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Departman</th>
                            <th class="hidden sm:table-cell px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Üst Departman</th>
                            <th class="hidden md:table-cell px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Yönetici</th>
                            <th class="px-3 sm:px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Personel</th>
                            <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Durum</th>
                            <th class="px-3 sm:px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody id="deptTableBody" class="divide-y divide-gray-50">
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Yükleniyor...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="px-3 sm:px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                <div class="text-xs text-gray-500" id="deptTableInfo">—</div>
                <div id="deptPagination" class="flex gap-1"></div>
            </div>
        </div>
    </div>

    {{-- TAB 3: POZISYONLAR --}}
    <div x-show="tab === 'positions'" x-cloak>
        <div class="bg-white rounded-xl border border-gray-100 p-3 sm:p-4 mb-4 shadow-sm">
            <div class="flex flex-col sm:flex-row gap-2 sm:items-end sm:flex-wrap">
                <div class="flex-1 min-w-0 sm:max-w-[200px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Arama</label>
                    <input type="text" id="searchPosition" placeholder="Pozisyon adı..." class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#02E0FB]">
                </div>
                <div class="flex gap-2 sm:items-end">
                    <button type="button" onclick="loadPositions()" class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors whitespace-nowrap">Filtrele</button>
                    @can('position.create')
                    <button type="button" onclick="openCreatePositionModal()"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-[#02E0FB] rounded-lg hover:bg-cyan-400 transition-colors shadow-sm whitespace-nowrap">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span class="hidden sm:inline">Yeni Pozisyon</span>
                        <span class="sm:hidden">Ekle</span>
                    </button>
                    @endcan
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pozisyon</th>
                            <th class="hidden sm:table-cell px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kod</th>
                            <th class="hidden md:table-cell px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Seviye</th>
                            <th class="px-3 sm:px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Personel</th>
                            <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Durum</th>
                            <th class="px-3 sm:px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody id="positionTableBody" class="divide-y divide-gray-50">
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Yükleniyor...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="px-3 sm:px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                <div class="text-xs text-gray-500" id="positionTableInfo">—</div>
                <div id="positionPagination" class="flex gap-1"></div>
            </div>
        </div>
    </div>

    {{-- TAB 4: PERSONELLER --}}
    <div x-show="tab === 'personels'" x-cloak>
        <div class="bg-white rounded-xl border border-gray-100 p-3 sm:p-4 mb-4 shadow-sm">
            <div class="flex flex-col sm:flex-row gap-2 sm:items-end sm:flex-wrap">
                <div class="flex-1 min-w-0 sm:max-w-[180px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Arama</label>
                    <input type="text" id="searchPersonel" placeholder="Ad, soyad..." class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#02E0FB]">
                </div>
                <div class="w-full sm:w-auto sm:min-w-[140px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Departman</label>
                    <select id="filterPersonelDept" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#02E0FB]">
                        <option value="">Tümü</option>
                    </select>
                </div>
                <div class="w-full sm:w-auto sm:min-w-[120px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Durum</label>
                    <select id="filterPersonelStatus" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#02E0FB]">
                        <option value="">Tümü</option>
                        <option value="active">Aktif</option>
                        <option value="terminated">Ayrıldı</option>
                        <option value="on_leave">İzinde</option>
                        <option value="suspended">Askıda</option>
                    </select>
                </div>
                <div class="flex gap-2 sm:items-end flex-wrap">
                    <button type="button" onclick="loadCompanyPersonels()" class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors whitespace-nowrap">Filtrele</button>
                    <label class="flex items-center gap-1.5 cursor-pointer whitespace-nowrap">
                        <input type="checkbox" id="personelActiveOnly" class="w-4 h-4 rounded text-[#02E0FB]" checked onchange="loadCompanyPersonels()">
                        <span class="text-xs text-gray-600">Aktif</span>
                    </label>
                    @can('personel.create')
                    <button type="button" onclick="openCreatePersonelModal()"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-[#02E0FB] rounded-lg hover:bg-cyan-400 transition-colors shadow-sm whitespace-nowrap">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span class="hidden sm:inline">Yeni Personel</span>
                        <span class="sm:hidden">Ekle</span>
                    </button>
                    <button type="button" onclick="openAssignPersonelModal()"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-500 rounded-lg hover:bg-emerald-400 transition-colors shadow-sm whitespace-nowrap">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span class="hidden sm:inline">Departmana Ata</span>
                        <span class="sm:hidden">Ata</span>
                    </button>
                    @endcan
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Personel</th>
                            <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Departman</th>
                            <th class="hidden sm:table-cell px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pozisyon</th>
                            <th class="hidden md:table-cell px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">E-posta</th>
                            <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Durum</th>
                            <th class="px-3 sm:px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody id="personelTableBody" class="divide-y divide-gray-50">
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Yükleniyor...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="px-3 sm:px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                <div class="text-xs text-gray-500" id="personelTableInfo">—</div>
                <div id="personelPagination" class="flex gap-1"></div>
            </div>
        </div>
    </div>

    {{-- TAB 5: ORGANIZASYON --}}
    <div x-show="tab === 'orgchart'" x-cloak>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 sm:p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Organizasyon Şeması</h3>
                <button type="button" onclick="loadOrgTree()" class="px-3 py-1.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">Yenile</button>
            </div>
            <div id="orgTreeContainer" class="font-mono text-xs sm:text-sm overflow-x-auto"></div>
        </div>
    </div>
</div>

{{-- Global Modal --}}
<div id="globalModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto mx-auto">
        <div class="flex items-center justify-between p-4 sm:p-5 border-b border-gray-100">
            <h3 id="modalTitle" class="text-base sm:text-lg font-bold text-gray-900">Başlık</h3>
            <button type="button" onclick="closeGlobalModal()" class="text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="modalBody" class="p-4 sm:p-5"></div>
        <div id="modalFooter" class="flex justify-end gap-2 p-4 sm:p-5 border-t border-gray-100 bg-gray-50 rounded-b-2xl"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const COMPANY_URLS = {
    list:     '{{ route("admin.companies.index") }}',
    create:   '{{ route("admin.companies.create") }}',
    store:    '{{ route("admin.companies.store") }}',
    edit:     id => `/admin/companies/${id}/edit`,
    update:   id => `/admin/companies/${id}`,
    destroy:  id => `/admin/companies/${id}`,
    departments:  '{{ route("admin.companies.departments") }}',
    positions:    '{{ route("admin.companies.positions") }}',
    personels:    '{{ route("admin.companies.personels") }}',
    orgTree:      '{{ route("admin.companies.org-tree") }}',
    personelCreate:  '{{ route("admin.companies.personel-create") }}',
    personelStore:   '{{ route("admin.companies.personel-store") }}',
    assignForm:      '{{ route("admin.companies.assign-form") }}',
    assignAction:    '{{ route("admin.companies.assign-action") }}',
    unassignAction:  '{{ route("admin.companies.unassign-action") }}',
    deptCreate:   '{{ route("admin.departments.create") }}',
    deptStore:    '{{ route("admin.departments.store") }}',
    deptEdit:     id => `/admin/departments/${id}/edit`,
    deptUpdate:   id => `/admin/departments/${id}`,
    deptDestroy:  id => `/admin/departments/${id}`,
    posCreate:    '{{ route("admin.positions.create") }}',
    posStore:     '{{ route("admin.positions.store") }}',
    posEdit:      id => `/admin/positions/${id}/edit`,
    posUpdate:    id => `/admin/positions/${id}`,
    posDestroy:   id => `/admin/positions/${id}`,
};

let tabInitialized = {};

function initTab(tab) {
    if (tabInitialized[tab]) return;
    tabInitialized[tab] = true;
    switch (tab) {
        case 'companies': loadCompanies(); break;
        case 'departments': loadDepartments(); loadParentDepartments(); break;
        case 'positions': loadPositions(); break;
        case 'personels': loadCompanyPersonels(); loadDeptFilter(); break;
        case 'orgchart': loadOrgTree(); break;
    }
}

function openGlobalModal() {
    document.getElementById('globalModal').classList.remove('hidden');
}

function closeGlobalModal() {
    document.getElementById('globalModal').classList.add('hidden');
}

// ═══════════════════════════════════════════
// TAB 1: SIRKETLER
// ═══════════════════════════════════════════
function loadCompanies(page = 1) {
    const params = {
        page,
        search: document.getElementById('searchCompany').value,
        status: document.getElementById('filterCompanyStatus').value,
        per_page: 15,
    };
    axios.get(COMPANY_URLS.list, { params }).then(res => renderCompanyTable(res.data));
}

function renderCompanyTable(data) {
    const tbody = document.getElementById('companyTableBody');
    const sm = { active: ['Aktif','bg-green-100 text-green-700'], trial: ['Deneme','bg-blue-100 text-blue-700'], inactive: ['Pasif','bg-gray-100 text-gray-600'], suspended: ['Askıda','bg-red-100 text-red-700'] };
    if (!data.data.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">Kayıt bulunamadı</td></tr>`;
        return;
    }
    tbody.innerHTML = data.data.map(c => {
        const [sl, sc] = sm[c.status] ?? ['—', 'bg-gray-100 text-gray-600'];
        return `<tr class="hover:bg-gray-50 transition-colors">
            <td class="px-3 sm:px-4 py-3">
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg bg-[#02E0FB]/10 flex items-center justify-center text-[#02E0FB] font-bold text-xs shrink-0">${c.name[0]}</div>
                    <div class="min-w-0"><p class="font-medium text-gray-800 text-sm truncate">${c.name}</p><p class="text-xs text-gray-400 truncate hidden sm:block">${c.email || '—'}</p></div>
                </div>
            </td>
            <td class="hidden sm:table-cell px-4 py-3 text-gray-600 text-sm">${c.domain || '—'}</td>
            <td class="hidden md:table-cell px-4 py-3 text-gray-600 text-sm">${c.city || '—'}</td>
            <td class="px-3 sm:px-4 py-3 text-center"><span class="font-semibold text-gray-800 text-sm">${c.personels_count ?? 0}</span></td>
            <td class="px-3 sm:px-4 py-3"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${sc}">${sl}</span></td>
            <td class="px-3 sm:px-4 py-3">
                <div class="flex items-center justify-end gap-1">
                    <button type="button" onclick="openEditCompanyModal(${c.id})" class="p-1.5 text-gray-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg" title="Düzenle">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button type="button" onclick="confirmDelete(COMPANY_URLS.destroy(${c.id}), () => loadCompanies())" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg" title="Sil">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
    document.getElementById('companyTableInfo').textContent = `${data.total} kayıt`;
}

function openCreateCompanyModal() {
    axios.get(COMPANY_URLS.create).then(res => {
        document.getElementById('modalTitle').textContent = 'Yeni Şirket Ekle';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modalFooter').innerHTML = `
            <button type="button" onclick="closeGlobalModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
            <button type="button" onclick="submitCompanyForm()" class="px-4 py-2 text-sm text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-lg font-medium">Kaydet</button>`;
        openGlobalModal();
    });
}

function openEditCompanyModal(id) {
    axios.get(COMPANY_URLS.edit(id)).then(res => {
        document.getElementById('modalTitle').textContent = 'Şirketi Düzenle';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modalFooter').innerHTML = `
            <button type="button" onclick="closeGlobalModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
            <button type="button" onclick="submitCompanyForm('${COMPANY_URLS.update(id)}','PUT')" class="px-4 py-2 text-sm text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-lg font-medium">Güncelle</button>`;
        openGlobalModal();
    });
}

function submitCompanyForm(url = COMPANY_URLS.store, method = 'POST') {
    const form = document.getElementById('companyForm');
    const data = Object.fromEntries(new FormData(form).entries());
    axios({ method, url, data }).then(res => {
        closeGlobalModal();
        toast('success', res.data.message);
        loadCompanies();
    });
}

// ═══════════════════════════════════════════
// TAB 2: DEPARTMANLAR
// ═══════════════════════════════════════════
function loadDepartments(page = 1) {
    const params = {
        page,
        search: document.getElementById('searchDept').value,
        parent_id: document.getElementById('filterParent').value,
        active_only: document.getElementById('deptActiveOnly').checked,
        per_page: 20,
    };
    axios.get(COMPANY_URLS.departments, { params }).then(res => renderDeptTable(res.data));
}

function loadParentDepartments() {
    axios.get(COMPANY_URLS.departments, { params: { per_page: 1000, active_only: true } }).then(res => {
        const sel = document.getElementById('filterParent');
        res.data.data.forEach(d => {
            const opt = document.createElement('option');
            opt.value = d.id;
            opt.textContent = d.name;
            sel.appendChild(opt);
        });
    });
}

function renderDeptTable(data) {
    const tbody = document.getElementById('deptTableBody');
    if (!data.data.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">Kayıt bulunamadı</td></tr>`;
        return;
    }
    tbody.innerHTML = data.data.map(d => `<tr class="hover:bg-gray-50 transition-colors">
        <td class="px-3 sm:px-4 py-3"><div><p class="font-medium text-gray-800 text-sm">${d.name}</p>${d.code ? `<p class="text-xs text-gray-400 font-mono">${d.code}</p>` : ''}</div></td>
        <td class="hidden sm:table-cell px-4 py-3 text-gray-600 text-sm">${d.parent?.name || '<span class="text-gray-300">—</span>'}</td>
        <td class="hidden md:table-cell px-4 py-3 text-gray-600 text-sm">${d.manager ? `${d.manager.first_name} ${d.manager.last_name}` : '<span class="text-gray-300">—</span>'}</td>
        <td class="px-3 sm:px-4 py-3 text-center"><span class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[#02E0FB]/10 text-[#02E0FB] text-xs font-bold">${d.personels_count ?? 0}</span></td>
        <td class="px-3 sm:px-4 py-3"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${d.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'}">${d.is_active ? 'Aktif' : 'Pasif'}</span></td>
        <td class="px-3 sm:px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                <button type="button" onclick="openEditDeptModal(${d.id})" class="p-1.5 text-gray-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg" title="Düzenle">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button type="button" onclick="confirmDelete(COMPANY_URLS.deptDestroy(${d.id}), () => loadDepartments())" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg" title="Sil">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </td>
    </tr>`).join('');
    document.getElementById('deptTableInfo').textContent = `${data.total} kayıt`;
}

function openCreateDeptModal() {
    axios.get(COMPANY_URLS.deptCreate).then(res => {
        document.getElementById('modalTitle').textContent = 'Yeni Departman Ekle';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modalFooter').innerHTML = `
            <button type="button" onclick="closeGlobalModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
            <button type="button" onclick="submitDeptForm()" class="px-4 py-2 text-sm text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-lg font-medium">Kaydet</button>`;
        openGlobalModal();
    });
}

function openEditDeptModal(id) {
    axios.get(COMPANY_URLS.deptEdit(id)).then(res => {
        document.getElementById('modalTitle').textContent = 'Departmanı Düzenle';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modalFooter').innerHTML = `
            <button type="button" onclick="closeGlobalModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
            <button type="button" onclick="submitDeptForm('${COMPANY_URLS.deptUpdate(id)}','PUT')" class="px-4 py-2 text-sm text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-lg font-medium">Güncelle</button>`;
        openGlobalModal();
    });
}

function submitDeptForm(url = COMPANY_URLS.deptStore, method = 'POST') {
    const form = document.getElementById('deptForm');
    const data = Object.fromEntries(new FormData(form).entries());
    axios({ method, url, data }).then(res => {
        closeGlobalModal();
        toast('success', res.data.message);
        loadDepartments();
    });
}

// ═══════════════════════════════════════════
// TAB 3: POZISYONLAR
// ═══════════════════════════════════════════
function loadPositions(page = 1) {
    const params = {
        page,
        search: document.getElementById('searchPosition').value,
        per_page: 20,
    };
    axios.get(COMPANY_URLS.positions, { params }).then(res => renderPositionTable(res.data));
}

function renderPositionTable(data) {
    const tbody = document.getElementById('positionTableBody');
    if (!data.data.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">Kayıt bulunamadı</td></tr>`;
        return;
    }
    tbody.innerHTML = data.data.map(p => `<tr class="hover:bg-gray-50 transition-colors">
        <td class="px-3 sm:px-4 py-3"><p class="font-medium text-gray-800 text-sm">${p.title}</p></td>
        <td class="hidden sm:table-cell px-4 py-3 text-gray-600 text-sm font-mono">${p.code || '—'}</td>
        <td class="hidden md:table-cell px-4 py-3 text-center"><span class="text-sm text-gray-600">${p.level ?? '—'}</span></td>
        <td class="px-3 sm:px-4 py-3 text-center"><span class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[#02E0FB]/10 text-[#02E0FB] text-xs font-bold">${p.personels_count ?? 0}</span></td>
        <td class="px-3 sm:px-4 py-3"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${p.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'}">${p.is_active ? 'Aktif' : 'Pasif'}</span></td>
        <td class="px-3 sm:px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                <button type="button" onclick="openEditPositionModal(${p.id})" class="p-1.5 text-gray-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg" title="Düzenle">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button type="button" onclick="confirmDelete(COMPANY_URLS.posDestroy(${p.id}), () => loadPositions())" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg" title="Sil">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </td>
    </tr>`).join('');
    document.getElementById('positionTableInfo').textContent = `${data.total} kayıt`;
}

function openCreatePositionModal() {
    axios.get(COMPANY_URLS.posCreate).then(res => {
        document.getElementById('modalTitle').textContent = 'Yeni Pozisyon Ekle';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modalFooter').innerHTML = `
            <button type="button" onclick="closeGlobalModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
            <button type="button" onclick="submitPositionForm()" class="px-4 py-2 text-sm text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-lg font-medium">Kaydet</button>`;
        openGlobalModal();
    });
}

function openEditPositionModal(id) {
    axios.get(COMPANY_URLS.posEdit(id)).then(res => {
        document.getElementById('modalTitle').textContent = 'Pozisyonu Düzenle';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modalFooter').innerHTML = `
            <button type="button" onclick="closeGlobalModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
            <button type="button" onclick="submitPositionForm('${COMPANY_URLS.posUpdate(id)}','PUT')" class="px-4 py-2 text-sm text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-lg font-medium">Güncelle</button>`;
        openGlobalModal();
    });
}

function submitPositionForm(url = COMPANY_URLS.posStore, method = 'POST') {
    const form = document.getElementById('positionForm');
    const data = Object.fromEntries(new FormData(form).entries());
    axios({ method, url, data }).then(res => {
        closeGlobalModal();
        toast('success', res.data.message);
        loadPositions();
    });
}

// ═══════════════════════════════════════════
// TAB 4: PERSONELLER
// ═══════════════════════════════════════════
function loadCompanyPersonels(page = 1) {
    const params = {
        page,
        search: document.getElementById('searchPersonel').value,
        department_id: document.getElementById('filterPersonelDept').value,
        status: document.getElementById('filterPersonelStatus').value,
        active_only: document.getElementById('personelActiveOnly').checked,
        per_page: 15,
    };
    axios.get(COMPANY_URLS.personels, { params }).then(res => renderPersonelTable(res.data));
}

function loadDeptFilter() {
    axios.get(COMPANY_URLS.departments, { params: { per_page: 1000, active_only: true } }).then(res => {
        const sel = document.getElementById('filterPersonelDept');
        sel.innerHTML = '<option value="">Tümü</option>';
        res.data.data.forEach(d => {
            const opt = document.createElement('option');
            opt.value = d.id;
            opt.textContent = d.name;
            sel.appendChild(opt);
        });
    });
}

function renderPersonelTable(data) {
    const tbody = document.getElementById('personelTableBody');
    if (!data.data.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">Kayıt bulunamadı</td></tr>`;
        return;
    }
    tbody.innerHTML = data.data.map(p => `<tr class="hover:bg-gray-50 transition-colors">
        <td class="px-3 sm:px-4 py-3">
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[#02E0FB]/10 flex items-center justify-center text-[#02E0FB] font-bold text-xs shrink-0">${(p.first_name?.[0] || '') + (p.last_name?.[0] || '')}</div>
                <div class="min-w-0"><p class="font-medium text-gray-800 text-sm truncate">${p.first_name} ${p.last_name}</p></div>
            </div>
        </td>
        <td class="px-3 sm:px-4 py-3 text-gray-600 text-sm">${p.department?.name || '<span class="text-gray-300">—</span>'}</td>
        <td class="hidden sm:table-cell px-4 py-3 text-gray-600 text-sm">${p.position?.title || '<span class="text-gray-300">—</span>'}</td>
        <td class="hidden md:table-cell px-4 py-3 text-gray-600 text-sm truncate max-w-[150px]">${p.email || '—'}</td>
        <td class="px-3 sm:px-4 py-3">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${p.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'}">
                ${p.is_active ? 'Aktif' : 'Pasif'}
            </span>
        </td>
        <td class="px-3 sm:px-4 py-3 text-right">
            <div class="flex items-center justify-end gap-1">
                ${p.department_id ? `<button type="button" onclick="removeOnePersonel(${p.id}, '${p.first_name} ${p.last_name}')" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg" title="Departmandan Cikar">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </button>` : ''}
                <a href="{{ route('admin.personel.index') }}" class="p-1.5 text-gray-400 hover:text-[#02E0FB] hover:bg-[#02E0FB]/5 rounded-lg" title="Detay">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </a>
            </div>
        </td>
    </tr>`).join('');
    document.getElementById('personelTableInfo').textContent = `${data.total} kayıt`;
}

// ─── PERSONEL CRUD ──────────────────────────

function openCreatePersonelModal() {
    axios.get(COMPANY_URLS.personelCreate).then(res => {
        document.getElementById('modalTitle').textContent = 'Yeni Personel Ekle';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modalFooter').innerHTML = `
            <button type="button" onclick="closeGlobalModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
            <button type="button" onclick="submitCompanyPersonelForm()" class="px-4 py-2 text-sm text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-lg font-medium">Kaydet</button>`;
        openGlobalModal();
    });
}

function submitCompanyPersonelForm() {
    const form = document.getElementById('companyPersonelForm');
    const data = Object.fromEntries(new FormData(form).entries());
    axios.post(COMPANY_URLS.personelStore, data).then(res => {
        closeGlobalModal();
        toast('success', res.data.message);
        loadCompanyPersonels();
    }).catch(err => {
        const msg = err.response?.data?.message || 'Bir hata oluştu';
        Swal.fire('Hata', msg, 'error');
    });
}

// ─── DEPARTMANA PERSONEL ATA / CIKAR ──────

function openAssignPersonelModal(deptId) {
    const url = deptId ? COMPANY_URLS.assignForm + '?department_id=' + deptId : COMPANY_URLS.assignForm;
    axios.get(url).then(res => {
        document.getElementById('modalTitle').textContent = 'Personel Departman Atama';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modalFooter').innerHTML = `
            <button type="button" onclick="closeGlobalModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
            <button type="button" onclick="submitAssignPersonel()" class="px-4 py-2 text-sm text-white bg-emerald-500 hover:bg-emerald-400 rounded-lg font-medium">Personelleri Ata</button>`;
        openGlobalModal();
    });
}

function submitAssignPersonel() {
    const department_id = document.getElementById('assignDeptSelect').value;
    if (!department_id) {
        Swal.fire('Uyarı', 'Lütfen bir departman seçin.', 'warning');
        return;
    }
    const checkedPersonels = Array.from(document.querySelectorAll('input[name="personel_ids[]"]:checked'));
    if (!checkedPersonels.length) {
        Swal.fire('Uyarı', 'Lütfen en az bir personel seçin.', 'warning');
        return;
    }
    const personel_ids = checkedPersonels.map(cb => cb.value);
    axios.post(COMPANY_URLS.assignAction, { department_id, personel_ids }).then(res => {
        closeGlobalModal();
        toast('success', res.data.message);
        loadCompanyPersonels();
    }).catch(err => {
        const msg = err.response?.data?.message || 'Bir hata oluştu';
        Swal.fire('Hata', msg, 'error');
    });
}

function showUnassigned() {
    document.getElementById('unassignedPanel').classList.remove('hidden');
    document.getElementById('deptPersonelsPanel').classList.add('hidden');
    document.getElementById('tabUnassignedBtn').style.background = '#02E0FB';
    document.getElementById('tabUnassignedBtn').style.color = 'white';
    document.getElementById('tabDeptBtn').style.background = '#f3f4f6';
    document.getElementById('tabDeptBtn').style.color = '#4b5563';
    document.getElementById('modalFooter').innerHTML = `
        <button type="button" onclick="closeGlobalModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
        <button type="button" onclick="submitAssignPersonel()" class="px-4 py-2 text-sm text-white bg-emerald-500 hover:bg-emerald-400 rounded-lg font-medium">Personelleri Ata</button>`;
}

function showDeptPersonels() {
    document.getElementById('unassignedPanel').classList.add('hidden');
    document.getElementById('deptPersonelsPanel').classList.remove('hidden');
    document.getElementById('tabUnassignedBtn').style.background = '#f3f4f6';
    document.getElementById('tabUnassignedBtn').style.color = '#4b5563';
    document.getElementById('tabDeptBtn').style.background = '#02E0FB';
    document.getElementById('tabDeptBtn').style.color = 'white';
    document.getElementById('modalFooter').innerHTML = `
        <button type="button" onclick="closeGlobalModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>`;
}

function filterAssignPersonels() {
    const deptId = document.getElementById('assignDeptSelect').value;
    if (deptId) {
        openAssignPersonelModal(deptId);
    }
}

function removeOnePersonel(id, name) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: name + ' departmandan çıkarılacak.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#FA6001',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Evet, Çıkar',
        cancelButtonText: 'İptal'
    }).then(result => {
        if (result.isConfirmed) {
            axios.post(COMPANY_URLS.unassignAction, { personel_ids: [id] }).then(res => {
                toast('success', res.data.message);
                loadCompanyPersonels();
            });
        }
    });
}

function removeFromDept(id, name) {
    removeOnePersonel(id, name);
}

// ═══════════════════════════════════════════
// TAB 5: ORGANIZASYON
// ═══════════════════════════════════════════
function loadOrgTree() {
    axios.get(COMPANY_URLS.orgTree).then(res => {
        const container = document.getElementById('orgTreeContainer');
        container.innerHTML = res.data.data.length
            ? renderTree(res.data.data, 0)
            : '<p class="text-gray-400 text-center py-8">Henüz departman eklenmemiş.</p>';
    });
}

function renderTree(nodes, depth) {
    return nodes.map(node => {
        const deptIcon = node.all_children?.length ? '📁' : '📂';
        let html = `<div class="py-1.5 hover:bg-gray-50 px-2 rounded flex items-center gap-2 flex-wrap" style="padding-left: ${depth * 20 + 8}px">
            <span>${deptIcon}</span>
            <span class="font-medium text-gray-800 text-sm">${node.name}</span>
            ${node.manager ? `<span class="text-xs text-gray-400">(${node.manager.first_name} ${node.manager.last_name})</span>` : ''}
            <span class="text-xs text-gray-400 ml-auto">${node.personels_count ?? 0} personel</span>
        </div>`;
        if (node.all_children?.length) {
            html += renderTree(node.all_children, depth + 1);
        }
        return html;
    }).join('');
}
</script>
@endpush
