@extends('frontend._layout')
@section('title', ($post->meta_title ?? $post->title ?? 'Blog Yazısı') . ' — MİYSOFT Blog')
@section('meta_description', $post->meta_description ?? '')

@section('content')
<section class="relative isolate overflow-hidden py-20 bg-gradient-to-b from-gray-950 to-gray-900">
    <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden" aria-hidden="true">
        <div class="absolute top-1/4 left-1/4 h-72 w-72 -translate-x-1/2 rounded-full bg-cyan-400/10 blur-3xl opacity-60"></div>
        <div class="absolute bottom-1/4 right-1/4 h-64 w-64 translate-x-1/4 rounded-full bg-orange-500/10 blur-3xl opacity-50"></div>
    </div>
    <div class="relative z-10 mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
        @if($post->category ?? null)
        <span class="mb-5 inline-block rounded-full border px-3 py-1 text-xs font-bold"
              style="background: {{ $post->category->color ?? '#02E0FB' }}15; color: {{ $post->category->color ?? '#02E0FB' }}; border-color: {{ $post->category->color ?? '#02E0FB' }}30;">
            {{ $post->category->name }}
        </span>
        @endif
        <h1 class="mb-6 text-3xl font-black leading-tight text-white md:text-5xl">{{ $post->title ?? 'Blog Yazısı' }}</h1>
        <div class="flex flex-wrap items-center justify-center gap-5 text-sm text-gray-400">
            @if($post->author ?? null)
            <span class="flex items-center gap-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-[#02E0FB] to-[#FA6001] text-xs font-bold text-white">
                    {{ strtoupper(substr($post->author->name ?? 'M', 0, 1)) }}
                </span>
                {{ $post->author->name ?? 'MİYSOFT' }}
            </span>
            @endif
            <span class="flex items-center gap-1.5">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                {{ $post->published_at ? $post->published_at->format('d.m.Y') : now()->format('d.m.Y') }}
            </span>
            <span class="flex items-center gap-1.5">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ $post->reading_time ?? 5 }} dk okuma
            </span>
            <span class="flex items-center gap-1.5">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                {{ number_format($post->views_count ?? 0) }} görüntülenme
            </span>
        </div>
    </div>
</section>

@if($post->featured_image ?? false)
<div class="relative z-20 mx-auto -mt-8 max-w-4xl px-4 sm:px-6 lg:px-8">
    <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}"
         class="h-72 w-full rounded-3xl border border-gray-100 object-cover shadow-xl md:h-96">
</div>
@endif

