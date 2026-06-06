@extends('layouts.app')

@section('title', 'Resmi Tatiller')

@section('page_header')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Resmi Tatil Yönetimi</h1>
        <p class="text-sm text-gray-500 mt-1">Yıllık resmi tatil takvimini yönetin.</p>
    </div>
    <button onclick="openAddModal()" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#02E0FB] hover:bg-[#00b8d9] text-gray-900 font-semibold rounded-xl shadow-md transition-all w-full sm:w-auto">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tatil Ekle
    </button>
</div>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Yıl Filtresi --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5">
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
            <label class="text-sm font-medium text-gray-700">Yıl Seç:</label>
            <select id="yearFilter" class="w-full sm:w-auto px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent" onchange="loadHolidays()">
                @for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    {{-- Tatil Tablosu --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="holidaysTable" class="min-w-full divide-y divide-gray-100">
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
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-gray-400 text-sm">Yükleniyor...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Sabit Türkiye Tatilleri --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-4">{{ date('Y') }} Yılı Türkiye Resmi Tatilleri</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @php
                $holidays = [
                    ['01 Ocak', 'Yılbaşı'],
                    ['23 Nisan', 'Ulusal Egemenlik ve Çocuk Bayramı'],
                    ['01 Mayıs', 'Emek ve Dayanışma Günü'],
                    ['19 Mayıs', 'Atatürk\'ü Anma, Gençlik ve Spor Bayramı'],
                    ['15 Temmuz', 'Demokrasi ve Millî Birlik Günü'],
                    ['30 Ağustos', 'Zafer Bayramı'],
                    ['29 Ekim', 'Cumhuriyet Bayramı'],
                    ['Ramazan Bayramı', '3.5 Gün'],
                    ['Kurban Bayramı', '4.5 Gün'],
                ];
            @endphp
            @foreach($holidays as $h)
            <div class="flex items-center gap-3 p-3 bg-[#02E0FB]/5 border border-[#02E0FB]/20 rounded-xl">
                <span class="w-2 h-2 rounded-full bg-[#02E0FB] flex-shrink-0"></span>
                <div>
                    <p class="text-sm font-semibold text-gray-800">{{ $h[1] }}</p>
                    <p class="text-xs text-gray-500">{{ $h[0] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Add Modal --}}
<div id="addModal" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-gray-900">Tatil Ekle</h3>
            <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="addHolidayForm">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tatil Adı</label>
                    <input type="text" name="name" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent" placeholder="Örn: Ulusal Bayram">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tarih</label>
                    <input type="date" name="date" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tür</label>
                    <select name="type" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                        <option value="national">Resmi Tatil</option>
                        <option value="religious">Dini Bayram</option>
                        <option value="custom">Şirket Tatili</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="closeAddModal()" class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">İptal</button>
                <button type="submit" class="flex-1 px-4 py-2.5 bg-[#02E0FB] hover:bg-[#00b8d9] text-gray-900 rounded-xl text-sm font-semibold transition-all">Kaydet</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openAddModal() { document.getElementById('addModal').classList.remove('hidden'); }
function closeAddModal() { document.getElementById('addModal').classList.add('hidden'); }

function loadHolidays() {
    // TODO: API endpoint bağlandığında burası aktif edilecek
    const rows = [
        { date: '01 Ocak', name: 'Yılbaşı', day: 'Çarşamba', type: 'Resmi' },
        { date: '23 Nisan', name: 'Ulusal Egemenlik ve Çocuk Bayramı', day: 'Perşembe', type: 'Resmi' },
        { date: '01 Mayıs', name: 'Emek ve Dayanışma Günü', day: 'Perşembe', type: 'Resmi' },
        { date: '19 Mayıs', name: 'Gençlik ve Spor Bayramı', day: 'Pazartesi', type: 'Resmi' },
        { date: '15 Temmuz', name: 'Demokrasi ve Millî Birlik Günü', day: 'Salı', type: 'Resmi' },
        { date: '30 Ağustos', name: 'Zafer Bayramı', day: 'Cumartesi', type: 'Resmi' },
        { date: '29 Ekim', name: 'Cumhuriyet Bayramı', day: 'Çarşamba', type: 'Resmi' },
    ];
    const body = document.getElementById('holidaysBody');
    body.innerHTML = rows.map((r, i) => `
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="hidden sm:table-cell px-4 sm:px-6 py-3 sm:py-4 text-sm text-gray-500">${i + 1}</td>
            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm font-medium text-gray-900 truncate max-w-[140px] sm:max-w-none">${r.name}</td>
            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm text-gray-600 whitespace-nowrap">${r.date}</td>
            <td class="hidden md:table-cell px-4 sm:px-6 py-3 sm:py-4 text-sm text-gray-600">${r.day}</td>
            <td class="hidden sm:table-cell px-4 sm:px-6 py-3 sm:py-4"><span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-[#02E0FB]/10 text-[#00b8d9]">${r.type}</span></td>
            <td class="px-4 sm:px-6 py-3 sm:py-4 text-right"><button class="text-red-400 hover:text-red-600 text-xs font-medium">Sil</button></td>
        </tr>
    `).join('');
}
document.addEventListener('DOMContentLoaded', loadHolidays);
</script>
@endpush
