@extends('frontend._layout')
@section('title', 'İletişim')

@section('content')
<section class="py-20 bg-gradient-to-b from-gray-50 to-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="inline-block px-4 py-1.5 rounded-full bg-[#FA6001]/10 text-[#FA6001] text-sm font-semibold mb-4">İletişim</span>
            <h1 class="text-4xl lg:text-5xl font-black text-gray-900 mb-4">Bizimle İletişime Geçin</h1>
            <p class="text-xl text-gray-500 max-w-2xl mx-auto">Sorularınız, önerileriniz veya demo talebiniz için bizimle iletişime geçin. En kısa sürede dönüş yapacağız.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            {{-- İletişim Formu --}}
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
                @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('contact.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Ad Soyad</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#02E0FB] focus:ring-2 focus:ring-[#02E0FB]/20 outline-none transition-all"
                                placeholder="Adınız Soyadınız" required>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">E-posta</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#02E0FB] focus:ring-2 focus:ring-[#02E0FB]/20 outline-none transition-all"
                                placeholder="ornek@sirket.com" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">Telefon</label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#02E0FB] focus:ring-2 focus:ring-[#02E0FB]/20 outline-none transition-all"
                                placeholder="0555 123 45 67">
                        </div>
                        <div>
                            <label for="company" class="block text-sm font-semibold text-gray-700 mb-2">Şirket</label>
                            <input type="text" name="company" id="company" value="{{ old('company') }}"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#02E0FB] focus:ring-2 focus:ring-[#02E0FB]/20 outline-none transition-all"
                                placeholder="Şirket Adı">
                        </div>
                    </div>
                    <div>
                        <label for="message" class="block text-sm font-semibold text-gray-700 mb-2">Mesajınız</label>
                        <textarea name="message" id="message" rows="5"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#02E0FB] focus:ring-2 focus:ring-[#02E0FB]/20 outline-none transition-all resize-none"
                            placeholder="Mesajınızı buraya yazın..." required>{{ old('message') }}</textarea>
                    </div>
                    <div>
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="consent" value="1" {{ old('consent') ? 'checked' : '' }}
                                class="mt-1 w-4 h-4 rounded border-gray-300 text-[#02E0FB] focus:ring-[#02E0FB]">
                            <span class="text-sm text-gray-600">
                                <a href="{{ route('kvkk') }}" class="text-[#02E0FB] hover:underline">KVKK</a> aydınlatma metnini okudum, kişisel verilerimin işlenmesine onay veriyorum.
                            </span>
                        </label>
                        @error('consent')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="w-full btn-primary py-4 rounded-2xl text-lg font-black inline-flex items-center justify-center gap-2 shadow-xl shadow-cyan-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Gönder
                    </button>
                </form>
            </div>

            {{-- Adres & Harita --}}
            <div>
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8 mb-8">
                    <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2">
                        <span class="w-10 h-10 rounded-xl bg-[#02E0FB]/10 flex items-center justify-center text-[#02E0FB]">📍</span>
                        Adres
                    </h3>
                    <p class="text-gray-600 leading-relaxed">
                        {{ $contact['contact.address'] ?? 'MİYSOFT Teknoloji A.Ş.' }}<br>
                        {{ $contact['contact.city'] ?? 'İstanbul' }}<br>
                        {{ $contact['contact.country'] ?? 'Türkiye' }}
                    </p>
                </div>
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8 mb-8">
                    <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2">
                        <span class="w-10 h-10 rounded-xl bg-[#FA6001]/10 flex items-center justify-center text-[#FA6001]">📞</span>
                        İletişim Bilgileri
                    </h3>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-2 text-gray-600">
                            <svg class="w-4 h-4 text-[#02E0FB]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            {{ $contact['contact.email'] ?? 'info@miysoft.com.tr' }}
                        </li>
                        <li class="flex items-center gap-2 text-gray-600">
                            <svg class="w-4 h-4 text-[#02E0FB]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ $contact['contact.phone'] ?? '+90 212 000 00 00' }}
                        </li>
                    </ul>
                </div>
                @if(!empty($contact['contact.map_url']))
                <div class="rounded-3xl overflow-hidden border border-gray-100 shadow-xl h-64">
                    <iframe src="{{ $contact['contact.map_url'] }}" width="100%" height="100%" style="border:0;" allowfullscreen loading="lazy"></iframe>
                </div>
                @else
                <div class="rounded-3xl overflow-hidden border border-gray-100 shadow-xl bg-gray-100 h-64 flex items-center justify-center">
                    <p class="text-gray-500 text-sm">Harita alanı — CMS'den yapılandırılabilir</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