<section class="bg-[#FEFEFE] py-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-10 lg:grid-cols-12">
            <article class="lg:col-span-8">
                <div class="rounded-3xl border border-gray-100 bg-white p-8 shadow-sm md:p-12">
                    @if($post->summary ?? null)
                    <div class="mb-8 rounded-r-xl border-l-4 border-[#02E0FB] bg-[#02E0FB]/5 py-2 pl-5">
                        <p class="text-base italic leading-relaxed text-gray-600">{{ $post->summary }}</p>
                    </div>
                    @endif

                    <div class="prose prose-lg max-w-none leading-relaxed text-gray-700
                                prose-headings:font-black prose-headings:text-gray-900
                                prose-a:text-[#02E0FB] prose-a:no-underline hover:prose-a:underline
                                prose-code:rounded prose-code:bg-gray-100 prose-code:px-1.5 prose-code:py-0.5 prose-code:text-sm
                                prose-blockquote:rounded-r-xl prose-blockquote:border-l-[#02E0FB] prose-blockquote:bg-[#02E0FB]/5 prose-blockquote:py-1
                                prose-img:rounded-2xl prose-img:shadow-lg">
                        @php
                            $raw = $post->content ?? $post->body ?? '';
                            $isHtml = $raw !== '' && preg_match('/<\s*[a-z][\s\S]*>/i', $raw);
                        @endphp
                        @if($isHtml)
                            {!! $raw !!}
                        @else
                            <div class="whitespace-pre-wrap">{{ $raw }}</div>
                        @endif
                    </div>

                    @if(($post->tags ?? null) && count($post->tags) > 0)
                    <div class="mt-10 flex flex-wrap gap-2 border-t border-gray-100 pt-8">
                        @foreach($post->tags as $tag)
                        <a href="{{ route('blog.index', ['tag' => $tag]) }}"
                           class="rounded-xl bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-600 transition-all hover:bg-[#02E0FB]/10 hover:text-[#02E0FB]">
                            #{{ $tag }}
                        </a>
                        @endforeach
                    </div>
                    @endif

                    <div class="mt-8 flex items-center gap-4 border-t border-gray-100 pt-6">
                        <span class="text-sm font-semibold text-gray-700">Paylaş:</span>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title ?? '') }}"
                           target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl bg-black px-4 py-2 text-xs font-semibold text-white transition-all hover:bg-gray-800">
                            X / Twitter
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->url()) }}"
                           target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl bg-[#0077B5] px-4 py-2 text-xs font-semibold text-white hover:bg-[#005f8e]">
                            LinkedIn
                        </a>
                    </div>
                </div>

                @if($post->author ?? null)
                <div class="mt-6 flex items-start gap-5 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-[#02E0FB] to-[#FA6001] text-2xl font-black text-white">
                        {{ strtoupper(substr($post->author->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="mb-1 text-xs font-semibold uppercase tracking-widest text-gray-400">Yazar</p>
                        <p class="text-lg font-bold text-gray-900">{{ $post->author->name }}</p>
                        <p class="mt-1 text-sm text-gray-500">MİYSOFT PTS içerik ekibi</p>
                    </div>
                </div>
                @endif
            </article>

            <aside class="space-y-6 lg:col-span-4">
                <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#02E0FB] hover:underline">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Tüm Blog Yazıları
                </a>
                <div class="rounded-2xl bg-gradient-to-br from-[#02E0FB] to-[#00b8d9] p-6 text-center">
                    <p class="mb-2 text-lg font-black text-gray-900">14 Gün Ücretsiz</p>
                    <p class="mb-5 text-sm leading-snug text-gray-800">MİYSOFT PTS'i ücretsiz deneyin.</p>
                    <a href="{{ route('free-trial') }}" class="block w-full rounded-xl bg-gray-900 py-3 text-sm font-bold text-white hover:bg-black">
                        Hemen Başla →
                    </a>
                </div>
            </aside>
        </div>
    </div>
</section>

@if(isset($relatedPosts) && $relatedPosts->count() > 0)
<section class="border-t border-gray-100 bg-gray-50 py-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <h2 class="mb-8 text-2xl font-black text-gray-900">İlgili Yazılar</h2>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            @foreach($relatedPosts as $rel)
            <a href="{{ route('blog.show', $rel->slug) }}" class="group overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                @if($rel->featured_image ?? false)
                <img src="{{ Storage::url($rel->featured_image) }}" alt="{{ $rel->title }}" class="h-36 w-full object-cover">
                @else
                <div class="flex h-36 w-full items-center justify-center bg-gradient-to-br from-[#02E0FB]/10 to-[#FA6001]/10 text-4xl">📝</div>
                @endif
                <div class="p-5">
                    @if($rel->category ?? null)
                    <span class="mb-2 block text-xs font-semibold text-[#02E0FB]">{{ $rel->category->name }}</span>
                    @endif
                    <h3 class="line-clamp-2 text-sm font-bold leading-snug text-gray-900 transition-colors group-hover:text-[#02E0FB]">{{ $rel->title }}</h3>
                    <p class="mt-2 line-clamp-2 text-xs text-gray-500">{{ $rel->summary }}</p>
                    <span class="mt-3 inline-block text-xs font-semibold text-[#02E0FB]">Devamını Oku →</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
