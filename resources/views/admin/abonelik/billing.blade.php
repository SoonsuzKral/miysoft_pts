@extends('layouts.app')
@section('title', 'Faturalar & Ödemeler')

@section('breadcrumbs')
<nav class="flex mb-4" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-2 text-sm">
        <li><a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-[#02E0FB]">Dashboard</a></li>
        <li><span class="text-gray-400">/</span></li>
        <li><a href="{{ route('admin.subscriptions.index') }}" class="text-gray-500 hover:text-[#02E0FB]">Abonelik</a></li>
        <li><span class="text-gray-400">/</span></li>
        <li class="text-gray-900 font-medium">Faturalar</li>
    </ol>
</nav>
@endsection

@section('page_header')
<div>
    <h1 class="text-2xl font-bold text-gray-900">Faturalar & Ödemeler</h1>
    <p class="text-sm text-gray-500 mt-0.5">Abonelik faturalarınızı görüntüleyin.</p>
</div>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-gray-100">
        <h2 class="text-lg font-bold text-gray-900 mb-2">Mevcut Abonelik</h2>
        @if($subscription)
        <p class="text-sm text-gray-600">{{ $subscription->plan->name ?? 'Plan' }} — {{ $subscription->status ?? 'Aktif' }}</p>
        @else
        <p class="text-sm text-gray-500">Aktif abonelik bulunamadı.</p>
        @endif
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Fatura No</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tarih</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tutar</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Durum</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($invoices ?? [] as $inv)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $inv->invoice_number ?? $inv->id }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $inv->invoice_date ?? $inv->created_at?->format('d.m.Y') }}</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ number_format($inv->amount ?? 0, 2) }} ₺</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ ($inv->status ?? '') === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $inv->status ?? 'pending' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-500 text-sm">Henüz fatura bulunmuyor.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
