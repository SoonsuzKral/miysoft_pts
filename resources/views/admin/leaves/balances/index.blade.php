@extends('layouts.app')
@section('title', 'İzin Bakiyeleri')
@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#02E0FB] transition-colors">Dashboard</a>
    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('admin.leave.index') }}" class="hover:text-[#02E0FB] transition-colors">İzin Yönetimi</a>
    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-800 font-medium">İzin Bakiyeleri</span>
@endsection
@section('page_header')
    <div>
        <h1 class="text-2xl font-bold text-gray-900">İzin Bakiyeleri</h1>
        <p class="text-sm text-gray-500 mt-0.5">Personellerin yıllık izin bakiyelerini görüntüleyin ve yönetin.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.leave.index') }}"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Geri
        </a>
        <select id="balanceYear" onchange="loadBalances()"
            class="text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
            @for($y = now()->year + 1; $y >= now()->year - 2; $y--)
                <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
        @can('leave.manage')
        <button onclick="recalculateAll()"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-[#FA6001] to-orange-500 hover:from-orange-500 hover:to-[#FA6001] rounded-xl shadow-sm transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Bakiyeleri Hesapla
        </button>
        @endcan
    </div>
@endsection
@section('content')

<style>
@media (max-width: 640px) {
    .balances-table thead { display: none; }
    .balances-table tbody tr { display: flex; flex-direction: column; padding: 12px; border-bottom: 1px solid #f1f5f9; }
    .balances-table tbody tr:last-child { border-bottom: none; }
    .balances-table tbody td { display: flex; align-items: center; justify-content: space-between; padding: 6px 0; border: none; text-align: right; }
    .balances-table tbody td:before { content: attr(data-label); font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; }
    .balances-table tbody td:first-child { padding-top: 0; }
    .balances-table tbody td:last-child { padding-bottom: 0; }
}
</style>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm balances-table">
            <thead>
                <tr class="bg-gray-50/50">
                    <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Personel</th>
                    <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">İzin Türü</th>
                    <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Hak (gün)</th>
                    <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Kullanılan</th>
                    <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Kalan</th>
                    <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Kullanım Oranı</th>
                </tr>
            </thead>
            <tbody id="balancesBody" class="divide-y divide-gray-50">
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400 text-sm">Yükleniyor...</td></tr>
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3.5 border-t border-gray-50 flex items-center justify-between bg-gray-50/30">
        <div class="text-xs text-gray-400 font-medium" id="balancesInfo">—</div>
        <div id="balancesPagination" class="flex gap-1.5"></div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/admin/leave.js') }}"></script>
<script>document.body.dataset.page = 'leave-balances';</script>
@endpush
