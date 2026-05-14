@extends('frontend._layout')
@section('title', 'Hakkımızda')

@section('content')
<section class="py-20 bg-gradient-to-b from-gray-50 to-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="inline-block px-4 py-1.5 rounded-full bg-[#02E0FB]/10 text-[#02E0FB] text-sm font-semibold mb-4">Hakkımızda</span>
            <h1 class="text-4xl lg:text-5xl font-black text-gray-900 mb-4">MİYSOFT PTS</h1>
            <p class="text-xl text-gray-500">Türkiye'nin en kapsamlı bulut tabanlı personel takip sistemi.</p>
        </div>

        <div class="prose prose-lg max-w-none">
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8 md:p-12 mb-12">
                <h2 class="text-2xl font-black text-gray-900 mb-6">Misyonumuz</h2>
                <p class="text-gray-600 leading-relaxed mb-6">
                    {{ $about['mission'] ?? 'Şirketlerin insan kaynakları süreçlerini dijitalleştirmek, personel yönetimini kolaylaştırmak ve verimliliği artırmak için güçlü, kullanıcı dostu bir platform sunmak.' }}
                </p>
                <h2 class="text-2xl font-black text-gray-900 mb-6">Vizyonumuz</h2>
                <p class="text-gray-600 leading-relaxed">
                    {{ $about['vision'] ?? 'Türkiye ve bölgede İK ve personel yönetiminde referans olan, en yenilikçi ve güvenilir SaaS platformu olmak.' }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                @foreach([
                    ['500+','Aktif Şirket','text-[#02E0FB]'],
                    ['50.000+','Yönetilen Personel','text-[#FA6001]'],
                    ['99.9%','Uptime','text-[#02E0FB]'],
                ] as [$val,$lbl,$color])
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm text-center card-hover">
                    <p class="text-4xl font-black {{ $color }}">{{ $val }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ $lbl }}</p>
                </div>
                @endforeach
            </div>

            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8 md:p-12">
                <h2 class="text-2xl font-black text-gray-900 mb-6">Neden MİYSOFT PTS?</h2>
                <ul class="space-y-4">
                    @foreach([
                        'Personel, izin, puantaj, vardiya, envanter ve masraf yönetimini tek platformda',
                        'KVKK uyumlu, güvenli ve bulut tabanlı altyapı',
                        'Kolay kurulum, 14 gün ücretsiz deneme imkanı',
                        'Türkçe destek ve müşteri odaklı geliştirme',
                    ] as $item)
                    <li class="flex items-start gap-3 text-gray-600">
                        <svg class="w-5 h-5 text-[#02E0FB] mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        {{ $item }}
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>
@endsection
