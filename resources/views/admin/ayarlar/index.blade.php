@extends('layouts.app')

@section('title', 'Sistem Ayarları')

@section('page_header')
<div>
    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Sistem Ayarları</h1>
    <p class="text-sm text-gray-500 mt-0.5">Şirket bilgileri, çalışma saatleri, bildirim ve entegrasyon ayarları.</p>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Sol Menü --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-2 sm:p-3">
            <div class="flex lg:flex-col gap-1 overflow-x-auto scrollbar-hide" style="-webkit-overflow-scrolling:touch">
                @php
                    $tabs = [
                        ['id' => 'company', 'label' => 'Şirket', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                        ['id' => 'work', 'label' => 'Çalışma', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['id' => 'notifications', 'label' => 'Bildirimler', 'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
                        ['id' => 'security', 'label' => 'Güvenlik', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                        ['id' => 'integrations', 'label' => 'Entegrasyon', 'icon' => 'M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z'],
                    ];
                @endphp
                @foreach($tabs as $tab)
                <button onclick="switchTab('{{ $tab['id'] }}')" id="tab-{{ $tab['id'] }}" class="whitespace-nowrap lg:w-full flex items-center gap-2 lg:gap-3 px-3 lg:px-4 py-2.5 lg:py-3 rounded-xl text-sm font-medium transition-all {{ $loop->first ? 'bg-[#02E0FB]/10 text-[#00b8d9]' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}"/>
                    </svg>
                    <span class="hidden sm:inline lg:inline">{{ $tab['label'] }}</span>
                </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- İçerik Alanı --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Şirket Bilgileri --}}
        <div id="content-company" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-5">Şirket Bilgileri</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Şirket Adı</label>
                    <input type="text" name="company_name" class="setting-input w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vergi No</label>
                    <input type="text" name="tax_number" class="setting-input w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vergi Dairesi</label>
                    <input type="text" name="tax_office" class="setting-input w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-posta</label>
                    <input type="email" name="company_email" class="setting-input w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                    <input type="tel" name="company_phone" class="setting-input w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adres</label>
                    <textarea name="company_address" rows="3" class="setting-input w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Saat Dilimi</label>
                    <select name="timezone" class="setting-input w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                        <option value="Europe/Istanbul">Europe/Istanbul</option>
                        <option value="Europe/London">Europe/London</option>
                        <option value="Europe/Berlin">Europe/Berlin</option>
                        <option value="America/New_York">America/New_York</option>
                        <option value="Asia/Dubai">Asia/Dubai</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Para Birimi</label>
                    <select name="currency" class="setting-input w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                        <option value="TRY">TRY — Türk Lirası</option>
                        <option value="USD">USD — Dolar</option>
                        <option value="EUR">EUR — Euro</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Çalışma Saatleri --}}
        <div id="content-work" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6 hidden">
            <h3 class="text-base font-semibold text-gray-900 mb-5">Çalışma Saatleri</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Giriş Saati</label>
                    <input type="time" name="work_start" class="setting-input w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Çıkış Saati</label>
                    <input type="time" name="work_end" class="setting-input w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Çalışma Günleri</label>
                    <div class="grid grid-cols-4 sm:flex sm:flex-wrap gap-2">
                        @foreach(['Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz'] as $i => $day)
                        <label class="flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-4 py-2 rounded-xl border border-gray-200 cursor-pointer hover:border-[#02E0FB] transition-colors has-[:checked]:bg-[#02E0FB]/10 has-[:checked]:border-[#02E0FB] text-sm">
                            <input type="checkbox" name="work_days" value="{{ $i + 1 }}" {{ $i < 5 ? 'checked' : '' }} class="rounded border-gray-300 text-[#02E0FB] focus:ring-[#02E0FB]">
                            <span class="text-xs sm:text-sm font-medium text-gray-700">{{ $day }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fazla Mesai Eşiği (saat/gün)</label>
                    <input type="number" name="overtime_threshold" min="1" max="24" class="setting-input w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Geç Giriş Toleransı (dakika)</label>
                    <input type="number" name="late_tolerance" min="0" max="60" class="setting-input w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent">
                </div>
            </div>
        </div>

        {{-- Bildirimler --}}
        <div id="content-notifications" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6 hidden">
            <h3 class="text-base font-semibold text-gray-900 mb-5">Bildirim Ayarları</h3>
            <div class="space-y-3">
                @php
                    $notifSettings = [
                        ['key' => 'notify_leave_request', 'label' => 'İzin Talebi', 'desc' => 'Yeni izin talebi geldiğinde yöneticiye bildirim gönderilsin'],
                        ['key' => 'notify_expense_request', 'label' => 'Masraf Talebi', 'desc' => 'Masraf/avans talebi onay beklerken bildirim gönderilsin'],
                        ['key' => 'notify_approve_reject', 'label' => 'Onay/Red', 'desc' => 'İzin/masraf onaylandığında veya reddedildiğinde personele bildirim'],
                        ['key' => 'notify_birthday', 'label' => 'Doğum Günü', 'desc' => 'Personel doğum günlerinde otomatik bildirim'],
                        ['key' => 'notify_contract_expire', 'label' => 'Sözleşme Bitişi', 'desc' => 'Sözleşme bitişinden 30 gün önce uyarı gönderilsin'],
                    ];
                @endphp
                @foreach($notifSettings as $n)
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 p-4 rounded-xl border border-gray-100 hover:bg-gray-50 transition-colors">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">{{ $n['label'] }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $n['desc'] }}</p>
                    </div>
                    <div class="flex items-center gap-4 shrink-0">
                        <label class="flex items-center gap-1.5 text-xs text-gray-500 cursor-pointer">
                            <input type="checkbox" name="{{ $n['key'] }}_email" checked class="setting-checkbox rounded border-gray-300 text-[#02E0FB] focus:ring-[#02E0FB]">
                            E-posta
                        </label>
                        <label class="flex items-center gap-1.5 text-xs text-gray-500 cursor-pointer">
                            <input type="checkbox" name="{{ $n['key'] }}_app" checked class="setting-checkbox rounded border-gray-300 text-[#02E0FB] focus:ring-[#02E0FB]">
                            Uygulama
                        </label>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Güvenlik --}}
        <div id="content-security" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6 hidden">
            <h3 class="text-base font-semibold text-gray-900 mb-5">Güvenlik Ayarları</h3>
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 rounded-xl border border-gray-100">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900">İki Faktörlü Doğrulama (2FA)</p>
                        <p class="text-xs text-gray-500">Yönetici hesapları için zorunlu kıl</p>
                    </div>
                    <input type="checkbox" name="two_factor_required" class="setting-checkbox rounded border-gray-300 text-[#02E0FB] focus:ring-[#02E0FB] shrink-0">
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 rounded-xl border border-gray-100">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900">Oturum Süresi</p>
                        <p class="text-xs text-gray-500">Hareketsizlik sonrası otomatik çıkış</p>
                    </div>
                    <select name="session_lifetime" class="setting-input w-full sm:w-auto px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#02E0FB]">
                        <option value="30">30 dakika</option>
                        <option value="60">1 saat</option>
                        <option value="240">4 saat</option>
                        <option value="480">8 saat</option>
                    </select>
                </div>
                <div class="p-4 rounded-xl border border-orange-100 bg-orange-50">
                    <p class="text-sm font-semibold text-orange-800">Şifre Politikası</p>
                    <ul class="mt-2 space-y-1 text-xs text-orange-700">
                        <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-orange-400 shrink-0"></span>En az 8 karakter</li>
                        <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-orange-400 shrink-0"></span>Büyük harf + rakam zorunlu</li>
                        <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-orange-400 shrink-0"></span>90 günde bir şifre değişikliği</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Entegrasyonlar --}}
        <div id="content-integrations" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6 hidden">
            <h3 class="text-base font-semibold text-gray-900 mb-5">Entegrasyon Ayarları</h3>
            <div class="space-y-4">
                <div class="p-4 rounded-xl border border-gray-100">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                        <p class="text-sm font-semibold text-gray-900">SMTP E-posta Ayarları</p>
                        <span id="smtpStatus" class="self-start sm:self-auto px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-50 text-yellow-600">Yapılandırılmamış</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <input type="text" name="smtp_host" placeholder="SMTP Host" class="setting-input w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#02E0FB]">
                        <input type="number" name="smtp_port" placeholder="Port (587)" class="setting-input w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#02E0FB]">
                        <input type="text" name="smtp_username" placeholder="Kullanıcı Adı" class="setting-input w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#02E0FB]">
                        <input type="password" name="smtp_password" placeholder="Şifre" class="setting-input w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#02E0FB]">
                    </div>
                </div>
                <div class="p-4 rounded-xl border border-gray-100 opacity-60">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <p class="text-sm font-semibold text-gray-900">Muhasebe Yazılımı Entegrasyonu</p>
                        <span class="self-start sm:self-auto px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Yakında</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Logo, Mikro, Netsis bağlantısı</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-6 flex justify-end">
    <button onclick="saveSettings()" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-[#02E0FB] hover:bg-[#00b8d9] text-gray-900 font-semibold rounded-xl shadow-md transition-all text-sm">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        Ayarları Kaydet
    </button>
</div>
@endsection

@push('scripts')
<script>
let settingsCache = {};
let savePending = false;

document.addEventListener('DOMContentLoaded', loadSettings);

function switchTab(id) {
    ['company', 'work', 'notifications', 'security', 'integrations'].forEach(t => {
        const content = document.getElementById('content-' + t);
        const tab = document.getElementById('tab-' + t);
        if (content) content.classList.add('hidden');
        if (tab) {
            tab.classList.remove('bg-[#02E0FB]/10', 'text-[#00b8d9]');
            tab.classList.add('text-gray-600', 'hover:bg-gray-50');
        }
    });
    const content = document.getElementById('content-' + id);
    const tab = document.getElementById('tab-' + id);
    if (content) content.classList.remove('hidden');
    if (tab) {
        tab.classList.add('bg-[#02E0FB]/10', 'text-[#00b8d9]');
        tab.classList.remove('text-gray-600', 'hover:bg-gray-50');
    }
}

function loadSettings() {
    fetch('{{ route('admin.settings.load') }}', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        settingsCache = res.data || {};

        document.querySelectorAll('.setting-input').forEach(el => {
            const name = el.getAttribute('name');
            const val = settingsCache[name];
            if (val !== undefined && val !== null) {
                if (el.type === 'checkbox' || el.type === 'radio') {
                    el.checked = !!val;
                } else {
                    el.value = val;
                }
            }
        });

        document.querySelectorAll('.setting-checkbox').forEach(el => {
            const name = el.getAttribute('name');
            if (settingsCache[name] !== undefined) {
                el.checked = !!settingsCache[name];
            }
        });

        const workDays = settingsCache['work_days'];
        if (Array.isArray(workDays)) {
            document.querySelectorAll('input[name="work_days"]').forEach(el => {
                el.checked = workDays.includes(parseInt(el.value));
            });
        }

        if (settingsCache['smtp_host']) {
            const st = document.getElementById('smtpStatus');
            st.textContent = 'Yapılandırılmış';
            st.className = 'self-start sm:self-auto px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-600';
        }
    });
}

