@extends('layouts.app')

@section('title', 'QR Giriş/Çıkış Kiosk')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900">QR Giriş/Çıkış</h1>
                <p class="text-gray-500 mt-2">Personel QR kodlarını yönetin</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($personels as $p)
                <div class="bg-gray-50 rounded-xl p-4 flex items-center gap-4 border border-gray-200">
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900">{{ $p->first_name }} {{ $p->last_name }}</h3>
                        <p class="text-sm text-gray-500">{{ $p->department?->name ?? '-' }} / {{ $p->position?->name ?? '-' }}</p>
                    </div>
                    <a href="{{ route('admin.qr.personel.qrcode', $p) }}"
                       target="_blank"
                       class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#FA6001] to-[#e05500] text-white text-sm font-semibold rounded-lg hover:opacity-90 transition">
                        QR Kod
                    </a>
                </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6 bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">QR Kullanımı</h2>
            <div class="prose max-w-none text-gray-600 text-sm space-y-2">
                <p>1. Personel QR kodunu yazdırın veya dijital olarak paylaşın.</p>
                <p>2. Personel kodu okutarak giriş/çıkış yapar.</p>
                <p>3. Her okutmada sırayla giriş ve çıkış kaydedilir.</p>
                <p class="text-yellow-600 font-medium">Not: QR kodları her personel için benzersizdir.</p>
            </div>
        </div>
    </div>
</div>
@endsection
