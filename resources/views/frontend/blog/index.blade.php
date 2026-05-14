@extends('frontend._layout')
@section('title', 'Blog')

@section('content')
<section class="py-20 bg-gradient-to-b from-gray-50 to-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="inline-block px-4 py-1.5 rounded-full bg-[#02E0FB]/10 text-[#02E0FB] text-sm font-semibold mb-4">Blog</span>
            <h1 class="text-4xl lg:text-5xl font-black text-gray-900 mb-4">İK ve Teknoloji Dünyası</h1>
            <p class="text-xl text-gray-500 max-w-2xl mx-auto">Personel yönetimi, İK trendleri ve MİYSOFT PTS güncellemeleri hakkında güncel yazılar.</p>
        </div>

        {{-- Kategori Filtreleri --}}
        @if($categories->count() > 0)
        <div class="flex flex-wrap justify-center gap-2 mb-12">
            <a href="{{ route('blog.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium {{ !request('category') ? 'bg-[#02E0FB] text-gray-900' : 'bg-gray-100 text-gray-600 hover:bg-[#02E0FB]/10' }} transition-colors">Tümü</a>
            @foreach($categories as $cat)
            <a href="{{ route('blog.index', ['category' => $cat->slug]) }}" class="px-4 py-2 rounded-xl text-sm font-medium {{ request('category') === $cat->slug ? 'bg-[#02E0FB] text-gray-900' : 'bg-gray-100 text-gray-600 hover:bg-[#02E0FB]/10' }} transition-colors">{{ $cat->name }}</a>
            @endforeach
        </div>
        @endif

        {{-- Blog Kartları --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($posts as $post)
            <article class="bg-white rounded-3xl overflow-hidden border border-gray-100 shadow-sm card-hover">
                @if($post->featured_image)
                <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                @else
                <div class="w-full h-48 bg-gradient-to-br from-[#02E0FB]/10 to-[#FA6001]/10 flex items-center justify-center text-5xl">📝</div>
                @endif
                <div class="p-6">
                    @if($post->category)
                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold mb-3" style="background: {{ $post->category->color ?? '#02E0FB' }}20; color: {{ $post->category->color ?? '#02E0FB' }}">{{ $post->category->name }}</span>
                    @endif
                    <h2 class="font-bold text-gray-900 text-lg mb-2 line-clamp-2 leading-snug">
                        <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-[#02E0FB] transition-colors">{{ $post->title }}</a>
                    </h2>
                    <p class="text-sm text-gray-500 line-clamp-2 mb-4">{{ $post->summary }}</p>
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <span class="text-xs text-gray-400">{{ $post->reading_time ?? 5 }} dk okuma</span>
                        <a href="{{ route('blog.show', $post->slug) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#02E0FB] hover:bg-[#00b8d9] text-gray-900 font-bold text-sm transition-all shadow-md hover:shadow-lg hover:scale-105">Devamını Oku →</a>
                    </div>
                </div>
            </article>
            @empty
            @foreach([
                ['Personel Yönetiminde Dijital Dönüşüm','İK süreçlerinizi nasıl hızlandırabilirsiniz?','5'],
                ['Uzaktan Çalışmada Puantaj Çözümleri','Hibrit modelde giriş-çıkış takibi.','4'],
                ['İzin Yönetimi En İyi Uygulamaları','Çok kademeli onay ve bakiye takibi.','6'],
            ] as [$title,$desc,$min])
            <article class="bg-white rounded-3xl overflow-hidden border border-gray-100 shadow-sm card-hover">
                <div class="w-full h-48 bg-gradient-to-br from-[#02E0FB]/10 to-[#FA6001]/10 flex items-center justify-center text-5xl">📝</div>
                <div class="p-6">
                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold mb-3 bg-[#02E0FB]/20 text-[#02E0FB]">Genel</span>
                    <h2 class="font-bold text-gray-900 text-lg mb-2 leading-snug">{{ $title }}</h2>
                    <p class="text-sm text-gray-500 line-clamp-2 mb-4">{{ $desc }}</p>
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <span class="text-xs text-gray-400">{{ $min }} dk okuma</span>
                        <a href="#" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#FA6001] hover:bg-[#e05500] text-white font-bold text-sm transition-all shadow-md hover:shadow-lg hover:scale-105">Devamını Oku →</a>
                    </div>
                </div>
            </article>
            @endforeach
            @endforelse
        </div>

        @if(method_exists($posts, 'links'))
        <div class="mt-12 flex justify-center">
            {{ $posts->links() }}
        </div>
        @endif
    </div>
</section>
@endsection
