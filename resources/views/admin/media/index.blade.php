@extends('layouts.app')
@section('title', 'Medya Kütüphanesi')

@section('page_header')
<div>
    <h1 class="text-2xl font-bold text-gray-900">Medya Kütüphanesi</h1>
    <p class="text-sm text-gray-500 mt-1">Yüklenen tüm dosyaları görüntüleyin ve yönetin.</p>
</div>
<button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2.5 bg-[#02E0FB] hover:bg-cyan-400 text-gray-900 rounded-xl text-sm font-semibold transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Dosya Yükle
    </button>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-2">
        <div class="flex items-center gap-3">
            <select id="mimeFilter" onchange="loadMedia()" class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB]">
                <option value="">Tümü</option>
                <option value="image">Görseller</option>
                <option value="application/pdf">PDF</option>
                <option value="application/vnd.openxmlformats-officedocument">Excel/Word</option>
                <option value="application/zip">Arşiv</option>
            </select>
        </div>
        <div>
            <span id="mediaCount" class="text-sm text-gray-400">0 dosya</span>
        </div>
    </div>

    <div id="mediaGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 p-4">
        <div class="col-span-full text-center py-12 text-gray-400">Yükleniyor...</div>
    </div>

    <div id="loadMore" class="hidden p-4 text-center border-t border-gray-100">
        <button onclick="loadMore()" class="px-6 py-2 text-sm text-[#02E0FB] hover:bg-[#02E0FB]/5 rounded-lg font-medium">Daha Fazla Yükle</button>
    </div>
</div>

{{-- Upload Modal --}}
<div id="uploadModal" class="hidden fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold">Dosya Yükle</h3>
            <button onclick="document.getElementById('uploadModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div id="dropZone" class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center hover:border-[#02E0FB] transition-colors cursor-pointer">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            <p class="text-sm text-gray-500">Dosyayı sürükleyin veya tıklayarak seçin</p>
            <p class="text-xs text-gray-400 mt-1">Maksimum 10MB (jpg, png, pdf, doc, xls, zip)</p>
            <input type="file" id="fileInput" class="hidden" accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.pdf,.doc,.docx,.xls,.xlsx,.zip" onchange="uploadFile(this)">
        </div>

        <div id="uploadProgress" class="hidden mt-4">
            <div class="w-full bg-gray-100 rounded-full h-2">
                <div id="progressBar" class="bg-[#02E0FB] h-2 rounded-full transition-all" style="width: 0%"></div>
            </div>
            <p id="uploadStatus" class="text-xs text-gray-400 mt-1">Yükleniyor...</p>
        </div>

        <div id="uploadResult" class="hidden mt-4 p-4 bg-green-50 rounded-xl text-sm text-green-700"></div>
    </div>
</div>

{{-- Preview Modal --}}
<div id="previewModal" class="hidden fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-auto p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 id="previewTitle" class="text-lg font-semibold truncate"></h3>
            <button onclick="document.getElementById('previewModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="previewContent" class="text-center"></div>
        <div id="previewInfo" class="mt-4 text-sm text-gray-500"></div>
        <button onclick="deleteMedia(currentPreviewId)" class="mt-4 px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg">Sil</button>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentPage = 1;
let currentPreviewId = null;
const PER_PAGE = 24;

document.addEventListener('DOMContentLoaded', loadMedia);

document.getElementById('dropZone').addEventListener('dragover', (e) => {
    e.preventDefault();
    document.getElementById('dropZone').classList.add('border-[#02E0FB]', 'bg-[#02E0FB]/5');
});

document.getElementById('dropZone').addEventListener('dragleave', () => {
    document.getElementById('dropZone').classList.remove('border-[#02E0FB]', 'bg-[#02E0FB]/5');
});

document.getElementById('dropZone').addEventListener('drop', (e) => {
    e.preventDefault();
    document.getElementById('dropZone').classList.remove('border-[#02E0FB]', 'bg-[#02E0FB]/5');
    const files = e.dataTransfer.files;
    if (files.length) document.getElementById('fileInput').files = files;
});

document.getElementById('dropZone').addEventListener('click', () => document.getElementById('fileInput').click());

function loadMedia() {
    currentPage = 1;
    const mime = document.getElementById('mimeFilter').value;
    fetch(`{{ route('admin.media.list') }}?per_page=${PER_PAGE}&mime=${mime}`)
        .then(r => r.json())
        .then(res => {
            document.getElementById('mediaCount').textContent = res.total + ' dosya';
            renderMedia(res.data);
            if (res.pages > 1) document.getElementById('loadMore').classList.remove('hidden');
            else document.getElementById('loadMore').classList.add('hidden');
        });
}

