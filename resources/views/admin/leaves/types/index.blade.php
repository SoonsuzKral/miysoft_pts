@extends('layouts.app')
@section('title', 'İzin Türleri')
@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#02E0FB] transition-colors">Dashboard</a>
    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('admin.leave.index') }}" class="hover:text-[#02E0FB] transition-colors">İzin Yönetimi</a>
    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-800 font-medium">İzin Türleri</span>
@endsection
@section('page_header')
    <div>
        <h1 class="text-2xl font-bold text-gray-900">İzin Türleri</h1>
        <p class="text-sm text-gray-500 mt-0.5">Şirketinizin izin türlerini tanımlayın ve yönetin.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.leave.index') }}"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Geri
        </a>
        @can('leave.manage')
        <button onclick="openCreateTypeModal()"
            class="flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-[#02E0FB] to-cyan-500 hover:from-cyan-500 hover:to-[#02E0FB] rounded-xl shadow-md hover:shadow-lg transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Yeni İzin Türü
        </button>
        @endcan
    </div>
@endsection
@section('content')

<style>
.animate-scale-in { animation: scaleIn .25s ease-out; }
@keyframes scaleIn { from { opacity: 0; transform: scale(0.95) translateY(-10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
@media (max-width: 640px) {
    .types-table thead { display: none; }
    .types-table tbody tr { display: flex; flex-direction: column; padding: 12px; border-bottom: 1px solid #f1f5f9; }
    .types-table tbody tr:last-child { border-bottom: none; }
    .types-table tbody td { display: flex; align-items: center; justify-content: space-between; padding: 6px 0; border: none; text-align: right; }
    .types-table tbody td:before { content: attr(data-label); font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; }
    .types-table tbody td:first-child { padding-top: 0; }
    .types-table tbody td:last-child { padding-bottom: 0; }
}
</style>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm types-table">
            <thead>
                <tr class="bg-gray-50/50">
                    <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">İzin Türü</th>
                    <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Ücretli</th>
                    <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Max Yıllık Gün</th>
                    <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Onay Gerekli</th>
                    <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Kullanım Sayısı</th>
                    <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Durum</th>
                    <th class="px-4 py-3.5 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">İşlemler</th>
                </tr>
            </thead>
            <tbody id="leaveTypesBody" class="divide-y divide-gray-50">
                <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400 text-sm">Yükleniyor...</td></tr>
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/admin/leave.js') }}"></script>
<script>document.body.dataset.page = 'leave-types';</script>
@endpush