function saveSettings() {
    if (savePending) return;
    savePending = true;

    const buttons = document.querySelectorAll('[onclick="saveSettings()"]');
    const origHtml = buttons[0]?.innerHTML || '';

    buttons.forEach(btn => {
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Kaydediliyor...';
    });

    const data = {};

    document.querySelectorAll('.setting-input').forEach(el => {
        const name = el.getAttribute('name');
        if (name) data[name] = el.value;
    });

    document.querySelectorAll('.setting-checkbox').forEach(el => {
        const name = el.getAttribute('name');
        if (name) data[name] = el.checked ? true : false;
    });

    const workDays = [];
    document.querySelectorAll('input[name="work_days"]:checked').forEach(el => {
        workDays.push(parseInt(el.value));
    });
    data['work_days'] = workDays;

    fetch('{{ route('admin.settings.save') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ settings: data })
    })
    .then(r => r.json())
    .then(res => {
        savePending = false;
        buttons.forEach(btn => {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        });
        if (res.success) {
            showToast(res.message, 'success');
        } else {
            showToast(res.message || 'Hata oluştu.', 'error');
        }
    })
    .catch(() => {
        savePending = false;
        buttons.forEach(btn => {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        });
        showToast('Bağlantı hatası.', 'error');
    });
}

function showToast(msg, type) {
    const existing = document.querySelector('.settings-toast');
    if (existing) existing.remove();

    const el = document.createElement('div');
    el.className = 'settings-toast fixed top-4 right-4 z-50 px-5 py-3 rounded-xl shadow-lg text-sm font-medium transition-all duration-300 ' + (type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200');
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 300); }, 3000);
}
</script>
@endpush
