@extends('layouts.app')
@section('title', 'Abonelik Süresi Doldu')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center">
    <div class="max-w-lg w-full text-center">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 sm:p-12">
            <div class="w-16 h-16 mx-auto mb-4 bg-red-50 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Aboneliğiniz Sona Erdi</h1>
            <p class="text-gray-500 mb-6">
                Hesabınızın abonelik süresi dolmuştur. Tüm özelliklere erişmeye devam etmek için lütfen aboneliğinizi yenileyin.
            </p>
            <div class="bg-gray-50 rounded-xl p-4 mb-6 text-sm text-gray-600">
                <p>Deneme süreniz bittiğinde verileriniz <strong>30 gün</strong> boyunca güvende kalır. Aboneliğinizi yenilediğinizde kaldığınız yerden devam edebilirsiniz.</p>
            </div>
            <a href="{{ route('admin.subscriptions.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-[#02E0FB] text-gray-900 font-bold rounded-xl hover:bg-cyan-300 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Planları Görüntüle
            </a>
        </div>
    </div>
</div>
@endsection
