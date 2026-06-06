<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QR Giriş/Çıkış — MİYSOFT PTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
        .pulse-dot { animation: pulse 1.5s ease-in-out infinite; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 min-h-screen flex items-center justify-center p-4">
    @if(isset($error))
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full text-center">
            <div class="text-6xl mb-4">⚠️</div>
            <h1 class="text-2xl font-bold text-red-600 mb-2">Geçersiz QR Kod</h1>
            <p class="text-gray-500">{{ $error }}</p>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full text-center">
            <div class="text-5xl mb-4">
                <span class="pulse-dot inline-block w-4 h-4 rounded-full {{ $nextType === 'in' ? 'bg-green-500' : 'bg-red-500' }}"></span>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900 mb-1">{{ $personel->first_name }} {{ $personel->last_name }}</h1>
            <p class="text-gray-500 text-sm mb-6">{{ $personel->department?->name ?? '-' }}</p>

            <div class="text-6xl font-black mb-4 {{ $nextType === 'in' ? 'text-green-500' : 'text-red-500' }}">
                {{ $nextType === 'in' ? 'GİRİŞ' : 'ÇIKIŞ' }}
            </div>

            @if($lastRecord)
                <p class="text-xs text-gray-400 mb-6">Son kayıt: {{ $lastRecord->type_label }} — {{ $lastRecord->recorded_at->format('H:i') }}</p>
            @else
                <p class="text-xs text-gray-400 mb-6">Bugün henüz kayıt yok</p>
            @endif

            <button onclick="submitScan()"
                    class="w-full py-4 px-6 rounded-xl text-white font-bold text-lg transition transform hover:scale-105 {{ $nextType === 'in' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }}">
                {{ $nextType === 'in' ? '👋 Giriş Yap' : '🚶 Çıkış Yap' }}
            </button>

            <div id="message" class="mt-4 text-sm font-medium hidden"></div>
        </div>

        <script>
            async function submitScan() {
                const btn = event.target;
                const msg = document.getElementById('message');
                btn.disabled = true;
                btn.textContent = 'İşleniyor...';
                msg.className = 'mt-4 text-sm font-medium hidden';

                try {
                    const res = await fetch('{{ route("qr.scan.submit", $personel->qr_token) }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                    });
                    const data = await res.json();

                    if (data.success) {
                        msg.className = 'mt-4 text-sm font-medium text-green-600';
                        msg.textContent = '✅ ' + data.message;
                        setTimeout(() => window.location.reload(), 2000);
                    } else {
                        msg.className = 'mt-4 text-sm font-medium text-red-600';
                        msg.textContent = '❌ ' + data.message;
                        btn.disabled = false;
                        btn.textContent = '{{ $nextType === "in" ? "👋 Giriş Yap" : "🚶 Çıkış Yap" }}';
                    }
                } catch (e) {
                    msg.className = 'mt-4 text-sm font-medium text-red-600';
                    msg.textContent = '❌ Bağlantı hatası';
                    btn.disabled = false;
                    btn.textContent = '{{ $nextType === "in" ? "👋 Giriş Yap" : "🚶 Çıkış Yap" }}';
                }
            }
        </script>
    @endif
</body>
</html>
