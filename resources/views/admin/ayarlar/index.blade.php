@extends('layouts.app')

@section('title', 'Sistem Ayarları')

@section('page_header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Sistem Ayarları</h1>
        <p class="text-sm text-gray-500 mt-1">Şirket bilgileri, bildirim ve entegrasyon ayarları.</p>
    </div>
    <button onclick="saveSettings()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#02E0FB] hover:bg-[#00b8d9] text-gray-900 font-semibold rounded-xl shadow-md transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        Kaydet
    </button>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Sol Menü --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-3">
            <nav class="space-y-1">
                @php
                    $tabs = [
                        ['id' => 'company', 'label' => 'Şirket Bilgileri', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                        ['id' => 'work', 'label' => 'Çalışma Saatleri', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['id' => 'notifications', 'label' => 'Bildirimler', 'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
                        ['id' => 'security', 'label' => 'Güvenlik', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                        ['id' => 'integrations', 'label' => 'Entegrasyonlar', 'icon' => 'M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z'],
                    ];
                @endphp
                @foreach($tabs as $tab)
                <button onclick="switchTab('{{ $tab['id'] }}')" id="tab-{{ $tab['id'] }}" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all {{ $loop->first ? 'bg-[#02E0FB]/10 text-[#00b8d9]' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}"/>
                    </svg>
                    {{ $tab['label'] }}
                </button>
                @endforeach
            </nav>
        </div>
    </div>

    {{-- İçerik Alanı --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Şirket Bilgileri --}}
        <div id="content-company" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-5">Şirket Bilgileri</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Şirket Adı</label>
                    <input type="text" value="{{ auth()->user()?->company?->name ?? 'MİYSOFT' }}" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vergi No</label>
                    <input type="text" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vergi Dairesi</label>
                    <input type="text" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-posta</label>
                    <input type="email" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                    <input type="tel" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adres</label>
                    <textarea rows="3" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Saat Dilimi</label>
                    <select class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                        <option selected>Europe/Istanbul</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Para Birimi</label>
                    <select class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                        <option value="TRY" selected>TRY — Türk Lirası</option>
                        <option value="USD">USD — Dolar</option>
                        <option value="EUR">EUR — Euro</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Çalışma Saatleri --}}
        <div id="content-work" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hidden">
            <h3 class="text-base font-semibold text-gray-900 mb-5">Çalışma Saatleri</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Giriş Saati</label>
                    <input type="time" value="09:00" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Çıkış Saati</label>
                    <input type="time" value="18:00" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Çalışma Günleri</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz'] as $i => $day)
                        <label class="flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 cursor-pointer hover:border-[#02E0FB] transition-colors has-[:checked]:bg-[#02E0FB]/10 has-[:checked]:border-[#02E0FB]">
                            <input type="checkbox" value="{{ $i + 1 }}" {{ $i < 5 ? 'checked' : '' }} class="rounded border-gray-300 text-[#02E0FB] focus:ring-[#02E0FB]">
                            <span class="text-sm font-medium text-gray-700">{{ $day }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fazla Mesai Eşiği (saat/gün)</label>
                    <input type="number" value="8" min="1" max="24" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Geç Giriş Toleransı (dakika)</label>
                    <input type="number" value="15" min="0" max="60" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                </div>
            </div>
        </div>

        {{-- Bildirimler --}}
        <div id="content-notifications" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hidden">
            <h3 class="text-base font-semibold text-gray-900 mb-5">Bildirim Ayarları</h3>
            <div class="space-y-4">
                @php
                    $notifs = [
                        ['key' => 'leave_request', 'label' => 'İzin Talebi Bildirimleri', 'desc' => 'Yeni izin talebi geldiğinde yöneticiye bildirim gönderilsin'],
                        ['key' => 'expense_request', 'label' => 'Masraf Talebi Bildirimleri', 'desc' => 'Masraf/avans talebi onay beklerken bildirim gönderilsin'],
                        ['key' => 'leave_approve', 'label' => 'Onay/Red Bildirimleri', 'desc' => 'İzin/masraf onaylandığında veya reddedildiğinde personele bildirim'],
                        ['key' => 'birthday', 'label' => 'Doğum Günü Hatırlatması', 'desc' => 'Personel doğum günlerinde otomatik bildirim'],
                        ['key' => 'contract_expire', 'label' => 'Sözleşme Bitiş Uyarısı', 'desc' => 'Sözleşme bitişinden 30 gün önce uyarı gönderilsin'],
                    ];
                @endphp
                @foreach($notifs as $n)
                <div class="flex items-start justify-between p-4 rounded-xl border border-gray-100 hover:bg-gray-50 transition-colors">
                    <div class="flex-1 mr-4">
                        <p class="text-sm font-medium text-gray-900">{{ $n['label'] }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $n['desc'] }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="text-xs text-gray-500">E-posta</label>
                        <input type="checkbox" checked class="rounded border-gray-300 text-[#02E0FB] focus:ring-[#02E0FB]">
                        <label class="text-xs text-gray-500">Uygulama</label>
                        <input type="checkbox" checked class="rounded border-gray-300 text-[#02E0FB] focus:ring-[#02E0FB]">
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Güvenlik --}}
        <div id="content-security" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hidden">
            <h3 class="text-base font-semibold text-gray-900 mb-5">Güvenlik Ayarları</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100">
                    <div>
                        <p class="text-sm font-medium text-gray-900">İki Faktörlü Doğrulama (2FA)</p>
                        <p class="text-xs text-gray-500">Yönetici hesapları için zorunlu kıl</p>
                    </div>
                    <input type="checkbox" class="rounded border-gray-300 text-[#02E0FB] focus:ring-[#02E0FB]">
                </div>
                <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Oturum Süresi</p>
                        <p class="text-xs text-gray-500">Hareketsizlik sonrası otomatik çıkış</p>
                    </div>
                    <select class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#02E0FB]">
                        <option>30 dakika</option>
                        <option selected>1 saat</option>
                        <option>4 saat</option>
                        <option>8 saat</option>
                    </select>
                </div>
                <div class="p-4 rounded-xl border border-orange-100 bg-orange-50">
                    <p class="text-sm font-semibold text-orange-800">Şifre Politikası</p>
                    <ul class="mt-2 space-y-1 text-xs text-orange-700">
                        <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-orange-400"></span>En az 8 karakter</li>
                        <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-orange-400"></span>Büyük harf + rakam zorunlu</li>
                        <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-orange-400"></span>90 günde bir şifre değişikliği</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Entegrasyonlar --}}
        <div id="content-integrations" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hidden">
            <h3 class="text-base font-semibold text-gray-900 mb-5">Entegrasyon Ayarları</h3>
            <div class="space-y-4">
                <div class="p-4 rounded-xl border border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm font-semibold text-gray-900">SMTP E-posta Ayarları</p>
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-50 text-yellow-600">Yapılandırılmamış</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" placeholder="SMTP Host" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#02E0FB]">
                        <input type="number" placeholder="Port (587)" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#02E0FB]">
                        <input type="text" placeholder="Kullanıcı Adı" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#02E0FB]">
                        <input type="password" placeholder="Şifre" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#02E0FB]">
                    </div>
                </div>
                <div class="p-4 rounded-xl border border-gray-100 opacity-60">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-gray-900">Muhasebe Yazılımı Entegrasyonu</p>
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Yakında</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Logo, Mikro, Netsis bağlantısı</p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function switchTab(id) {
    ['company', 'work', 'notifications', 'security', 'integrations'].forEach(t => {
        document.getElementById('content-' + t)?.classList.add('hidden');
        document.getElementById('tab-' + t)?.classList.remove('bg-[#02E0FB]/10', 'text-[#00b8d9]');
        document.getElementById('tab-' + t)?.classList.add('text-gray-600', 'hover:bg-gray-50');
    });
    document.getElementById('content-' + id)?.classList.remove('hidden');
    document.getElementById('tab-' + id)?.classList.add('bg-[#02E0FB]/10', 'text-[#00b8d9]');
    document.getElementById('tab-' + id)?.classList.remove('text-gray-600', 'hover:bg-gray-50');
}

function saveSettings() {
    Swal?.fire({ title: 'Kaydedildi!', text: 'Ayarlar başarıyla güncellendi.', icon: 'success', timer: 2000, showConfirmButton: false });
}
</script>
@endpush
