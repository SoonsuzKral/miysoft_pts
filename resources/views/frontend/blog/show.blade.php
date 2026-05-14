@extends('frontend._layout')
@section('title', ($post->title ?? 'Blog Yazısı') . ' — MİYSOFT Blog')

@section('content')
{{-- Hero --}}
<section class="relative py-20 bg-gradient-to-b from-gray-950 to-gray-900 overflow-hidden">
    <div class="absolute inset-0 z-0 pointer-events-none">
        <div class="absolute top-0 left-1/4 w-96 h-96 rounded-full" style="background:rgba(2,224,251,.06);filter:blur(80px);"></div>
        <div class="absolute bottom-0 right-1/4 w-72 h-72 rounded-full" style="background:rgba(250,96,1,.06);filter:blur(80px);"></div>
    </div>
    <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        @if($post->category ?? null)
        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold mb-5 border"
              style="background: {{ $post->category->color ?? '#02E0FB' }}15; color: {{ $post->category->color ?? '#02E0FB' }}; border-color: {{ $post->category->color ?? '#02E0FB' }}30;">
            {{ $post->category->name }}
        </span>
        @endif
        <h1 class="text-3xl md:text-5xl font-black text-white leading-tight mb-6">{{ $post->title ?? 'Blog Yazısı' }}</h1>
        <div class="flex items-center justify-center flex-wrap gap-5 text-sm text-gray-400">
            @if($post->author ?? null)
            <span class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-full bg-gradient-to-br from-[#02E0FB] to-[#FA6001] flex items-center justify-center text-white text-xs font-bold">
                    {{ strtoupper(substr($post->author->name ?? 'M', 0, 1)) }}
                </span>
                {{ $post->author->name ?? 'MİYSOFT' }}
            </span>
            @endif
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                {{ $post->published_at ? $post->published_at->format('d M Y') : now()->format('d M Y') }}
            </span>
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ $post->reading_time ?? 5 }} dk okuma
            </span>
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                {{ number_format($post->views_count ?? 0) }} görüntülenme
            </span>
        </div>
    </div>
</section>

{{-- Featured Image --}}
@if($post->featured_image ?? false)
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-20">
    <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}"
         class="w-full h-72 md:h-96 object-cover rounded-3xl shadow-2xl border border-gray-100">
</div>
@endif

