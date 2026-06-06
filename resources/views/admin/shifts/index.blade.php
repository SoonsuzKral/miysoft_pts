@extends('layouts.app')
@section('title', 'Vardiya Yönetimi')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#02E0FB]">Dashboard</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-800 font-medium">Vardiya Yönetimi</span>
@endsection

@section('page_header')
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Vardiya Yönetimi</h1>
        <p class="text-sm text-gray-500 mt-0.5">Vardiyaları tanımlayın, personellere atayın ve canlı takip edin.</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <button onclick="exportExcel()"
            class="flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
            <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Excel
        </button>
        <button onclick="exportPdf()"
            class="flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
            <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            PDF
        </button>
        <button onclick="setView('roster')" id="rosterBtn"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Takvim
        </button>
        @can('shift.create')
        <button onclick="openAssignModal()"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4 text-[#02E0FB]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Vardiya Ata
        </button>
        <button onclick="openCreateShiftModal()"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-[#02E0FB] rounded-lg hover:bg-cyan-400 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Yeni Vardiya
        </button>
        @endcan
    </div>
@endsection

@section('content')
{{-- Sekmeler --}}
<div class="flex gap-1 border-b border-gray-200 mb-5 overflow-x-auto">
    @foreach([['list','Vardiya Listesi'],['live','Canlı Durum'],['roster','Atama Takvimi'],['swaps','Değişim Talepleri']] as [$key,$label])
    <button onclick="setView('{{ $key }}')" id="tab-{{ $key }}"
        class="whitespace-nowrap px-4 py-2.5 text-sm font-medium border-b-2 transition-all
        {{ $key === 'list' ? 'border-[#02E0FB] text-[#02E0FB]' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
        {{ $label }}
    </button>
    @endforeach
</div>

{{-- VARDİYA LİSTESİ --}}
<div id="view-list">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="shiftCards">
        @foreach($shifts as $shift)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="h-2" style="background-color: {{ $shift->color }}"></div>
            <div class="p-4">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ $shift->name }}</h3>
                        <div class="flex items-center gap-1 mt-1 flex-wrap">
                            @if($shift->is_night_shift)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-indigo-100 text-indigo-700">Gece</span>
                            @endif
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs {{ $shift->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $shift->is_active ? 'Aktif' : 'Pasif' }}
                            </span>
                        </div>
                    </div>
                    @can('shift.create')
                    <div class="flex gap-1">
                        <button onclick="openEditShiftModal({{ $shift->id }})" class="p-1 text-gray-400 hover:text-amber-500 hover:bg-amber-50 rounded">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        @can('shift.manage')
                        <button onclick="confirmDelete('/admin/shifts/{{ $shift->id }}', () => location.reload())" class="p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                        @endcan
                    </div>
                    @endcan
                </div>
                <div class="space-y-1 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ substr($shift->start_time, 0, 5) }} — {{ substr($shift->end_time, 0, 5) }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span class="font-medium text-[#02E0FB]">{{ $shift->duration_label }}</span>
                    </div>
                    @if($shift->breaks)
                    <div class="flex items-center gap-2 text-xs text-gray-400">
                        <span>{{ count($shift->breaks) }} mola</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach

        @if($shifts->isEmpty())
        <div class="col-span-full py-12 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p>Henüz vardiya tanımlanmamış</p>
            <button onclick="openCreateShiftModal()" class="mt-3 text-sm text-[#02E0FB] hover:underline">İlk vardiyayı oluştur</button>
        </div>
        @endif
    </div>
</div>

{{-- CANLI DURUM --}}
<div id="view-live" class="hidden">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-3xl font-bold text-green-600" id="liveActiveCount">0</p>
            <p class="text-xs text-gray-500 mt-1">Aktif Vardiya</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-3xl font-bold text-amber-600" id="liveLateCount">0</p>
            <p class="text-xs text-gray-500 mt-1">Geç Kalan</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-3xl font-bold text-blue-600" id="liveTotalCount">0</p>
            <p class="text-xs text-gray-500 mt-1">Bugünkü Atama</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-3xl font-bold text-purple-600" id="liveCompletedCount">0</p>
            <p class="text-xs text-gray-500 mt-1">Tamamlanan</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-4">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative w-full sm:w-auto flex-1 max-w-xs">
                <input type="text" id="liveSearch" oninput="filterLiveTable()" placeholder="Personel ara..."
                    class="w-full text-sm border border-gray-200 rounded-lg pl-8 pr-3 py-2 focus:outline-none focus:border-[#02E0FB]">
                <svg class="absolute left-2.5 top-2.5 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <div>
                <select id="liveStatusFilter" onchange="filterLiveTable()" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#02E0FB]">
                    <option value="">Tümü</option>
                    <option value="on_shift">Vardiyada</option>
                    <option value="late">Geç Geldi</option>
                    <option value="completed">Tamamlandı</option>
                    <option value="left_early">Erken Ayrıldı</option>
                    <option value="pending">Bekliyor</option>
                    <option value="missed">Kaçırıldı</option>
                </select>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Personel</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Departman</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Vardiya</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Durum</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Giriş</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">İşlem</th>
                    </tr>
                </thead>
                <tbody id="liveStatusBody">
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Yükleniyor...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- TAKVİM GÖRÜNÜMÜ --}}
<div id="view-roster" class="hidden">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-4">
        <div class="flex flex-wrap items-center gap-3">
            <div class="w-full sm:w-auto">
                <label class="block text-xs font-medium text-gray-500 mb-1">Personel</label>
                <select id="rosterPersonelFilter" class="w-full sm:w-48 text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#02E0FB]" onchange="calendar?.refetchEvents()">
                    <option value="">Tümü</option>
                    @foreach($personels as $p)
                        <option value="{{ $p->id }}">{{ $p->first_name }} {{ $p->last_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-auto relative">
                <label class="block text-xs font-medium text-gray-500 mb-1">&nbsp;</label>
                <input type="text" id="rosterSearch" oninput="filterRosterSelect()" placeholder="Personel ara..."
                    class="w-full sm:w-40 text-sm border border-gray-200 rounded-lg pl-8 pr-3 py-2 focus:outline-none focus:border-[#02E0FB]">
                <svg class="absolute left-2.5 top-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" style="margin-top:2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-2 sm:p-4">
        <div id="shiftCalendar"></div>
    </div>
</div>

{{-- DEĞİŞİM TALEPLERİ --}}
<div id="view-swaps" class="hidden">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Talep Eden</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Hedef Personel</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Tarihler</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Durum</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">İşlem</th>
                    </tr>
                </thead>
                <tbody id="swapTableBody">
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Yükleniyor...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Vardiya Ata Modal --}}
<div id="assignModal" class="hidden fixed inset-0 z-[110] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('assignModal').classList.add('hidden')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Vardiya Ata</h3>
            <button onclick="document.getElementById('assignModal').classList.add('hidden')" class="p-1 text-gray-400 hover:text-gray-700 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Vardiya <span class="text-red-500">*</span></label>
                <select id="assignShiftId" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#02E0FB]">
                    <option value="">Vardiya seçin</option>
                    @foreach($shifts as $s)
                        <option value="{{ $s->id }}">{{ $s->name }} ({{ substr($s->start_time,0,5) }}-{{ substr($s->end_time,0,5) }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Vardiya Planı <span class="text-red-500">*</span></label>
                <select id="assignPlanId" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#02E0FB]">
                    <option value="">Plan seçin</option>
                    @foreach($plans as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Personeller <span class="text-red-500">*</span></label>
                <div class="relative mb-1">
                    <input type="text" id="assignPersonelSearch" oninput="filterAssignPersonel()" placeholder="Personel ara..."
                        class="w-full text-sm border border-gray-200 rounded-lg pl-8 pr-3 py-2 focus:outline-none focus:border-[#02E0FB]">
                    <svg class="absolute left-2.5 top-2.5 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <div id="assignPersonelList" class="border border-gray-200 rounded-lg overflow-hidden max-h-40 overflow-y-auto">
                    @foreach($personels as $p)
                    <label class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-50 last:border-0">
                        <input type="checkbox" name="personel_ids[]" value="{{ $p->id }}" class="w-4 h-4 rounded text-[#02E0FB]">
                        <span class="text-sm text-gray-700">{{ $p->first_name }} {{ $p->last_name }}</span>
                    </label>
                    @endforeach
                </div>
                <div class="flex gap-2 mt-1">
                    <button onclick="document.querySelectorAll('[name=\'personel_ids[]\']').forEach(c=>c.checked=true)" class="text-xs text-[#02E0FB] hover:underline">Tümünü seç</button>
                    <span class="text-xs text-gray-300">|</span>
                    <button onclick="document.querySelectorAll('[name=\'personel_ids[]\']').forEach(c=>c.checked=false)" class="text-xs text-gray-400 hover:underline">Temizle</button>
                    <span class="text-xs text-gray-300">|</span>
                    <span id="assignPersonelCount" class="text-xs text-gray-400">0 seçili</span>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tarihler <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <div>
                        <label class="text-xs text-gray-400">Başlangıç</label>
                        <input type="date" id="assignDateFrom" min="{{ today()->toDateString() }}" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#02E0FB]">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400">Bitiş</label>
                        <input type="date" id="assignDateTo" min="{{ today()->toDateString() }}" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#02E0FB]">
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-1">Aralıktaki tüm günlere atama yapılır.</p>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="document.getElementById('assignModal').classList.add('hidden')" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
            <button onclick="submitAssignment()" class="px-5 py-2 text-sm text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-lg font-medium">Atamaları Kaydet</button>
        </div>
    </div>
</div>

{{-- Global Modal --}}
<div id="globalModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div onclick="closeModal()" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto border border-gray-100">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <h2 id="modalTitle" class="text-lg font-bold text-gray-900">Başlık</h2>
            <button onclick="closeModal()" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="modalBody" class="px-6 py-5"></div>
        <div id="modalFooter" class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-2"></div>
    </div>
</div>

{{-- Clock-In/Out Modal --}}
<div id="clockModal" class="hidden fixed inset-0 z-[110] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeClockModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4" id="clockModalTitle">Giriş/Çıkış Yap</h3>
        <div class="space-y-3">
            <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">Personel</label>
                <div class="relative">
                    <input type="text" id="clockPersonelSearch" oninput="filterClockPersonel()" placeholder="Personel ara..."
                        class="w-full text-sm border border-gray-200 rounded-lg pl-8 pr-3 py-2 focus:outline-none focus:border-[#02E0FB]" autocomplete="off">
                    <svg class="absolute left-2.5 top-2.5 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="hidden" id="clockPersonelId" value="">
                </div>
                <div id="clockPersonelOptions" class="hidden mt-1 border border-gray-200 rounded-lg max-h-40 overflow-y-auto bg-white shadow-lg absolute z-20 left-0 right-0"></div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Vardiya <span class="text-xs text-gray-400">(opsiyonel)</span></label>
                <select id="clockShiftId" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#02E0FB]">
                    <option value="">Otomatik belirle</option>
                    @foreach($shifts as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Not</label>
                <input type="text" id="clockNote" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#02E0FB]" placeholder="İsteğe bağlı">
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6">
            <button onclick="closeClockModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
            <button onclick="submitClockIn()" class="px-4 py-2 text-sm text-white bg-green-600 hover:bg-green-700 rounded-lg font-medium">Giriş Yap</button>
            <button onclick="submitClockOut()" class="px-4 py-2 text-sm text-white bg-red-600 hover:bg-red-700 rounded-lg font-medium">Çıkış Yap</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.11/locales/tr.global.min.js"></script>
<style>
@media (max-width: 640px) {
    .fc-toolbar-title { font-size: 1em !important; }
    .fc-header-toolbar { flex-direction: column; gap: 8px; }
    .fc-header-toolbar .fc-toolbar-chunk { display: flex; justify-content: center; flex-wrap: wrap; gap: 4px; }
    .fc-button { font-size: 11px !important; padding: 4px 8px !important; }
    .fc-daygrid-day-number, .fc-col-header-cell-cushion { font-size: 11px !important; }
}
</style>
<script>
const SHIFT_URLS = {
    list:       '{{ route("admin.shifts.list") }}',
    create:     '{{ route("admin.shifts.create") }}',
    store:      '{{ route("admin.shifts.store") }}',
    edit:       id => `/admin/shifts/${id}/edit`,
    update:     id => `/admin/shifts/${id}`,
    roster:     '{{ route("admin.shifts.roster") }}',
    assign:     '{{ route("admin.shifts.assign") }}',
    swaps:      '{{ route("admin.shifts.swap.index") }}',
    liveStatus: '{{ route("admin.shifts.live-status") }}',
    clockIn:    '{{ route("admin.shifts.clock-in") }}',
    clockOut:   '{{ route("admin.shifts.clock-out") }}',
    exportExcel:'{{ route("admin.shifts.export.excel") }}',
    exportPdf:  '{{ route("admin.shifts.export.pdf") }}',
};

let calendar = null;
let currentView = 'list';
let liveRefreshInterval = null;

function setView(v) {
    ['list','live','roster','swaps'].forEach(id => {
        const el = document.getElementById(`view-${id}`);
        const tab = document.getElementById(`tab-${id}`);
        if (el) el.classList.toggle('hidden', id !== v);
        if (tab) {
            tab.classList.toggle('border-[#02E0FB]', id === v);
            tab.classList.toggle('text-[#02E0FB]', id === v);
            tab.classList.toggle('border-transparent', id !== v);
            tab.classList.toggle('text-gray-500', id !== v);
        }
    });

    currentView = v;

    if (v === 'roster' && !calendar) initCalendar();
    if (v === 'roster' && calendar) setTimeout(() => calendar.updateSize(), 100);
    if (v === 'swaps') loadSwaps();
    if (v === 'live') { loadLiveStatus(); startLiveRefresh(); }
    else stopLiveRefresh();
}

function startLiveRefresh() {
    stopLiveRefresh();
    liveRefreshInterval = setInterval(loadLiveStatus, 30000);
}

function stopLiveRefresh() {
    if (liveRefreshInterval) { clearInterval(liveRefreshInterval); liveRefreshInterval = null; }
}

function initCalendar() {
    const el = document.getElementById('shiftCalendar');
    const isMobile = window.innerWidth < 768;

    calendar = new FullCalendar.Calendar(el, {
        locale: 'tr',
        initialView: isMobile ? 'dayGridMonth' : 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },
        height: 'auto',
        contentHeight: 'auto',
        aspectRatio: isMobile ? 0.8 : 1.5,
        handleWindowResize: true,
        windowResize: function(view) {
            const mobile = window.innerWidth < 768;
            this.aspectRatio = mobile ? 0.8 : 1.5;
            if (mobile && this.view.type !== 'dayGridMonth') this.changeView('dayGridMonth');
        },
        editable: false,
        eventSources: [{
            url: SHIFT_URLS.roster,
            extraParams: () => ({ personel_id: document.getElementById('rosterPersonelFilter').value }),
            failure: () => toast('error', 'Takvim yüklenemedi.'),
        }],
        eventClick: info => {
            Swal.fire({
                title: info.event.title.replace('\n', ' - '),
                html: `<p>Tarih: ${info.event.startStr}</p>
                       <p>Vardiya: ${info.event.extendedProps.shift_name}</p>`,
                icon: 'info',
                showCancelButton: true,
                cancelButtonText: 'Kapat',
                confirmButtonText: 'Atamayı Sil',
                confirmButtonColor: '#FA6001',
            }).then(r => {
                if (r.isConfirmed) {
                    axios.delete(`/admin/shifts/assignments/${info.event.id}`).then(res => {
                        toast('success', res.data.message);
                        calendar.refetchEvents();
                    });
                }
            });
        },
        eventDidMount: function(info) {
            if (window.innerWidth < 640) {
                info.el.style.fontSize = '10px';
                info.el.style.padding = '1px 2px';
            }
        },
    });
    calendar.render();
}

function loadSwaps() {
    axios.get(SHIFT_URLS.swaps).then(res => {
        const tbody = document.getElementById('swapTableBody');
        if (!res.data.data.length) {
            tbody.innerHTML = `<tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Değişim talebi yok</td></tr>`;
            return;
        }
        tbody.innerHTML = res.data.data.map(s => `
            <tr class="hover:bg-gray-50 border-b border-gray-50">
                <td class="px-4 py-3 text-sm text-gray-700">${s.requester?.first_name ?? '#'+s.requester_id} ${s.requester?.last_name ?? ''}</td>
                <td class="px-4 py-3 text-sm text-gray-700 hidden sm:table-cell">${s.target_personel?.first_name ?? '#'+s.target_personel_id} ${s.target_personel?.last_name ?? ''}</td>
                <td class="px-4 py-3 text-center text-xs text-gray-500">${s.requester_date} ↔ ${s.target_date}</td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                        ${s.status === 'approved' ? 'bg-green-100 text-green-700' : s.status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'}">
                        ${s.status === 'approved' ? 'Onaylı' : s.status === 'rejected' ? 'Red' : 'Bekliyor'}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    ${s.status === 'pending'
                        ? `<button onclick="axios.post('/admin/shifts/swap-requests/${s.id}/approve').then(r=>{toast('success',r.data.message);loadSwaps()})" class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200 mr-1">Onayla</button>
                           <button onclick="axios.post('/admin/shifts/swap-requests/${s.id}/reject').then(r=>{toast('success',r.data.message);loadSwaps()})" class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200">Reddet</button>`
                        : ''}
                </td>
            </tr>`).join('');
    });
}

function loadLiveStatus() {
    axios.get(SHIFT_URLS.liveStatus).then(res => {
        document.getElementById('liveActiveCount').textContent = res.data.active_count;
        document.getElementById('liveLateCount').textContent = res.data.active?.filter(a => a.late > 15).length ?? 0;
        document.getElementById('liveTotalCount').textContent = res.data.total_today;
        document.getElementById('liveCompletedCount').textContent = res.data.all?.filter(a => a.status === 'completed' || a.status === 'left_early').length ?? 0;

        const body = document.getElementById('liveStatusBody');
        if (!res.data.all?.length) {
            body.innerHTML = `<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Bugün vardiya ataması bulunmuyor</td></tr>`;
            return;
        }
        body.innerHTML = res.data.all.map(a => {
            const statusMap = {
                pending: '<span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">Bekliyor</span>',
                on_shift: '<span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Vardiyada</span>',
                completed: '<span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">Tamamlandı</span>',
                missed: '<span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700">Kaçırıldı</span>',
                late: '<span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-700">Geç Geldi</span>',
                left_early: '<span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-orange-100 text-orange-700">Erken Ayrıldı</span>',
            };
            const isClockedIn = a.status === 'on_shift' || a.status === 'late';
            const isClockedOut = a.clock_out;
            const personelName = a.personel || '-';
            const deptName = a.department || '-';
            return `<tr data-personel="${(a.personel || '').toLowerCase()}" data-status="${a.status}" class="hover:bg-gray-50 border-b border-gray-50">
                <td class="px-4 py-3 text-sm text-gray-700 font-medium">${personelName}</td>
                <td class="px-4 py-3 text-sm text-gray-500 hidden sm:table-cell">${deptName}</td>
                <td class="px-4 py-3 text-sm text-gray-600">${a.shift_name || '-'} <span class="text-xs text-gray-400">${a.start_time || ''}</span></td>
                <td class="px-4 py-3 text-center">${statusMap[a.status] || a.status}</td>
                <td class="px-4 py-3 text-center text-xs text-gray-500 hidden md:table-cell">
                    ${a.clock_in || '-'} ${a.clock_out ? '→ ' + a.clock_out : ''}
                    ${a.late > 0 ? `<br><span class="text-amber-600">${a.late} dk geç</span>` : ''}
                    ${a.early > 0 ? `<br><span class="text-orange-600">${a.early} dk erken</span>` : ''}
                </td>
                <td class="px-4 py-3 text-right">
                    ${!isClockedIn && !isClockedOut
                        ? `<button onclick="openClockModal(${a.personel_id}, '${(a.personel || '').replace(/'/g, "\\'")}')" class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200">Giriş</button>`
                        : isClockedIn && !isClockedOut
                            ? `<button onclick="clockOutPerson(${a.personel_id})" class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200">Çıkış</button>`
                            : '<span class="text-xs text-gray-400">Tamam</span>'}
                </td>
            </tr>`;
        }).join('');
        filterLiveTable();
    });
}

function openClockModal(personelId, personelName) {
    if (personelId) {
        document.getElementById('clockPersonelId').value = personelId;
        if (personelName) document.getElementById('clockPersonelSearch').value = personelName;
    }
    document.getElementById('clockModal').classList.remove('hidden');
}

function closeClockModal() {
    document.getElementById('clockModal').classList.add('hidden');
}

function submitClockIn() {
    const personelId = document.getElementById('clockPersonelId').value;
    const shiftId = document.getElementById('clockShiftId').value;
    const note = document.getElementById('clockNote').value;
    if (!personelId) { toast('warning', 'Personel seçin.'); return; }
    const today = new Date().toISOString().split('T')[0];
    axios.post(SHIFT_URLS.clockIn, { personel_id: personelId, shift_id: shiftId || null, date: today, note }).then(res => {
        toast('success', res.data.message);
        closeClockModal();
        loadLiveStatus();
    }).catch(e => toast('error', e.response?.data?.message || 'Hata oluştu'));
}

function submitClockOut() {
    const personelId = document.getElementById('clockPersonelId').value;
    const note = document.getElementById('clockNote').value;
    if (!personelId) { toast('warning', 'Personel seçin.'); return; }
    const today = new Date().toISOString().split('T')[0];
    axios.post(SHIFT_URLS.clockOut, { personel_id: personelId, date: today, note }).then(res => {
        toast('success', res.data.message);
        closeClockModal();
        loadLiveStatus();
    }).catch(e => toast('error', e.response?.data?.message || 'Hata oluştu'));
}

function clockOutPerson(personelId) {
    const today = new Date().toISOString().split('T')[0];
    axios.post(SHIFT_URLS.clockOut, { personel_id: personelId, date: today }).then(res => {
        toast('success', res.data.message);
        loadLiveStatus();
    }).catch(e => toast('error', e.response?.data?.message || 'Hata'));
}

function exportExcel() {
    const type = currentView === 'roster' ? 'roster' : currentView === 'live' ? 'attendance' : currentView === 'swaps' ? 'swaps' : 'shifts';
    window.location.href = SHIFT_URLS.exportExcel + '?type=' + type;
}

function exportPdf() {
    const type = currentView === 'roster' ? 'roster' : currentView === 'live' ? 'attendance' : 'shifts';
    window.location.href = SHIFT_URLS.exportPdf + '?type=' + type;
}

function openCreateShiftModal() {
    axios.get(SHIFT_URLS.create).then(res => {
        document.getElementById('modalTitle').textContent = 'Yeni Vardiya Oluştur';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modalFooter').innerHTML = `
            <button onclick="document.getElementById('globalModal').classList.add('hidden')" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
            <button onclick="submitShiftForm()" class="px-4 py-2 text-sm text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-lg font-medium">Kaydet</button>`;
        document.getElementById('globalModal').classList.remove('hidden');
    });
}

function openEditShiftModal(id) {
    axios.get(SHIFT_URLS.edit(id)).then(res => {
        document.getElementById('modalTitle').textContent = 'Vardiyayı Düzenle';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modalFooter').innerHTML = `
            <button onclick="document.getElementById('globalModal').classList.add('hidden')" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
            <button onclick="submitShiftForm('${SHIFT_URLS.update(id)}','PUT')" class="px-4 py-2 text-sm text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-lg font-medium">Güncelle</button>`;
        document.getElementById('globalModal').classList.remove('hidden');
    });
}

function submitShiftForm(url = SHIFT_URLS.store, method = 'POST') {
    const form = document.getElementById('shiftForm');
    const data = Object.fromEntries(new FormData(form).entries());
    data.is_night_shift = form.querySelector('[name="is_night_shift"]')?.checked ? 1 : 0;
    data.is_active = form.querySelector('[name="is_active"]')?.checked ? 1 : 0;
    axios({ method, url, data }).then(res => {
        document.getElementById('globalModal').classList.add('hidden');
        toast('success', res.data.message);
        location.reload();
    });
}

function closeModal() {
    const m = document.getElementById('globalModal');
    if (m) m.classList.add('hidden');
}

function filterAssignPersonel() {
    const q = document.getElementById('assignPersonelSearch').value.toLowerCase();
    const labels = document.querySelectorAll('#assignPersonelList label');
    labels.forEach(l => {
        const name = l.querySelector('span')?.textContent.toLowerCase() || '';
        l.style.display = name.includes(q) ? '' : 'none';
    });
    updateAssignCount();
}

function updateAssignCount() {
    const checked = document.querySelectorAll('[name="personel_ids[]"]:checked').length;
    document.getElementById('assignPersonelCount').textContent = checked + ' seçili';
}

document.addEventListener('change', function(e) {
    if (e.target.matches('[name="personel_ids[]"]')) updateAssignCount();
});

const CLOCK_PERSONEL_LIST = [
@foreach($personels as $p)
    {id: {{ $p->id }}, name: '{{ str_replace("'", "\\'", $p->first_name . ' ' . $p->last_name) }}'},
@endforeach
];

function filterClockPersonel() {
    const q = document.getElementById('clockPersonelSearch').value.toLowerCase();
    const container = document.getElementById('clockPersonelOptions');

    if (!q) { container.classList.add('hidden'); container.innerHTML = ''; return; }

    const filtered = CLOCK_PERSONEL_LIST.filter(p => p.name.toLowerCase().includes(q));

    if (!filtered.length) {
        container.innerHTML = '<div class="px-3 py-2 text-sm text-gray-400">Eşleşen bulunamadı</div>';
        container.classList.remove('hidden');
        return;
    }
    container.innerHTML = filtered.map(p =>
        `<div onclick="selectClockPersonel(${p.id}, '${p.name.replace(/'/g, "\\'")}')" class="px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 cursor-pointer border-b border-gray-50">${p.name}</div>`
    ).join('');
    container.classList.remove('hidden');
}

function selectClockPersonel(id, name) {
    document.getElementById('clockPersonelSearch').value = name;
    document.getElementById('clockPersonelId').value = id;
    document.getElementById('clockPersonelOptions').classList.add('hidden');
}

document.addEventListener('click', function(e) {
    const container = document.getElementById('clockPersonelOptions');
    if (container && !e.target.closest('#clockPersonelSearch') && !e.target.closest('#clockPersonelOptions')) {
        container.classList.add('hidden');
    }
});

function filterRosterSelect() {
    const q = document.getElementById('rosterSearch').value.toLowerCase();
    const select = document.getElementById('rosterPersonelFilter');
    Array.from(select.options).forEach(opt => {
        if (opt.value === '') return;
        opt.hidden = !opt.text.toLowerCase().includes(q);
    });
}

function filterLiveTable() {
    const q = document.getElementById('liveSearch').value.toLowerCase();
    const status = document.getElementById('liveStatusFilter').value;
    const rows = document.querySelectorAll('#liveStatusBody tr');
    rows.forEach(row => {
        if (!row.dataset.personel) return;
        const matchName = !q || row.dataset.personel.toLowerCase().includes(q);
        const matchStatus = !status || row.dataset.status === status;
        row.style.display = (matchName && matchStatus) ? '' : 'none';
    });
}

function openAssignModal() {
    document.getElementById('assignModal').classList.remove('hidden');
    updateAssignCount();
}

function submitAssignment() {
    const shiftId   = document.getElementById('assignShiftId').value;
    const planId    = document.getElementById('assignPlanId').value;
    const dateFrom  = document.getElementById('assignDateFrom').value;
    const dateTo    = document.getElementById('assignDateTo').value;
    const personelIds = [...document.querySelectorAll('[name="personel_ids[]"]:checked')].map(c => c.value);

    if (!shiftId || !planId || !dateFrom || !dateTo || !personelIds.length) {
        toast('warning', 'Lütfen tüm zorunlu alanları doldurun.');
        return;
    }

    const dates = [];
    const d = new Date(dateFrom);
    const end = new Date(dateTo);
    while (d <= end) {
        dates.push(d.toISOString().split('T')[0]);
        d.setDate(d.getDate() + 1);
    }

    axios.post(SHIFT_URLS.assign, {
        shift_id: shiftId,
        shift_plan_id: planId,
        personel_ids: personelIds,
        dates,
    }).then(res => {
        document.getElementById('assignModal').classList.add('hidden');
        Swal.fire({
            icon: res.data.success ? 'success' : 'warning',
            title: res.data.message,
            html: res.data.conflicts?.length ? `<p class="text-sm text-gray-500">Çakışmalar: ${res.data.conflicts.join(', ')}</p>` : '',
        });
        if (calendar) calendar.refetchEvents();
    });
}
</script>
@endpush
