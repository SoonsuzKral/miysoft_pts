<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('E-posta')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Şifre')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-[#02E0FB] shadow-sm focus:ring-[#02E0FB]" name="remember">
                <span class="ms-2 text-sm text-gray-600">Beni hatırla</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
                <a class="text-sm text-gray-600 hover:text-[#02E0FB] rounded-md focus:outline-none focus:ring-2 focus:ring-[#02E0FB]" href="{{ route('password.request') }}">
                    Şifremi unuttum
                </a>
            @endif
            <x-primary-button class="ms-3">
                Giriş Yap
            </x-primary-button>
        </div>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600">
        Hesabınız yok mu? <a href="{{ route('register') }}" class="font-medium text-[#02E0FB] hover:underline">Kayıt olun</a>
    </p>
</x-guest-layout>
