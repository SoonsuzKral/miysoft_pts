{{-- Blog form — AJAX multipart (başlık, içerik, öne çıkan görsel) --}}
<form id="blogForm" class="space-y-5" novalidate enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="blog_id" id="blogFormId" value="{{ $blog->id ?? '' }}">

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Başlık <span class="text-red-500">*</span></label>
        <input type="text" name="title" id="blogTitle" value="{{ $blog->title ?? '' }}" required
            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent"
            placeholder="Blog yazısı başlığı" oninput="autoSlug(this.value)">
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Slug <span class="text-xs font-normal text-gray-400">(URL)</span></label>
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-400 shrink-0">/blog/</span>
            <input type="text" name="slug" id="blogSlug" value="{{ $blog->slug ?? '' }}"
                class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent font-mono text-gray-600"
                placeholder="otomatik">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Kategori</label>
            <select name="category_id" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#02E0FB]">
                <option value="">— Seçiniz —</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ isset($blog) && $blog->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Yayın Tarihi</label>
            <input type="datetime-local" name="published_at"
                value="{{ isset($blog) ? $blog->published_at?->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i') }}"
                class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#02E0FB]">
        </div>
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Öne Çıkan Görsel <span class="text-xs font-normal text-gray-400">(AJAX kayıtta FormData ile gönderilir)</span></label>
        <div id="imageUploadArea"
             class="relative border-2 border-dashed border-gray-200 rounded-2xl p-6 text-center cursor-pointer hover:border-[#02E0FB] hover:bg-[#02E0FB]/5 transition-all group"
             onclick="document.getElementById('featuredImageInput').click()">

            <div id="imagePreview" class="{{ isset($blog) && $blog->featured_image ? '' : 'hidden' }} mb-3">
                <img id="previewImg" src="{{ isset($blog) && $blog->featured_image ? Storage::url($blog->featured_image) : '' }}"
                     alt="Önizleme" class="mx-auto max-h-40 rounded-xl object-cover shadow-md">
            </div>

            <div id="uploadPlaceholder" class="{{ isset($blog) && $blog->featured_image ? 'hidden' : '' }}">
                <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-gray-100 group-hover:bg-[#02E0FB]/10 flex items-center justify-center transition-colors">
                    <svg class="w-6 h-6 text-gray-400 group-hover:text-[#02E0FB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-600">Görsel seçin</p>
                <p class="text-xs text-gray-400 mt-1">PNG, JPG, WEBP — maks. 2 MB</p>
            </div>

            <input type="file" id="featuredImageInput" name="featured_image" accept="image/*" class="hidden" onchange="previewImage(this)">

            <div id="uploadProgress" class="hidden mt-3">
                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div id="progressBar" class="h-2 rounded-full bg-[#02E0FB] transition-all duration-150" style="width:0%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1" id="progressText">Yükleniyor...</p>
            </div>
        </div>

        @if(isset($blog) && $blog->featured_image)
        <button type="button" onclick="removeImage()" class="mt-2 text-xs text-red-500 hover:text-red-700 font-medium">× Görseli Kaldır</button>
        @endif
        <input type="hidden" name="remove_featured_image" id="removeFeaturedImage" value="0">
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Özet</label>
        <textarea name="summary" rows="2" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#02E0FB] resize-none"
                  placeholder="Kısa özet...">{{ $blog->summary ?? '' }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">İçerik <span class="text-red-500">*</span></label>
        <div class="border border-gray-200 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-[#02E0FB]">
            <div class="flex flex-wrap gap-1 p-2 bg-gray-50 border-b border-gray-200">
                @foreach([['B','bold','font-bold'],['I','italic','italic'],['U','underline','underline'],['H2','formatBlock','H2'],['H3','formatBlock','H3'],['OL','insertOrderedList',''],['UL','insertUnorderedList',''],['🔗','createLink','']] as [$lbl,$cmd,$cls])
                <button type="button" onclick="execCmd('{{ $cmd }}', '{{ $cls }}')"
                    class="px-2.5 py-1 text-xs {{ $cls }} rounded-lg text-gray-600 hover:bg-[#02E0FB]/10 hover:text-[#02E0FB] transition-colors">
                    {{ $lbl }}
                </button>
                @endforeach
            </div>
            <div id="contentEditor" contenteditable="true"
                 class="min-h-48 max-h-72 overflow-y-auto p-4 text-sm text-gray-700 focus:outline-none leading-relaxed">
                {!! $blog->content ?? '' !!}
            </div>
        </div>
        <textarea name="content" id="contentHidden" class="hidden">{{ $blog->content ?? '' }}</textarea>
        <p class="mt-1 text-xs text-gray-400">HTML; vitrinde güvenli şekilde basılır.</p>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">SEO Başlık</label>
            <input type="text" name="meta_title" value="{{ $blog->meta_title ?? '' }}" maxlength="60"
                class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#02E0FB]">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">SEO Açıklama</label>
            <input type="text" name="meta_description" value="{{ $blog->meta_description ?? '' }}" maxlength="160"
                class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#02E0FB]">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Okuma Süresi (dk)</label>
            <input type="number" name="reading_time" value="{{ $blog->reading_time ?? 5 }}" min="1" max="60"
                class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#02E0FB]">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Etiketler</label>
            <input type="text" name="tags_input" value="{{ isset($blog) && $blog->tags ? implode(', ', $blog->tags) : '' }}"
                class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#02E0FB]"
                placeholder="laravel, hr">
        </div>
    </div>

    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100">
        <div>
            <p class="text-sm font-semibold text-gray-800">Yayınla</p>
            <p class="text-xs text-gray-500">Kapalıysa taslak.</p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="is_published" id="isPublished" class="sr-only peer" {{ isset($blog) && $blog->is_published ? 'checked' : '' }}>
            <div class="w-12 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#02E0FB] transition-colors
                        after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                        after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
        </label>
    </div>
</form>

<script>
function autoSlug(title) {
    const slug = title.toLowerCase()
        .replace(/ğ/g,'g').replace(/ü/g,'u').replace(/ş/g,'s')
        .replace(/ı/g,'i').replace(/ö/g,'o').replace(/ç/g,'c')
        .replace(/[^a-z0-9\s-]/g,'')
        .trim().replace(/\s+/g,'-');
    const slugEl = document.getElementById('blogSlug');
    if (slugEl && !slugEl.dataset.manual) slugEl.value = slug;
}

document.getElementById('blogSlug')?.addEventListener('input', function() {
    this.dataset.manual = '1';
});

function previewImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > 2 * 1024 * 1024) {
            if (typeof Swal !== 'undefined') Swal.fire({ title: 'Dosya çok büyük', text: 'Maks. 2 MB.', icon: 'error' });
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').classList.remove('hidden');
            document.getElementById('uploadPlaceholder').classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }
}

function removeImage() {
    document.getElementById('featuredImageInput').value = '';
    document.getElementById('imagePreview').classList.add('hidden');
    document.getElementById('uploadPlaceholder').classList.remove('hidden');
    document.getElementById('removeFeaturedImage').value = '1';
}

function execCmd(cmd, arg) {
    if (cmd === 'createLink') {
        const url = prompt('Link URL:');
        if (url) document.execCommand(cmd, false, url);
    } else if (cmd === 'formatBlock') {
        document.execCommand(cmd, false, arg);
    } else {
        document.execCommand(cmd, false, null);
    }
    document.getElementById('contentEditor')?.focus();
}

document.getElementById('blogForm')?.addEventListener('submit', function() {
    const ed = document.getElementById('contentEditor');
    const hi = document.getElementById('contentHidden');
    if (ed && hi) hi.value = ed.innerHTML;
});
</script>
