@extends('layouts.app')
@section('title', 'Masraf Yönetimi')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#02E0FB]">Dashboard</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-800 font-medium">Masraf Yönetimi</span>
@endsection

@section('page_header')
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Masraf Yönetimi</h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Personel masraf taleplerini ve fiş/belge yüklemelerini yönetin.</p>
    </div>
    @can('expense.request')
    <button onclick="openCreateExpenseModal()"
        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-xl transition-all shadow-sm w-full sm:w-auto">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span class="hidden sm:inline">Yeni Masraf Talebi</span>
        <span class="sm:hidden">Masraf Talep</span>
    </button>
    @endcan
@endsection

@section('content')

{{-- KPI --}}
@php
$kpiCards = [
    ['id'=>'exp-pending','label'=>'Bekleyen','icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z','bg'=>'bg-yellow-50','text'=>'text-yellow-600'],
    ['id'=>'exp-approved','label'=>'Onaylanan','icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z','bg'=>'bg-green-50','text'=>'text-green-600'],
    ['id'=>'exp-rejected','label'=>'Reddedilen','icon'=>'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z','bg'=>'bg-red-50','text'=>'text-red-600'],
    ['id'=>'exp-paid','label'=>'Ödenen','icon'=>'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z','bg'=>'bg-blue-50','text'=>'text-blue-600'],
    ['id'=>'exp-avg','label'=>'Ortalama Masraf','icon'=>'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z','bg'=>'bg-cyan-50','text'=>'text-cyan-600'],
];
@endphp
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-5">
    @foreach($kpiCards as $k)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-3 sm:p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl {{ $k['bg'] }} flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 {{ $k['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $k['icon'] }}"/>
            </svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs text-gray-500 truncate">{{ $k['label'] }}</p>
            <p class="text-base sm:text-lg font-bold text-gray-900" id="{{ $k['id'] }}">—</p>
        </div>
    </div>
    @endforeach
</div>

{{-- Filtreler --}}
<div class="bg-white rounded-xl border border-gray-100 p-4 mb-4 shadow-sm">
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Personel</label>
            <select id="expPersonel" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#02E0FB]">
                <option value="">Tümü</option>
                @foreach($personels as $p)
                    <option value="{{ $p->id }}">{{ $p->first_name }} {{ $p->last_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Kategori</label>
            <select id="expCategory" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#02E0FB]">
                <option value="">Tümü</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Durum</label>
            <select id="expStatus" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#02E0FB]">
                <option value="">Tümü</option>
                <option value="pending">Bekleyen</option>
                <option value="approved">Onaylanan</option>
                <option value="rejected">Reddedilen</option>
                <option value="paid">Ödenen</option>
                <option value="cancelled">İptal</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Tarih</label>
            <input type="date" id="expDateFrom" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#02E0FB]">
        </div>
        <div class="flex items-end">
            <button onclick="loadExpenses()" class="w-full px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">Filtrele</button>
        </div>
    </div>
</div>

{{-- Tablo --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Personel</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Kategori</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Tutar</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Açıklama</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Tarih</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Durum</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">İşlem</th>
                </tr>
            </thead>
            <tbody id="expTableBody" class="divide-y divide-gray-50">
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Yükleniyor...</td></tr>
            </tbody>
        </table>
    </div>
    <div id="expPagination" class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
        <div class="text-xs text-gray-500" id="expTableInfo">—</div>
        <div class="flex gap-1" id="expPageButtons"></div>
    </div>
</div>

{{-- Global Modal (Create) --}}
<div id="globalModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto p-5 sm:p-6 relative">
        <button onclick="document.getElementById('globalModal').classList.add('hidden')" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <h3 id="modalTitle" class="text-lg font-semibold text-gray-800 mb-4">—</h3>
        <div id="modalBody"></div>
        <div id="modalFooter" class="flex justify-end gap-3 mt-5 pt-4 border-t border-gray-100"></div>
    </div>
</div>

{{-- Kategori Ekle Modal --}}
<div id="categoryModal" class="hidden fixed inset-0 z-[120] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeCategoryModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm z-10 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Yeni Masraf Kategorisi</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori Adı <span class="text-red-500">*</span></label>
                <input type="text" id="categoryName" placeholder="Örn: Yemek"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Limit (TRY)</label>
                <input type="number" id="categoryLimit" placeholder="Opsiyonel"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" id="categoryReceipt" class="w-4 h-4 rounded border-gray-300">
                Fiş/Fatura zorunlu olsun
            </label>
        </div>
        <div class="flex justify-end gap-3 mt-5">
            <button onclick="closeCategoryModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
            <button onclick="storeCategory()" class="px-4 py-2 text-sm text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-lg font-medium">Kaydet</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const EXP_URLS = {
    list:         '{{ route("admin.expense.list") }}',
    create:       '{{ route("admin.expense.create") }}',
    store:        '{{ route("admin.expense.store") }}',
    approve:      id => `/admin/expenses/requests/${id}/approve`,
    reject:       id => `/admin/expenses/requests/${id}/reject`,
    paid:         id => `/admin/expenses/requests/${id}/paid`,
    destroy:      id => `/admin/expenses/requests/${id}`,
    attachment:   (id, idx) => `/admin/expenses/requests/${id}/attachments/${idx}`,
    storeCat:     '{{ route("admin.expense.categories.store") }}',
};

const statusMap = {
    pending:   { label: 'Bekliyor',    bg: 'bg-yellow-100', text: 'text-yellow-700' },
    approved:  { label: 'Onaylandı',   bg: 'bg-green-100',  text: 'text-green-700' },
    rejected:  { label: 'Reddedildi',  bg: 'bg-red-100',    text: 'text-red-700' },
    paid:      { label: 'Ödendi',      bg: 'bg-blue-100',   text: 'text-blue-700' },
    cancelled: { label: 'İptal',       bg: 'bg-gray-100',   text: 'text-gray-600' },
};

let CURRENT_PAGE = 1;
let expPersonelList = [];
let expPersonelSearchInit = false;

document.addEventListener('DOMContentLoaded', () => {
    loadExpenses();
    loadExpKPIs();
});

function loadExpKPIs() {
    ['pending','approved','rejected','paid'].forEach(s => {
        axios.get(EXP_URLS.list, { params: { status: s, per_page: 1 } })
            .then(r => { const el = document.getElementById(`exp-${s}`); if (el) el.textContent = r.data.total; })
            .catch(() => { const el = document.getElementById(`exp-${s}`); if (el) el.textContent = '—'; });
    });
    axios.get(EXP_URLS.list, { params: { per_page: 1 } })
        .then(r => {
            if (r.data.meta?.avg_amount) {
                const el = document.getElementById('exp-avg');
                if (el) el.textContent = new Intl.NumberFormat('tr-TR', { style:'currency', currency:'TRY', maximumFractionDigits:0 }).format(r.data.meta.avg_amount);
            }
        }).catch(() => {});
}

function loadExpenses(page) {
    if (page) CURRENT_PAGE = page;
    axios.get(EXP_URLS.list, { params: {
        page: CURRENT_PAGE,
        personel_id:  document.getElementById('expPersonel').value,
        category_id:  document.getElementById('expCategory').value,
        status:       document.getElementById('expStatus').value,
        date_from:    document.getElementById('expDateFrom').value,
        per_page: 15,
    }}).then(res => renderExpTable(res.data)).catch(() => {
        document.getElementById('expTableBody').innerHTML = '<tr><td colspan="7" class="px-4 py-8 text-center text-red-400 text-sm">Veri yüklenemedi.</td></tr>';
    });
}

function renderExpTable(data) {
    const tbody = document.getElementById('expTableBody');
    if (!data.data.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Kayıt bulunamadı</td></tr>`;
        document.getElementById('expTableInfo').textContent = '—';
        document.getElementById('expPageButtons').innerHTML = '';
        return;
    }
    tbody.innerHTML = data.data.map(e => {
        const sc = statusMap[e.status] || { label: e.status, bg: 'bg-gray-100', text: 'text-gray-600' };
        const amount = new Intl.NumberFormat('tr-TR', { style:'currency', currency: e.currency||'TRY' }).format(e.amount);
        const date   = e.expense_date ? new Date(e.expense_date).toLocaleDateString('tr-TR') : '—';
        const initials = (e.personel?.first_name?.[0]||'') + (e.personel?.last_name?.[0]||'');
        const attachFiles = Array.isArray(e.attachments) ? e.attachments : [];
        const hasAttach = attachFiles.length > 0;
        return `
        <tr class="hover:bg-gray-50 transition-colors ${e.exceeds_limit ? 'bg-orange-50/30' : ''}">
            <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-[#02E0FB]/15 text-[#02E0FB] text-xs font-bold flex items-center justify-center shrink-0">${initials || '?'}</div>
                    <span class="font-medium text-gray-800 truncate">${e.personel?.first_name||''} ${e.personel?.last_name||''}</span>
                </div>
            </td>
            <td class="px-4 py-3 text-gray-600 text-sm hidden sm:table-cell">${e.category?.name || '—'}</td>
            <td class="px-4 py-3 text-right font-bold ${e.exceeds_limit ? 'text-orange-600' : 'text-gray-800'} whitespace-nowrap">
                ${amount} ${e.exceeds_limit ? '<span class="text-xs" title="Limit aşımı">⚠</span>' : ''}
            </td>
            <td class="px-4 py-3 text-gray-600 text-sm max-w-[180px] truncate hidden md:table-cell" title="${(e.description||'').replace(/"/g,'&quot;')}">${e.description || '—'}</td>
            <td class="px-4 py-3 text-gray-500 text-sm hidden lg:table-cell">${date}</td>
            <td class="px-4 py-3 text-center">
                <div class="flex flex-col items-center gap-0.5">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium ${sc.bg} ${sc.text}">${sc.label}</span>
                    ${hasAttach ? `<span onclick="event.stopPropagation(); viewAttachments(${e.id})" class="text-[10px] text-[#02E0FB] cursor-pointer hover:underline">📎 ${attachFiles.length} dosya</span>` : ''}
                </div>
            </td>
            <td class="px-4 py-3">
                <div class="flex items-center justify-end gap-1">
                    ${e.status === 'pending' ? `
                    @can('expense.approve')
                    <button onclick="approveExpense(${e.id})" class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg" title="Onayla">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </button>
                    <button onclick="rejectExpense(${e.id})" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Reddet">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    @endcan
                    <button onclick="confirmDelete(EXP_URLS.destroy(${e.id}), loadExpenses)" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg" title="Sil">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>` : ''}
                    ${e.status === 'approved' ? `
                    @can('expense.manage')
                    <button onclick="markPaid(${e.id})" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg" title="Ödendi İşaretle">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </button>
                    @endcan` : ''}
                </div>
            </td>
        </tr>`;
    }).join('');
    document.getElementById('expTableInfo').textContent = `${data.total} kayıttan ${data.data.length} gösteriliyor`;
    renderPagination(data);
}

function renderPagination(data) {
    const container = document.getElementById('expPageButtons');
    if (!data.pages || data.pages <= 1) { container.innerHTML = ''; return; }
    let html = '';
    for (let p = 1; p <= data.pages; p++) {
        html += `<button onclick="loadExpenses(${p})" class="px-2.5 py-1 text-xs rounded-lg ${p === CURRENT_PAGE ? 'bg-[#02E0FB] text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}">${p}</button>`;
    }
    container.innerHTML = html;
}

function openCreateExpenseModal() {
    axios.get(EXP_URLS.create).then(res => {
        document.getElementById('modalTitle').textContent = 'Yeni Masraf Talebi';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modalFooter').innerHTML = `
            <button onclick="document.getElementById('globalModal').classList.add('hidden')" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
            <button onclick="submitExpenseForm()" class="px-4 py-2 text-sm text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-lg font-medium">Talep Oluştur</button>`;
        document.getElementById('globalModal').classList.remove('hidden');
        expPersonelList = res.data.personels || [];
        initExpPersonelSearch();
    }).catch(() => toast('error', 'Form yüklenemedi.'));
}

function initExpPersonelSearch() {
    const input = document.getElementById('expPersonelSearch');
    const hidden = document.getElementById('expPersonelId');
    const dropdown = document.getElementById('expPersonelDropdown');
    if (!input) return;
    hidden.value = '';
    input.value = '';

    function render(filter) {
        const q = (filter || '').toLowerCase();
        const filtered = q ? expPersonelList.filter(p => p.name.toLowerCase().includes(q)) : expPersonelList;
        dropdown.innerHTML = !filtered.length
            ? '<div class="px-3 py-2 text-sm text-gray-400">Eşleşen personel bulunamadı</div>'
            : filtered.map(p => `<div class="px-3 py-2 text-sm text-gray-700 hover:bg-[#02E0FB]/10 cursor-pointer border-b border-gray-50 last:border-0" data-id="${p.id}">${p.name}</div>`).join('');
        dropdown.classList.remove('hidden');
    }

    input.addEventListener('input', () => render(input.value));
    input.addEventListener('focus', () => render(input.value));

    dropdown.addEventListener('click', e => {
        const item = e.target.closest('[data-id]');
        if (!item) return;
        hidden.value = item.dataset.id;
        input.value = item.textContent;
        dropdown.classList.add('hidden');
    });

    if (!expPersonelSearchInit) {
        document.addEventListener('click', e => {
            const wrap = document.getElementById('expPersonelSelectWrap');
            if (wrap && !wrap.contains(e.target)) {
                const dd = document.getElementById('expPersonelDropdown');
                if (dd) dd.classList.add('hidden');
            }
        });
        expPersonelSearchInit = true;
    }

    render('');
}

function submitExpenseForm() {
    const form = document.getElementById('expenseForm');
    if (!form) return;
    const formData = new FormData(form);
    axios.post(EXP_URLS.store, formData).then(res => {
        document.getElementById('globalModal').classList.add('hidden');
        if (res.data.exceeds_limit) {
            toast('warning', res.data.message);
        } else {
            toast('success', res.data.message);
        }
        loadExpenses();
        loadExpKPIs();
    }).catch(err => {
        const msg = err.response?.data?.message || err.response?.data?.error || 'Talep oluşturulamadı.';
        if (err.response?.data?.errors) {
            const first = Object.values(err.response.data.errors).flat()[0];
            toast('error', first);
        } else {
            toast('error', msg);
        }
    });
}

function approveExpense(id) {
    Swal.fire({ title: 'Masrafı Onayla', text: 'Bu masraf talebini onaylıyor musunuz?', icon: 'question',
        showCancelButton: true, confirmButtonColor: '#02E0FB', cancelButtonColor: '#6B7280',
        confirmButtonText: 'Onayla', cancelButtonText: 'İptal'
    }).then(r => {
        if (r.isConfirmed) axios.post(EXP_URLS.approve(id)).then(res => { toast('success', res.data.message); loadExpenses(); loadExpKPIs(); }).catch(err => toast('error', err.response?.data?.message || 'Hata'));
    });
}

function rejectExpense(id) {
    Swal.fire({ title: 'Masrafı Reddet', input: 'textarea', inputPlaceholder: 'Ret gerekçesi...',
        showCancelButton: true, confirmButtonColor: '#FA6001', confirmButtonText: 'Reddet', cancelButtonText: 'İptal',
        inputValidator: v => !v && 'Gerekçe zorunludur!'
    }).then(r => {
        if (r.isConfirmed) axios.post(EXP_URLS.reject(id), { reason: r.value }).then(res => { toast('success', res.data.message); loadExpenses(); loadExpKPIs(); }).catch(err => toast('error', err.response?.data?.message || 'Hata'));
    });
}

function markPaid(id) {
    Swal.fire({ title: 'Ödendi Olarak İşaretle?', icon: 'question',
        showCancelButton: true, confirmButtonColor: '#02E0FB', confirmButtonText: 'Evet, Ödendi', cancelButtonText: 'İptal'
    }).then(r => {
        if (r.isConfirmed) axios.post(EXP_URLS.paid(id)).then(res => { toast('success', res.data.message); loadExpenses(); loadExpKPIs(); }).catch(err => toast('error', err.response?.data?.message || 'Hata'));
    });
}

function viewAttachments(expenseId) {
    const m = document.getElementById('globalModal');
    document.getElementById('modalTitle').textContent = 'Yüklenen Belgeler';
    document.getElementById('modalBody').innerHTML = '<div class="text-center text-gray-400 py-4">Yükleniyor...</div>';
    document.getElementById('modalFooter').innerHTML = `
        <button onclick="document.getElementById('globalModal').classList.add('hidden')" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">Kapat</button>`;
    m.classList.remove('hidden');

    axios.get(EXP_URLS.list, { params: { per_page: 50 } }).then(r => {
        const exp = r.data.data.find(e => e.id === expenseId);
        if (!exp || !Array.isArray(exp.attachments) || !exp.attachments.length) {
            document.getElementById('modalBody').innerHTML = '<div class="text-center text-gray-400 py-4">Belge bulunamadı.</div>';
            return;
        }
        document.getElementById('modalBody').innerHTML = `<div class="space-y-2">${exp.attachments.map((f, i) => {
            const ext = f.split('.').pop().toLowerCase();
            const isImage = ['jpg','jpeg','png','webp','gif'].includes(ext);
            const url = EXP_URLS.attachment(expenseId, i);
            const name = f.split('/').pop();
            return `<div class="flex items-center gap-3 px-3 py-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                <span class="text-base">${isImage ? '🖼️' : '📄'}</span>
                <span class="flex-1 text-sm text-gray-700 truncate">${name}</span>
                <a href="${url}" target="_blank" class="shrink-0 px-3 py-1 text-xs font-medium text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-lg transition-colors">Görüntüle</a>
            </div>`;
        }).join('')}</div>`;
    }).catch(() => {
        document.getElementById('modalBody').innerHTML = '<div class="text-center text-red-400 py-4">Belgeler yüklenemedi.</div>';
    });
}

function openCreateCategoryModal() {
    document.getElementById('categoryName').value = '';
    document.getElementById('categoryLimit').value = '';
    document.getElementById('categoryReceipt').checked = false;
    document.getElementById('categoryModal').classList.remove('hidden');
    document.getElementById('categoryName').focus();
}

function closeCategoryModal() {
    document.getElementById('categoryModal').classList.add('hidden');
}

function storeCategory() {
    const name = document.getElementById('categoryName').value.trim();
    if (!name) { toast('warning', 'Kategori adı zorunludur.'); return; }
    axios.post(EXP_URLS.storeCat, {
        name: name,
        limit_per_item: document.getElementById('categoryLimit').value || null,
        requires_receipt: document.getElementById('categoryReceipt').checked,
    }).then(res => {
        closeCategoryModal();
        toast('success', res.data.message || 'Kategori oluşturuldu.');
        const opt = document.createElement('option');
        opt.value = res.data.data.id;
        opt.textContent = res.data.data.name;
        const sel = document.getElementById('expCategorySelect');
        if (sel) { sel.appendChild(opt); sel.value = res.data.data.id; }
        const filter = document.getElementById('expCategory');
        if (filter) { const fo = document.createElement('option'); fo.value = res.data.data.id; fo.textContent = res.data.data.name; filter.appendChild(fo); }
    }).catch(err => {
        const msg = err.response?.data?.message || err.response?.data?.error || 'Kaydedilemedi.';
        toast('error', msg);
    });
}
</script>
@endpush
