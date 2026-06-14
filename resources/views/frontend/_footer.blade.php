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
                <p class="text-gray-400 text-sm leading-relaxed mb-6">{{ $footer['footer.description'] ?? 'Türkiye\'nin en kapsamlı bulut tabanlı İK ve personel yönetim platformu.' }}</p>
                <div class="flex items-center gap-3">
                    @php
                        $socialPlatforms = [
                            'facebook'  => '<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>',
                            'twitter'   => '<path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>',
                            'linkedin'  => '<path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>',
                            'instagram' => '<path d="M12 0C8.74 0 8.333.015 7.053.07 5.775.126 4.905.326 4.14.637c-.79.32-1.459.748-2.127 1.416-.668.668-1.096 1.337-1.416 2.127-.311.765-.511 1.635-.567 2.913C.015 8.333 0 8.74 0 12s.015 3.667.07 4.947c.056 1.278.256 2.148.567 2.913.32.79.748 1.459 1.416 2.127.668.668 1.337 1.096 2.127 1.416.765.311 1.635.511 2.913.567C8.333 23.985 8.74 24 12 24s3.667-.015 4.947-.07c1.278-.056 2.148-.256 2.913-.567.79-.32 1.459-.748 2.127-1.416.668-.668 1.096-1.337 1.416-2.127.311-.765.511-1.635.567-2.913C23.985 15.667 24 15.26 24 12s-.015-3.667-.07-4.947c-.056-1.278-.256-2.148-.567-2.913-.32-.79-.748-1.459-1.416-2.127-.668-.668-1.337-1.096-2.127-1.416-.765-.311-1.635-.511-2.913-.567C15.667.015 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.166.422.36 1.057.415 2.227.055 1.265.071 1.647.071 4.85s-.016 3.585-.071 4.85c-.055 1.17-.249 1.805-.415 2.227-.217.562-.477.96-.896 1.382-.42.419-.819.679-1.381.896-.422.166-1.057.36-2.227.415-1.265.055-1.647.071-4.85.071-3.204 0-3.585-.016-4.85-.071-1.17-.055-1.805-.249-2.227-.415-.562-.217-.96-.477-1.382-.896-.419-.42-.679-.819-.896-1.381-.166-.422-.36-1.057-.415-2.227C2.175 15.585 2.16 15.203 2.16 12c0-3.203.016-3.585.071-4.85.055-1.17.249-1.805.415-2.227.217-.562.477-.96.896-1.382.419-.419.819-.679 1.381-.896.422-.166 1.057-.36 2.227-.415C8.415 2.175 8.797 2.16 12 2.16zm0 3.678a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405a1.441 1.441 0 11-2.882 0 1.441 1.441 0 012.882 0z"/>',
                        ];
                    @endphp
                    @foreach(['facebook','twitter','linkedin','instagram'] as $platform)
                    @php $url = $footer['footer.social.'.$platform] ?? '#'; @endphp
                    @if($url && $url !== '#')
                    <a href="{{ $url }}" target="_blank" rel="noopener" class="w-8 h-8 rounded-lg bg-gray-800 hover:bg-[#02E0FB] flex items-center justify-center text-gray-400 hover:text-white transition-all">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">{!! $socialPlatforms[$platform] !!}</svg>
                    </a>
                    @endif
                    @endforeach
                </div>
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
                <h4 class="text-white font-bold text-sm uppercase tracking-wider mb-5">İletişim</h4>
                <ul class="space-y-3 text-sm text-gray-400">
                    @if(!empty($footer['footer.email']))
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#02E0FB] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>{{ $footer['footer.email'] }}</span>
                    </li>
                    @endif
                    @if(!empty($footer['footer.phone']))
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#02E0FB] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>{{ $footer['footer.phone'] }}</span>
                    </li>
                    @endif
                    @if(!empty($footer['footer.address']))
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-[#02E0FB] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>{{ $footer['footer.address'] }}</span>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="pt-8 border-t border-gray-800 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-gray-500 text-sm">{{ $footer['footer.copyright'] ?? '&copy; ' . now()->year . ' MİYSOFT Teknoloji A.Ş. Tüm hakları saklıdır.' }}</p>
            <div class="flex items-center gap-4 text-xs text-gray-600">
                <span>🇹🇷 Türkiye'de Üretilmiştir</span>
                <span>•</span>
                <span>v{{ config('app.version', '1.0.0') }}</span>
            </div>
        </div>
    </div>
</footer>
