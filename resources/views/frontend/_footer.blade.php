<footer class="bg-gray-900 pt-16 pb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">
            <div>
                <div class="flex items-center gap-2.5 mb-5">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#02E0FB] to-[#00b8d9] flex items-center justify-center">
                        <span class="text-white font-black text-sm">M</span>
                    </div>
                    <div>
                        <p class="font-black text-white text-base">MİYSOFT PTS</p>
                        <p class="text-[10px] text-[#02E0FB] font-semibold tracking-widest uppercase">Personel Takip Sistemi</p>
                    </div>
                </div>
                <p class="text-gray-400 text-sm leading-relaxed mb-6">Türkiye'nin en kapsamlı bulut tabanlı İK ve personel yönetim platformu.</p>
            </div>
            <div>
                <h4 class="text-white font-bold text-sm uppercase tracking-wider mb-5">Ürün</h4>
                <ul class="space-y-3">
                    @foreach([['Özellikler',route('product')],['Fiyatlandırma',route('pricing')],['Güncellemeler','#']] as [$lbl,$href])
                    <li><a href="{{ $href }}" class="text-gray-400 hover:text-[#02E0FB] text-sm transition-colors">{{ $lbl }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold text-sm uppercase tracking-wider mb-5">Şirket</h4>
                <ul class="space-y-3">
                    @foreach([['Hakkımızda',route('about')],['Blog',route('blog.index')],['İletişim',route('contact')]] as [$lbl,$href])
                    <li><a href="{{ $href }}" class="text-gray-400 hover:text-[#02E0FB] text-sm transition-colors">{{ $lbl }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold text-sm uppercase tracking-wider mb-5">Yasal</h4>
                <ul class="space-y-3">
                    @foreach([['KVKK',route('kvkk')],['Gizlilik Politikası',route('privacy')],['Kullanım Şartları',route('terms')]] as [$lbl,$href])
                    <li><a href="{{ $href }}" class="text-gray-400 hover:text-[#02E0FB] text-sm transition-colors">{{ $lbl }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="pt-8 border-t border-gray-800 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-gray-500 text-sm">&copy; {{ now()->year }} MİYSOFT Teknoloji A.Ş. Tüm hakları saklıdır.</p>
            <div class="flex items-center gap-4 text-xs text-gray-600">
                <span>🇹🇷 Türkiye'de Üretilmiştir</span>
                <span>•</span>
                <span>v{{ config('app.version', '1.0.0') }}</span>
            </div>
        </div>
    </div>
</footer>
