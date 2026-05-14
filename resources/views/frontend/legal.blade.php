@extends('frontend._layout')
@section('title', $title ?? 'Yasal')

@section('content')
<section class="py-20 bg-gradient-to-b from-gray-50 to-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8 md:p-12">
            <h1 class="text-3xl font-black text-gray-900 mb-8">{{ $title ?? 'Yasal Metin' }}</h1>
            <div class="prose prose-lg max-w-none text-gray-600 leading-relaxed">
                @if(isset($content) && $content)
                    {!! $content !!}
                @else
                <p>Bu sayfa CMS üzerinden yönetilebilir. Şu an için varsayılan metin gösterilmektedir.</p>
                <p>MİYSOFT PTS olarak kişisel verilerinizin güvenliği bizim için önemlidir. KVKK kapsamında verileriniz korunmaktadır.</p>
                <p>Gizlilik politikası ve kullanım şartları metinleri yönetim panelinden düzenlenebilir.</p>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
