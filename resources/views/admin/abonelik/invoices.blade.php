@extends('layouts.app')

@section('title', 'Faturalar')

@section('page_header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Fatura Geçmişi</h1>
        <p class="text-sm text-gray-500 mt-1">Tüm abonelik faturalarınızı görüntüleyin ve indirin.</p>
    </div>
</div>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-5 border-b border-gray-100 flex items-center justify-between">
        <p class="text-sm text-gray-500">Toplam <span class="font-semibold text-gray-900" id="invoiceCount">-</span> fatura</p>
        <input type="text" placeholder="Fatura ara..." class="px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent w-56">
    </div>
    <table class="min-w-full divide-y divide-gray-100">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fatura No</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Dönem</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Plan</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tutar</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Durum</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">İşlem</th>
            </tr>
        </thead>
        <tbody id="invoicesBody" class="bg-white divide-y divide-gray-50">
            <tr>
                <td colspan="6" class="px-6 py-16 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                        </svg>
                        <p class="text-gray-400 text-sm">Henüz fatura bulunmuyor.</p>
                        <a href="{{ route('admin.subscriptions.index') }}" class="text-[#02E0FB] text-sm font-medium hover:underline">Abonelik planı seçin →</a>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
