@extends('frontend._layout')
@section('title', 'Fiyatlandırma')

@section('content')
<section class="py-20 bg-gradient-to-b from-gray-50 to-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="inline-block px-4 py-1.5 rounded-full bg-[#02E0FB]/10 text-[#02E0FB] text-sm font-semibold mb-4">Fiyatlandırma</span>
            <h1 class="text-4xl lg:text-5xl font-black text-gray-900 mb-4">Bütçenize Uygun Paketler</h1>
            <p class="text-xl text-gray-500 max-w-2xl mx-auto">İhtiyacınıza göre büyüyen, esnek fiyatlandırma. MİYSOFT PTS ile personel yönetimimiz tek platformda.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            @forelse($plans as $plan)
            <div class="relative rounded-3xl p-8 border-2 card-hover {{ $plan->is_popular ? 'border-[#02E0FB] bg-gradient-to-br from-[#02E0FB]/5 to-[#FA6001]/5 shadow-xl shadow-cyan-500/20' : 'border-gray-100 bg-white shadow-sm' }}">
                @if($plan->is_popular)
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gradient-to-r from-[#02E0FB] to-[#00b8d9] text-gray-900 text-xs font-black px-4 py-1 rounded-full">En Popüler</div>
                @endif
                <div class="mb-6">
                    <h3 class="text-xl font-black text-gray-900 mb-1">{{ $plan->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $plan->description }}</p>
                </div>
                <div class="mb-8">
                    <span class="text-5xl font-black text-gray-900">{{ number_format($plan->price_monthly, 0, ',', '.') }}</span>
                    <span class="text-gray-400"> TRY/ay</span>
                    @if($plan->max_personel)
                    <p class="text-xs text-gray-400 mt-1">Maks {{ $plan->max_personel }} personel</p>
                    @else
                    <p class="text-xs text-[#02E0FB] mt-1 font-medium">Sınırsız personel</p>
                    @endif
                </div>
                <ul class="space-y-2.5 mb-8">
                    @foreach(($plan->features ?? ['Tüm temel özellikler','Destek']) as $feature)
                    <li class="flex items-start gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-[#02E0FB] mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        {{ is_string($feature) ? $feature : ($feature['label'] ?? '') }}
                    </li>
                    @endforeach
                </ul>
                <a href="{{ route('free-trial') }}" class="{{ $plan->is_popular ? 'btn-primary' : 'border-2 border-[#02E0FB] text-[#02E0FB] hover:bg-[#02E0FB] hover:text-gray-900 transition-all' }} block text-center py-3 rounded-2xl text-sm font-bold">
                    Hemen Başla
                </a>
            </div>
            @empty
            @foreach([['Starter','₺999','50 Personel','Temel modüller',false],['Pro','₺2.499','250 Personel','Tüm modüller',true],['Enterprise','Özel','Sınırsız','Özel entegrasyon',false]] as [$n,$p,$m,$d,$pop])
            <div class="rounded-3xl p-8 border-2 {{ $pop ? 'border-[#02E0FB] shadow-xl' : 'border-gray-100' }} bg-white card-hover">
                @if($pop)<div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-[#02E0FB] text-gray-900 text-xs font-black px-4 py-1 rounded-full">En Popüler</div>@endif
                <h3 class="text-xl font-black mb-1">{{ $n }}</h3>
                <p class="text-sm text-gray-500 mb-4">{{ $d }}</p>
                <p class="text-4xl font-black text-gray-900 mb-1">{{ $p }}</p>
                <p class="text-xs text-gray-400 mb-6">{{ $m }}</p>
                <ul class="space-y-2 mb-8">
                    @foreach(['Personel Yönetimi','İzin & Puantaj','Vardiya Planlama','Envanter & Zimmet','Raporlar'] as $f)
                    <li class="flex items-center gap-2 text-sm text-gray-600"><svg class="w-4 h-4 text-[#02E0FB]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>{{ $f }}</li>
                    @endforeach
                </ul>
                <a href="{{ route('free-trial') }}" class="{{ $pop ? 'btn-primary' : 'btn-secondary' }} block text-center py-3 rounded-2xl text-sm font-bold">Hemen Başla</a>
            </div>
            @endforeach
            @endforelse
        </div>

        <div class="text-center mt-12">
            <p class="text-gray-500 text-sm mb-4">Tüm paketlerde 14 gün ücretsiz deneme. Kredi kartı gerekmez.</p>
            <a href="{{ route('contact') }}" class="text-[#02E0FB] font-medium hover:underline text-sm">Kurumsal teklif için iletişime geçin →</a>
        </div>
    </div>
</section>
@endsection