function loadMore() {
    currentPage++;
    const mime = document.getElementById('mimeFilter').value;
    fetch(`{{ route('admin.media.list') }}?per_page=${PER_PAGE}&mime=${mime}&page=${currentPage}`)
        .then(r => r.json())
        .then(res => {
            renderMedia(res.data, true);
            if (currentPage >= res.pages) document.getElementById('loadMore').classList.add('hidden');
        });
}

function renderMedia(items, append = false) {
    const grid = document.getElementById('mediaGrid');
    if (!append) grid.innerHTML = '';

    items.forEach(item => {
        const isImage = item.mime?.startsWith('image');
        const url = '/storage/' + item.path;
        const size = item.size ? (item.size / 1024).toFixed(1) + ' KB' : '—';

        const el = document.createElement('div');
        el.className = 'group relative rounded-xl border border-gray-100 overflow-hidden hover:shadow-md transition-all cursor-pointer';
        el.innerHTML = `
            <div class="aspect-square bg-gray-50 flex items-center justify-center" onclick="previewMedia(${item.id}, '${url}', '${isImage ? 'image' : 'file'}", '${item.filename.replace(/'/g, "\\'")}', '${size}', '${item.mime || '—'}')">
                ${isImage
                    ? `<img src="${url}" alt="${item.filename}" class="w-full h-full object-cover">`
                    : `<div class="text-center"><svg class="w-10 h-10 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg><p class="text-xs text-gray-400 mt-1 truncate px-2">${item.filename.split('.').pop()}</p></div>`
                }
            </div>
            <div class="p-2">
                <p class="text-xs font-medium text-gray-700 truncate">${item.filename}</p>
                <p class="text-xs text-gray-400">${size}</p>
            </div>
            <button onclick="event.stopPropagation(); deleteMedia(${item.id})" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 p-1.5 bg-white/90 rounded-lg text-red-400 hover:text-red-600 shadow-sm transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>`;
        grid.appendChild(el);
    });

    if (!items.length && !append) {
        grid.innerHTML = '<div class="col-span-full text-center py-12 text-gray-400">Henüz dosya yüklenmemiş.</div>';
    }
}

function uploadFile(input) {
    const file = input.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file);
    formData.append('_token', '{{ csrf_token() }}');

    document.getElementById('uploadProgress').classList.remove('hidden');
    document.getElementById('uploadResult').classList.add('hidden');

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '{{ route('admin.media.upload') }}');
    xhr.setRequestHeader('Accept', 'application/json');

    xhr.upload.onprogress = (e) => {
        if (!e.lengthComputable) return;
        const pct = Math.round((e.loaded * 100) / e.total);
        document.getElementById('progressBar').style.width = pct + '%';
        document.getElementById('uploadStatus').textContent = '%' + pct + ' yüklendi...';
    };

    xhr.onload = () => {
        document.getElementById('uploadProgress').classList.add('hidden');
        if (xhr.status >= 200 && xhr.status < 300) {
            const res = JSON.parse(xhr.responseText);
            document.getElementById('uploadResult').classList.remove('hidden');
            document.getElementById('uploadResult').innerHTML = '✅ ' + res.message;
            document.getElementById('progressBar').style.width = '0%';
            document.getElementById('fileInput').value = '';
            loadMedia();
            setTimeout(() => document.getElementById('uploadResult').classList.add('hidden'), 3000);
        } else {
            const res = JSON.parse(xhr.responseText);
            document.getElementById('uploadStatus').textContent = 'Hata: ' + (res.message || 'Yükleme başarısız');
        }
    };

    xhr.onerror = () => {
        document.getElementById('uploadProgress').classList.add('hidden');
        document.getElementById('uploadStatus').textContent = 'Yükleme başarısız';
    };

    xhr.send(formData);
}

function previewMedia(id, url, type, filename, size, mime) {
    currentPreviewId = id;
    document.getElementById('previewTitle').textContent = filename;
    document.getElementById('previewInfo').innerHTML = `<p>Boyut: ${size}</p><p>Tür: ${mime}</p>`;
    document.getElementById('previewContent').innerHTML = type === 'image'
        ? `<img src="${url}" class="max-h-96 mx-auto rounded-lg">`
        : `<a href="${url}" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-[#02E0FB] rounded-xl text-sm font-semibold">Dosyayı Aç</a>`;
    document.getElementById('previewModal').classList.remove('hidden');
}

function deleteMedia(id) {
    if (!confirm('Bu dosyayı silmek istediğinize emin misiniz?')) return;
    fetch('/admin/media/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) { loadMedia(); document.getElementById('previewModal').classList.add('hidden'); }
        else alert(res.message);
    });
}

function toast(type, msg) {
    const el = document.createElement('div');
    el.className = 'fixed top-4 right-4 z-50 px-5 py-3 rounded-xl shadow-lg text-sm font-medium transition-all ' + (type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200');
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 300); }, 3000);
}
</script>
@endpush
