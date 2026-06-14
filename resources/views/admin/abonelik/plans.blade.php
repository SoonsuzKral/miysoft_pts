@extends('layouts.app')

@section('title', 'Abonelik Planları')

@section('page_header')
<div>
    <h1 class="text-2xl font-bold text-gray-900">Abonelik Planları</h1>
    <p class="text-sm text-gray-500 mt-1">Mevcut planlar ve özellik karşılaştırması.</p>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    @php
    $plans = [
        ['name'=>'Starter', 'price'=>'₺990', 'period'=>'/ay', 'color'=>'#02E0FB', 'users'=>'25 kullanıcıya kadar', 'highlight'=>false,
         'features'=>['Personel Yönetimi', 'İzin Yönetimi', 'Puantaj', 'E-posta Bildirimleri', 'Temel Raporlar', '5 GB Depolama']],
        ['name'=>'Pro', 'price'=>'₺2.490', 'period'=>'/ay', 'color'=>'#FA6001', 'users'=>'100 kullanıcıya kadar', 'highlight'=>true,
         'features'=>['Starter\'ın tüm özellikleri', 'Vardiya Planlama', 'Envanter & Zimmet', 'Masraf & Avans', 'Gelişmiş Raporlar', 'CMS Yönetimi', '25 GB Depolama', 'Öncelikli Destek']],
        ['name'=>'Enterprise', 'price'=>'Özel Fiyat', 'period'=>'', 'color'=>'#6366f1', 'users'=>'Sınırsız kullanıcı', 'highlight'=>false,
         'features'=>['Pro\'nun tüm özellikleri', 'API Erişimi', 'Özel Entegrasyonlar', 'SSO (SAML)', 'SLA Garantisi', 'Özel Eğitim', 'Sınırsız Depolama', 'Özel Geliştirme']],
    ];
    @endphp
    @foreach($plans as $plan)
    <div class="relative bg-white rounded-2xl shadow-sm border {{ $plan['highlight'] ? 'border-[#FA6001] shadow-lg shadow-[#FA6001]/10' : 'border-gray-100' }} p-6 flex flex-col">
        @if($plan['highlight'])
        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
            <span class="px-4 py-1.5 bg-[#FA6001] text-white text-xs font-bold rounded-full whitespace-nowrap">EN POPÜLER</span>
        </div>
        @endif
        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-900">{{ $plan['name'] }}</h3>
            <p class="text-xs text-gray-500 mt-1">{{ $plan['users'] }}</p>
        </div>
        <div class="mb-6">
            <span class="text-4xl font-extrabold" style="color: {{ $plan['color'] }}">{{ $plan['price'] }}</span>
            <span class="text-gray-500 text-sm">{{ $plan['period'] }}</span>
        </div>
        <ul class="space-y-2.5 mb-8 flex-1">
            @foreach($plan['features'] as $f)
            <li class="flex items-center gap-2.5 text-sm text-gray-700">
                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                {{ $f }}
            </li>
            @endforeach
        </ul>
        <button onclick="Swal?.fire({title:'{{ $plan['name'] }} Planı', text:'Plan yükseltme için satış ekibiyle iletişime geçin.', icon:'info'})"
            class="w-full py-3 rounded-xl text-sm font-bold transition-all {{ $plan['highlight'] ? 'text-white' : 'text-gray-900' }}"
            style="background-color: {{ $plan['color'] }}; opacity: 0.9;"
            onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'">
            {{ $plan['price'] === 'Özel Fiyat' ? 'Teklif Al' : 'Bu Planı Seç' }}
        </button>
    </div>
    @endforeach
</div>

{{-- Karşılaştırma Tablosu --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="text-base font-semibold text-gray-900">Detaylı Özellik Karşılaştırması</h3>
    </div>
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Özellik</th>
                <th class="px-6 py-3 text-center text-xs font-semibold text-[#02E0FB] uppercase">Starter</th>
                <th class="px-6 py-3 text-center text-xs font-semibold text-[#FA6001] uppercase">Pro</th>
                <th class="px-6 py-3 text-center text-xs font-semibold text-indigo-500 uppercase">Enterprise</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @php
            $compare = [
                ['Personel Yönetimi', true, true, true],
                ['İzin Yönetimi', true, true, true],
                ['Puantaj & Vardiya', true, true, true],
                ['Envanter & Zimmet', false, true, true],
                ['Masraf & Avans', false, true, true],
                ['CMS & Blog', false, true, true],
                ['API Erişimi', false, false, true],
                ['SSO / SAML', false, false, true],
                ['Özel Domain', false, false, true],
            ];
            @endphp
            @foreach($compare as $row)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-3 text-sm text-gray-700">{{ $row[0] }}</td>
                @foreach([1, 2, 3] as $i)
                <td class="px-6 py-3 text-center">
                    @if($row[$i])
                    <svg class="w-5 h-5 text-green-500 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    @else
                    <svg class="w-5 h-5 text-gray-300 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    @endif
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