{{-- Article Content --}}
<section class="py-16 bg-[#FEFEFE]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

            {{-- Main Content --}}
            <article class="lg:col-span-8">
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 md:p-12">
                    @if($post->summary ?? null)
                    <div class="border-l-4 border-[#02E0FB] pl-5 mb-8 py-2 bg-[#02E0FB]/5 rounded-r-xl">
                        <p class="text-base text-gray-600 italic leading-relaxed">{{ $post->summary }}</p>
                    </div>
                    @endif

                    <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed
                                prose-headings:font-black prose-headings:text-gray-900
                                prose-a:text-[#02E0FB] prose-a:no-underline hover:prose-a:underline
                                prose-code:bg-gray-100 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded prose-code:text-sm
                                prose-blockquote:border-l-[#02E0FB] prose-blockquote:bg-[#02E0FB]/5 prose-blockquote:rounded-r-xl prose-blockquote:py-1
                                prose-img:rounded-2xl prose-img:shadow-lg">
                        {!! $post->content ?? $post->body ?? '<p class="text-gray-500">İçerik yükleniyor...</p>' !!}
                    </div>

                    {{-- Tags --}}
                    @if(($post->tags ?? null) && count($post->tags) > 0)
                    <div class="flex flex-wrap gap-2 mt-10 pt-8 border-t border-gray-100">
                        @foreach($post->tags as $tag)
                        <a href="{{ route('blog.index', ['tag' => $tag]) }}"
                           class="px-3 py-1.5 rounded-xl text-sm font-medium bg-gray-100 text-gray-600 hover:bg-[#02E0FB]/10 hover:text-[#02E0FB] transition-all">
                            #{{ $tag }}
                        </a>
                        @endforeach
                    </div>
                    @endif

                    {{-- Share --}}
                    <div class="flex items-center gap-4 mt-8 pt-6 border-t border-gray-100">
                        <span class="text-sm font-semibold text-gray-700">Paylaş:</span>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title ?? '') }}"
                           target="_blank" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-black text-white text-xs font-semibold hover:bg-gray-800 transition-all">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.742l7.774-8.905L2.0 2.25h6.962l4.264 5.638L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            X / Twitter
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->url()) }}"
                           target="_blank" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-[#0077B5] text-white text-xs font-semibold hover:bg-[#005f8e] transition-all">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                            LinkedIn
                        </a>
                    </div>
                </div>

                {{-- Author Card --}}
                @if($post->author ?? null)
                <div class="mt-6 bg-white rounded-3xl border border-gray-100 shadow-sm p-6 flex items-start gap-5">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#02E0FB] to-[#FA6001] flex items-center justify-center text-white text-2xl font-black flex-shrink-0">
                        {{ strtoupper(substr($post->author->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-widest mb-1">Yazar</p>
                        <p class="font-bold text-gray-900 text-lg">{{ $post->author->name }}</p>
                        <p class="text-sm text-gray-500 mt-1">MİYSOFT PTS içerik ekibi</p>
                    </div>
                </div>
                @endif
            </article>

            {{-- Sidebar --}}
            <aside class="lg:col-span-4 space-y-6">
                {{-- Back --}}
                <a href="{{ route('blog.index') }}" class="flex items-center gap-2 text-sm font-semibold text-[#02E0FB] hover:underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Tüm Blog Yazıları
                </a>

                {{-- CTA Box --}}
                <div class="bg-gradient-to-br from-[#02E0FB] to-[#00b8d9] rounded-2xl p-6 text-center">
                    <p class="text-lg font-black text-gray-900 mb-2">14 Gün Ücretsiz</p>
                    <p class="text-sm text-gray-800 mb-5 leading-snug">MİYSOFT PTS'i ücretsiz deneyin, hiç kredi kartı gerekmez.</p>
                    <a href="{{ route('free-trial') }}" class="block w-full py-3 rounded-xl bg-gray-900 text-white text-sm font-bold hover:bg-black transition-all">
                        Hemen Başla →
                    </a>
                </div>

                {{-- Table of Contents placeholder --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <p class="text-sm font-bold text-gray-900 mb-3">Bu Yazıda</p>
                    <p class="text-xs text-gray-400">İçerik tablosu yükleniyor...</p>
                </div>
            </aside>
        </div>
    </div>
</section>

{{-- Related Posts --}}
@if(isset($relatedPosts) && $relatedPosts->count() > 0)
<section class="py-16 bg-gray-50 border-t border-gray-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-black text-gray-900 mb-8">İlgili Yazılar</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($relatedPosts as $rel)
            <a href="{{ route('blog.show', $rel->slug) }}" class="group bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                @if($rel->featured_image ?? false)
                <img src="{{ Storage::url($rel->featured_image) }}" alt="{{ $rel->title }}" class="w-full h-36 object-cover">
                @else
                <div class="w-full h-36 bg-gradient-to-br from-[#02E0FB]/10 to-[#FA6001]/10 flex items-center justify-center text-4xl">📝</div>
                @endif
                <div class="p-5">
                    @if($rel->category ?? null)
                    <span class="text-xs font-semibold text-[#02E0FB] mb-2 block">{{ $rel->category->name }}</span>
                    @endif
                    <h3 class="font-bold text-gray-900 text-sm leading-snug line-clamp-2 group-hover:text-[#02E0FB] transition-colors">{{ $rel->title }}</h3>
                    <p class="text-xs text-gray-500 mt-2 line-clamp-2">{{ $rel->summary }}</p>
                    <span class="inline-block mt-3 text-xs font-semibold text-[#02E0FB]">Devamını Oku →</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
