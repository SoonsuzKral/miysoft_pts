@extends('frontend._layout')
@section('title', 'Ücretsiz Deneyin')

@section('content')
<section class="py-20 bg-gradient-to-b from-gray-50 to-white">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="inline-block px-4 py-1.5 rounded-full bg-[#02E0FB]/10 text-[#02E0FB] text-sm font-semibold mb-4">14 Gün Ücretsiz</span>
            <h1 class="text-4xl lg:text-5xl font-black text-gray-900 mb-4">Ücretsiz Deneyin</h1>
            <p class="text-xl text-gray-500">Kredi kartı gerekmez. 5 dakikada hesabınızı oluşturun. Tüm özellikleri keşfedin.</p>
        </div>

        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8 md:p-10">
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

            <form action="{{ route('free-trial.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="company_name" class="block text-sm font-semibold text-gray-700 mb-2">Şirket Adı</label>
                        <input type="text" name="company_name" id="company_name" value="{{ old('company_name') }}"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#02E0FB] focus:ring-2 focus:ring-[#02E0FB]/20 outline-none transition-all"
                            placeholder="Örn: Şirket A.Ş." required>
                    </div>
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Ad Soyad</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#02E0FB] focus:ring-2 focus:ring-[#02E0FB]/20 outline-none transition-all"
                            placeholder="Örn: Ahmet Yılmaz" required>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">E-posta</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#02E0FB] focus:ring-2 focus:ring-[#02E0FB]/20 outline-none transition-all"
                            placeholder="ornek@sirket.com" required>
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">Telefon</label>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#02E0FB] focus:ring-2 focus:ring-[#02E0FB]/20 outline-none transition-all"
                            placeholder="Örn: 0555 123 45 67">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Şifre</label>
                        <input type="password" name="password" id="password"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#02E0FB] focus:ring-2 focus:ring-[#02E0FB]/20 outline-none transition-all"
                            placeholder="En az 8 karakter" required>
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Şifre Tekrar</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#02E0FB] focus:ring-2 focus:ring-[#02E0FB]/20 outline-none transition-all"
                            placeholder="Şifreyi tekrar girin" required>
                    </div>
                </div>
                <div>
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="agree_terms" value="1" {{ old('agree_terms') ? 'checked' : '' }}
                            class="mt-1 w-4 h-4 rounded border-gray-300 text-[#02E0FB] focus:ring-[#02E0FB]">
                        <span class="text-sm text-gray-600">
                            <a href="{{ route('terms') }}" class="text-[#02E0FB] hover:underline">Kullanım Şartları</a>'nı ve
                            <a href="{{ route('privacy') }}" class="text-[#02E0FB] hover:underline">Gizlilik Politikası</a>'nı okudum, kabul ediyorum.
                        </span>
                    </label>
                    @error('agree_terms')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="w-full btn-primary py-4 rounded-2xl text-lg font-black inline-flex items-center justify-center gap-2 shadow-xl shadow-cyan-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Hemen Başla — Ücretsiz
                </button>
            </form>
        </div>
        <p class="text-center text-sm text-gray-500 mt-6">Zaten hesabınız var mı? <a href="{{ route('login') }}" class="text-[#02E0FB] font-medium hover:underline">Giriş Yap</a></p>
    </div>
</section>
@endsection
