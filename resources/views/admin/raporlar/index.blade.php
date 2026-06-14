@extends('layouts.app')

@section('title', 'Raporlar')

@section('page_header')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Raporlar ve Analizler</h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-1">Puantaj, izin, masraf ve personel raporlarını görüntüleyin ve dışa aktarın.</p>
    </div>
    <div class="flex items-center gap-2 text-xs text-gray-400">
        <span class="w-2 h-2 rounded-full bg-yellow-400 inline-block"></span> Bekleyen
        <span class="w-2 h-2 rounded-full bg-green-400 inline-block"></span> Tamamlanan
        <span class="w-2 h-2 rounded-full bg-red-400 inline-block"></span> Hatalı
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Rapor Kartları --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6">

        {{-- Tatil Raporu --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 flex flex-col gap-3 sm:gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Tatil Raporu</h3>
                    <p class="text-xs text-gray-500">Resmi tatil takvimi</p>
                </div>
            </div>
            <div class="flex gap-2 mt-auto">
                <button onclick="generateReport('holiday')" class="flex-1 inline-flex items-center justify-center gap-1 sm:gap-2 px-3 sm:px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-all">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Excel İndir
                </button>
            </div>
        </div>

        {{-- Puantaj Raporu --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 flex flex-col gap-3 sm:gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-[#02E0FB]/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#02E0FB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Puantaj Raporu</h3>
                    <p class="text-xs text-gray-500">Aylık çalışma saatleri</p>
                </div>
            </div>
            <div class="space-y-2">
                <div>
                    <label class="text-xs text-gray-500">Ay/Yıl</label>
                    <input type="month" id="attendanceMonth" class="mt-1 w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB]" value="{{ date('Y-m') }}">
                </div>
            </div>
            <div class="flex gap-2 mt-auto">
                <button onclick="generateReport('attendance', '{{ date('Y') }}', '{{ date('m') }}')" class="flex-1 inline-flex items-center justify-center gap-1 sm:gap-2 px-3 sm:px-4 py-2.5 bg-[#02E0FB] hover:bg-[#00b8d9] text-gray-900 rounded-xl text-sm font-semibold transition-all">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Excel İndir
                </button>
            </div>
        </div>

        {{-- İzin Raporu --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 flex flex-col gap-3 sm:gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-[#FA6001]/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#FA6001]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">İzin Raporu</h3>
                    <p class="text-xs text-gray-500">İzin kullanım ve bakiye analizi</p>
                </div>
            </div>
            <div class="flex gap-2 mt-auto">
                <button onclick="generateReport('leave')" class="flex-1 inline-flex items-center justify-center gap-1 sm:gap-2 px-3 sm:px-4 py-2.5 bg-[#FA6001] hover:bg-[#e05500] text-white rounded-xl text-sm font-semibold transition-all">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Excel İndir
                </button>
            </div>
        </div>

        {{-- Personel Raporu --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 flex flex-col gap-3 sm:gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Personel Listesi</h3>
                    <p class="text-xs text-gray-500">Tüm personel detaylı raporu</p>
                </div>
            </div>
            <div class="flex gap-2 mt-auto">
                <button onclick="generateReport('personel')" class="flex-1 inline-flex items-center justify-center gap-1 sm:gap-2 px-3 sm:px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-sm font-semibold transition-all">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Excel İndir
                </button>
            </div>
        </div>

        {{-- Masraf Raporu --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 flex flex-col gap-3 sm:gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Masraf & Avans Raporu</h3>
                    <p class="text-xs text-gray-500">Finansal harcama analizi</p>
                </div>
            </div>
            <div class="flex gap-2 mt-auto">
                <button onclick="generateReport('expense')" class="flex-1 inline-flex items-center justify-center gap-1 sm:gap-2 px-3 sm:px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-semibold transition-all">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Excel İndir
                </button>
            </div>
        </div>

        {{-- Envanter Raporu --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 flex flex-col gap-3 sm:gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-yellow-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Envanter Raporu</h3>
                    <p class="text-xs text-gray-500">Zimmet ve varlık durumu</p>
                </div>
            </div>
            <div class="flex gap-2 mt-auto">
                <button onclick="generateReport('asset')" class="flex-1 inline-flex items-center justify-center gap-1 sm:gap-2 px-3 sm:px-4 py-2.5 bg-yellow-600 hover:bg-yellow-700 text-white rounded-xl text-sm font-semibold transition-all">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Excel İndir
                </button>
            </div>
        </div>

        {{-- Denetim Raporu --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 flex flex-col gap-3 sm:gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Denetim Kayıtları</h3>
                    <p class="text-xs text-gray-500">Sistem aktivite logları</p>
                </div>
            </div>
            <div class="flex gap-2 mt-auto">
                <button onclick="generateReport('audit')" class="flex-1 inline-flex items-center justify-center gap-1 sm:gap-2 px-3 sm:px-4 py-2.5 bg-gray-700 hover:bg-gray-800 text-white rounded-xl text-sm font-semibold transition-all">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Excel İndir
                </button>
            </div>
        </div>

    </div>

    {{-- Son Raporlar --}}
    @if(count($recentExports) > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">Son Raporlarınız</h3>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($recentExports as $export)
            <div class="px-4 sm:px-6 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="w-2 h-2 rounded-full inline-block
                        @if($export->status === 'completed') bg-green-400
                        @elseif($export->status === 'failed') bg-red-400
                        @else bg-yellow-400 @endif">
                    </span>
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $export->module_label }}</p>
                        <p class="text-xs text-gray-400">{{ $export->created_at->format('d.m.Y H:i') }}</p>
                    </div>
                </div>
                <div>
                    @if($export->status === 'completed')
                        <a href="{{ route('admin.reports.download', $export->id) }}" class="text-xs font-medium text-[#02E0FB] hover:underline">İndir</a>
                    @elseif($export->status === 'failed')
                        <span class="text-xs text-red-500">Hata</span>
                    @else
                        <span class="text-xs text-yellow-500">Hazırlanıyor...</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
function generateReport(module, year, month) {
    const btn = event.currentTarget;
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Oluşturuluyor...';

    fetch('{{ route('admin.reports.generate') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ module, year, month })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const exportId = data.export_id;
            const downloadUrl = '/admin/reports/download/' + exportId;
            const checkExport = setInterval(() => {
                fetch('/admin/exports/status?export_id=' + exportId, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(status => {
                    if (status.data?.status === 'completed') {
                        clearInterval(checkExport);
                        window.location.href = downloadUrl;
                        Swal?.fire({ title: 'Rapor Hazır!', icon: 'success', timer: 2000 });
                    } else if (status.data?.status === 'failed') {
                        clearInterval(checkExport);
                        Swal?.fire({ title: 'Hata!', text: status.data.error_message, icon: 'error' });
                    }
                });
            }, 2000);
            setTimeout(() => clearInterval(checkExport), 60000);
        } else {
            Swal?.fire({ title: 'Hata!', text: data.message, icon: 'error' });
        }
    })
    .catch(() => Swal?.fire({ title: 'Hata!', text: 'Rapor oluşturulamadı.', icon: 'error' }))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> Excel İndir';
    });
}
</script>
@endpush
@endsection
