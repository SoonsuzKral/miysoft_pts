@extends('layouts.app')
@section('title', 'Bildirimler')

@section('page_header')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Bildirimler</h1>
        <p class="text-sm text-gray-500 mt-1">Tüm sistem bildirimlerini görüntüleyin ve yönetin.</p>
    </div>
    <div class="flex gap-2">
        <button onclick="markAllRead()" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 border border-gray-200 text-gray-600 font-semibold rounded-xl hover:bg-gray-50 transition-all text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Tümünü Okundu İşaretle
        </button>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Filtreler --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5">
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
            <label class="text-sm font-medium text-gray-700">Filtrele:</label>
            <select id="filterType" class="w-full sm:w-auto px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent" onchange="loadNotifications()">
                <option value="">Tümü</option>
                <option value="info">Bilgi</option>
                <option value="success">Başarılı</option>
                <option value="warning">Uyarı</option>
                <option value="error">Hata</option>
            </select>
            <select id="filterRead" class="w-full sm:w-auto px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#02E0FB] focus:border-transparent" onchange="loadNotifications()">
                <option value="">Tümü</option>
                <option value="unread">Okunmamış</option>
                <option value="read">Okunmuş</option>
            </select>
        </div>
    </div>

    {{-- Bildirim Listesi --}}
    <div id="notifContainer" class="space-y-2">
        <div class="text-center py-12 text-gray-400">Yükleniyor...</div>
    </div>

    {{-- Sayfalama --}}
    <div id="paginationWrap" class="flex flex-col sm:flex-row items-center justify-between gap-2 pt-2 hidden">
        <div class="text-sm text-gray-500" id="pageInfo"></div>
        <div class="flex gap-2">
            <button onclick="changePage(-1)" id="prevBtn" class="px-4 py-2 text-sm border border-gray-200 rounded-xl hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all" disabled>Önceki</button>
            <button onclick="changePage(1)" id="nextBtn" class="px-4 py-2 text-sm border border-gray-200 rounded-xl hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all" disabled>Sonraki</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentPage = 1;
const PER_PAGE = 20;

document.addEventListener('DOMContentLoaded', loadNotifications);

function loadNotifications(page) {
    if (page) currentPage = page;
    const params = new URLSearchParams({
        page: currentPage,
        per_page: PER_PAGE,
        type: document.getElementById('filterType')?.value || '',
        read: document.getElementById('filterRead')?.value || '',
    });

    fetch(`{{ route("admin.notifications.index") }}?${params}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        const container = document.getElementById('notifContainer');
        if (!res.data.length) {
            container.innerHTML = `
                <div class="text-center py-16 bg-white rounded-2xl border border-gray-100 shadow-sm">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <p class="text-gray-400 font-medium">Bildirim bulunamadı</p>
                    <p class="text-gray-300 text-sm mt-1">Filtreleri değiştirerek tekrar deneyin</p>
                </div>`;
            document.getElementById('paginationWrap').classList.add('hidden');
            return;
        }

        container.innerHTML = res.data.map(n => {
            const ico = n.icon || '🔔';
            const colors = {
                info:    { bg: 'bg-blue-50',    border: 'border-l-blue-400',  text: 'text-blue-600' },
                success: { bg: 'bg-green-50',   border: 'border-l-green-400', text: 'text-green-600' },
                warning: { bg: 'bg-yellow-50',  border: 'border-l-yellow-400',text: 'text-yellow-600' },
                error:   { bg: 'bg-red-50',     border: 'border-l-red-400',   text: 'text-red-600' },
            };
            const c = colors[n.type] || colors.info;

            return `
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow ${n.is_read ? 'opacity-70' : ''}">
                <div class="flex items-start gap-3 p-4 sm:p-5 ${!n.is_read ? 'border-l-4 ' + c.border : ''}">
                    <div class="w-10 h-10 rounded-xl ${c.bg} flex items-center justify-center text-lg shrink-0">${ico}</div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-sm font-semibold text-gray-800 ${n.is_read ? '' : ''}">${n.title}</p>
                                <p class="text-sm text-gray-500 mt-0.5 line-clamp-2">${n.message}</p>
                                ${n.subtitle ? `<p class="text-xs text-gray-400 mt-1">${n.subtitle}</p>` : ''}
                            </div>
                            <div class="flex gap-1 shrink-0">
                                ${n.action_url ? `<a href="${n.action_url}" class="p-1.5 text-gray-400 hover:text-[#02E0FB] hover:bg-cyan-50 rounded-lg" title="${n.action_label || 'İncele'}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>` : ''}
                                ${!n.is_read ? `<button onclick="markAsRead(${n.id})" class="p-1.5 text-gray-400 hover:text-green-500 hover:bg-green-50 rounded-lg" title="Okundu işaretle">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </button>` : ''}
                                <button onclick="deleteNotif(${n.id})" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg" title="Sil">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">${n.time_full || n.time}</p>
                    </div>
                </div>
            </div>`;
        }).join('');

        const pw = document.getElementById('paginationWrap');
        const pi = document.getElementById('pageInfo');
        const prv = document.getElementById('prevBtn');
        const nxt = document.getElementById('nextBtn');

        if (res.total > PER_PAGE) {
            pw.classList.remove('hidden');
            const totalPages = Math.ceil(res.total / PER_PAGE);
            pi.textContent = `Sayfa ${currentPage} / ${totalPages} (${res.total} bildirim)`;
            prv.disabled = currentPage <= 1;
            nxt.disabled = currentPage >= totalPages;
        } else {
            pw.classList.add('hidden');
        }
    })
    .catch(() => {
        document.getElementById('notifContainer').innerHTML = `<div class="text-center py-12 text-red-400">Bildirimler yüklenirken hata oluştu.</div>`;
    });
}

function changePage(dir) {
    loadNotifications(currentPage + dir);
}

function markAsRead(id) {
    fetch('{{ route("admin.notifications.markRead") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ id })
    }).then(() => loadNotifications());
}

function markAllRead() {
    fetch('{{ route("admin.notifications.markRead") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    }).then(res => {
        if (res.ok) { toast('success', 'Tüm bildirimler okundu işaretlendi.'); loadNotifications(); }
    });
}

function deleteNotif(id) {
    if (!confirm('Bildirimi silmek istediğinize emin misiniz?')) return;
    fetch(`{{ url("admin/notifications") }}/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    }).then(res => {
        if (res.ok) { toast('success', 'Bildirim silindi.'); loadNotifications(); }
    });
}
</script>
@endpush
