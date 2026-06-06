@extends('layouts.app')
@section('title', 'Avans Yönetimi')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#02E0FB]">Dashboard</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-800 font-medium">Avans Yönetimi</span>
@endsection

@section('page_header')
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Avans Yönetimi</h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Personel avans taleplerini yönetin, onaylayın ve ödeme takibi yapın.</p>
    </div>
    @can('advance.request')
    <button onclick="openCreateAdvanceModal()"
        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-xl transition-all shadow-sm w-full sm:w-auto">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span class="hidden sm:inline">Yeni Avans Talebi</span>
        <span class="sm:hidden">Avans Talep</span>
    </button>
    @endcan
@endsection

@section('content')

{{-- KPI --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-5">
    @php
    $kpiCards = [
        ['id'=>'adv-pending','label'=>'Bekleyen','icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z','bg'=>'bg-yellow-50','text'=>'text-yellow-600'],
        ['id'=>'adv-approved','label'=>'Onaylanan','icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z','bg'=>'bg-green-50','text'=>'text-green-600'],
        ['id'=>'adv-rejected','label'=>'Reddedilen','icon'=>'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z','bg'=>'bg-red-50','text'=>'text-red-600'],
        ['id'=>'adv-repaid','label'=>'Ödenen','icon'=>'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z','bg'=>'bg-blue-50','text'=>'text-blue-600'],
        ['id'=>'adv-total-pending','label'=>'Bekleyen Toplam','icon'=>'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z','bg'=>'bg-purple-50','text'=>'text-purple-600'],
        ['id'=>'adv-avg','label'=>'Ortalama','icon'=>'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z','bg'=>'bg-cyan-50','text'=>'text-cyan-600'],
    ];
    @endphp
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
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Personel</label>
            <select id="advPersonel" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#02E0FB]">
                <option value="">Tümü</option>
                @foreach($personels as $p)
                    <option value="{{ $p->id }}">{{ $p->first_name }} {{ $p->last_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Durum</label>
            <select id="advStatus" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#02E0FB]">
                <option value="">Tümü</option>
                <option value="pending">Bekleyen</option>
                <option value="approved">Onaylanan</option>
                <option value="rejected">Reddedilen</option>
                <option value="repaid">Geri Ödenen</option>
                <option value="cancelled">İptal</option>
            </select>
        </div>
        <div class="flex items-end">
            <button onclick="loadAdvances()" class="w-full px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">Filtrele</button>
        </div>
        <div class="flex items-end">
            <button onclick="exportAdvances()" class="w-full px-4 py-2 text-sm bg-[#02E0FB]/10 hover:bg-[#02E0FB]/20 text-[#02E0FB] rounded-lg transition-colors">
                <span class="hidden sm:inline">Excel Aktar</span><span class="sm:hidden">Aktar</span>
            </button>
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
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Tutar</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Gerekçe</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Tarih</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Durum</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">İşlem</th>
                </tr>
            </thead>
            <tbody id="advTableBody" class="divide-y divide-gray-50">
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Yükleniyor...</td></tr>
            </tbody>
        </table>
    </div>
    <div id="advPagination" class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
        <div class="text-xs text-gray-500" id="advTableInfo">—</div>
        <div class="flex gap-1" id="advPageButtons"></div>
    </div>
</div>

{{-- Global Modal (Create/Approve) --}}
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

@endsection

@push('scripts')
<script>
const ADV_URLS = {
    list:    '{{ route("admin.advance.list") }}',
    create:  '{{ route("admin.advance.create") }}',
    store:   '{{ route("admin.advance.store") }}',
    approve: id => `/admin/advances/requests/${id}/approve`,
    reject:  id => `/admin/advances/requests/${id}/reject`,
    cancel:  id => `/admin/advances/requests/${id}/cancel`,
    repaid:  id => `/admin/advances/requests/${id}/repaid`,
    destroy: id => `/admin/advances/requests/${id}`,
};

const statusMap = {
    pending:   { label: 'Bekliyor',      bg: 'bg-yellow-100', text: 'text-yellow-700' },
    approved:  { label: 'Onaylandı',     bg: 'bg-green-100',  text: 'text-green-700' },
    rejected:  { label: 'Reddedildi',    bg: 'bg-red-100',    text: 'text-red-700' },
    repaid:    { label: 'Ödendi',        bg: 'bg-blue-100',   text: 'text-blue-700' },
    cancelled: { label: 'İptal',         bg: 'bg-gray-100',   text: 'text-gray-600' },
};

let CURRENT_PAGE = 1;

document.addEventListener('DOMContentLoaded', () => {
    loadAdvances();
    loadAdvKPIs();
});

function loadAdvKPIs() {
    ['pending','approved','rejected','repaid'].forEach(s => {
        axios.get(ADV_URLS.list, { params: { status: s, per_page: 1 } })
            .then(r => { const el = document.getElementById(`adv-${s}`); if (el) el.textContent = r.data.total; })
            .catch(() => { const el = document.getElementById(`adv-${s}`); if (el) el.textContent = '—'; });
    });
    axios.get(ADV_URLS.list, { params: { status: 'pending', per_page: 1 } })
        .then(r => {
            const totals = r.data.meta?.totals;
            if (totals) {
                const el = document.getElementById('adv-total-pending');
                if (el) el.textContent = new Intl.NumberFormat('tr-TR', { style:'currency', currency:'TRY', maximumFractionDigits:0 }).format(totals.amount || 0);
            }
        }).catch(() => {});
    axios.get(ADV_URLS.list, { params: { per_page: 1 } })
        .then(r => {
            const avg = r.data.meta?.avg_amount;
            if (avg !== undefined) {
                const el = document.getElementById('adv-avg');
                if (el) el.textContent = new Intl.NumberFormat('tr-TR', { style:'currency', currency:'TRY', maximumFractionDigits:0 }).format(avg);
            }
        }).catch(() => {});
}

function loadAdvances(page) {
    if (page) CURRENT_PAGE = page;
    const params = {
        page: CURRENT_PAGE,
        personel_id: document.getElementById('advPersonel').value,
        status: document.getElementById('advStatus').value,
        per_page: 15,
    };
    axios.get(ADV_URLS.list, { params })
        .then(res => renderAdvTable(res.data))
        .catch(() => {
            document.getElementById('advTableBody').innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-red-400 text-sm">Veri yüklenemedi.</td></tr>';
        });
}

function renderAdvTable(data) {
    const tbody = document.getElementById('advTableBody');
    if (!data.data.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Kayıt bulunamadı</td></tr>`;
        document.getElementById('advTableInfo').textContent = '—';
        document.getElementById('advPageButtons').innerHTML = '';
        return;
    }
    tbody.innerHTML = data.data.map(a => {
        const sc = statusMap[a.status] || { label: a.status, bg: 'bg-gray-100', text: 'text-gray-600' };
        const amount = new Intl.NumberFormat('tr-TR', { style:'currency', currency: a.currency||'TRY' }).format(a.amount);
        const date   = new Date(a.created_at).toLocaleDateString('tr-TR');
        const initials = (a.personel?.first_name?.[0]||'') + (a.personel?.last_name?.[0]||'');
        return `
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-[#02E0FB]/15 text-[#02E0FB] text-xs font-bold flex items-center justify-center shrink-0">${initials || '?'}</div>
                    <span class="font-medium text-gray-800 truncate">${a.personel?.first_name||''} ${a.personel?.last_name||''}</span>
                </div>
            </td>
            <td class="px-4 py-3 text-right font-bold text-gray-800 whitespace-nowrap">${amount}</td>
            <td class="px-4 py-3 text-gray-600 text-sm max-w-[200px] truncate hidden sm:table-cell" title="${(a.reason||'').replace(/"/g,'&quot;')}">${a.reason || '—'}</td>
            <td class="px-4 py-3 text-gray-500 text-sm hidden md:table-cell">${date}</td>
            <td class="px-4 py-3 text-center">
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium ${sc.bg} ${sc.text}">${sc.label}</span>
                ${a.approver ? `<div class="text-xs text-gray-400 mt-0.5">${a.approver.name}</div>` : ''}
            </td>
            <td class="px-4 py-3">
                <div class="flex items-center justify-end gap-1">
                    ${a.status === 'pending' ? `
                    @can('advance.approve')
                    <button onclick="approveAdvance(${a.id})" class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg" title="Onayla">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </button>
                    <button onclick="rejectAdvance(${a.id})" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Reddet">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    @endcan
                    <button onclick="confirmDelete(ADV_URLS.destroy(${a.id}), loadAdvances)" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg" title="Sil">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>` : ''}
                    ${a.status === 'approved' ? `
                    @can('advance.approve')
                    <button onclick="markRepaid(${a.id})" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg" title="Ödendi İşaretle">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </button>
                    @endcan` : ''}
                </div>
            </td>
        </tr>`;
    }).join('');
    document.getElementById('advTableInfo').textContent = `${data.total} kayıttan ${data.data.length} gösteriliyor`;
    renderPagination(data);
}

function renderPagination(data) {
    const container = document.getElementById('advPageButtons');
    if (!data.pages || data.pages <= 1) { container.innerHTML = ''; return; }
    let html = '';
    for (let p = 1; p <= data.pages; p++) {
        html += `<button onclick="loadAdvances(${p})" class="px-2.5 py-1 text-xs rounded-lg ${p === CURRENT_PAGE ? 'bg-[#02E0FB] text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}">${p}</button>`;
    }
    container.innerHTML = html;
}

let personelList = [];
let personelSearchInitialized = false;

function openCreateAdvanceModal() {
    axios.get(ADV_URLS.create).then(res => {
        document.getElementById('modalTitle').textContent = 'Yeni Avans Talebi';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modalFooter').innerHTML = `
            <button onclick="document.getElementById('globalModal').classList.add('hidden')" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
            <button onclick="submitAdvanceForm()" class="px-4 py-2 text-sm text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-lg font-medium">Talep Oluştur</button>`;
        document.getElementById('globalModal').classList.remove('hidden');
        personelList = res.data.personels || [];
        initPersonelSearch();
    }).catch(() => toast('error', 'Form yüklenemedi.'));
}

function initPersonelSearch() {
    const input = document.getElementById('personelSearchInput');
    const hidden = document.getElementById('personelIdInput');
    const dropdown = document.getElementById('personelDropdown');
    if (!input) return;

    // Değerleri sıfırla
    hidden.value = '';
    input.value = '';

    function render(filter) {
        const q = (filter || '').toLowerCase();
        const filtered = q ? personelList.filter(p => p.name.toLowerCase().includes(q)) : personelList;
        if (!filtered.length) {
            dropdown.innerHTML = '<div class="px-3 py-2 text-sm text-gray-400">Eşleşen personel bulunamadı</div>';
        } else {
            dropdown.innerHTML = filtered.map(p =>
                `<div class="px-3 py-2 text-sm text-gray-700 hover:bg-[#02E0FB]/10 cursor-pointer border-b border-gray-50 last:border-0" data-id="${p.id}">${p.name}</div>`
            ).join('');
        }
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

    if (!personelSearchInitialized) {
        document.addEventListener('click', e => {
            const wrap = document.getElementById('personelSelectWrap');
            if (wrap && !wrap.contains(e.target)) {
                const dd = document.getElementById('personelDropdown');
                if (dd) dd.classList.add('hidden');
            }
        });
        personelSearchInitialized = true;
    }

    render('');
}

function submitAdvanceForm() {
    const form = document.getElementById('advanceForm');
    if (!form) return;
    const data = Object.fromEntries(new FormData(form).entries());
    axios.post(ADV_URLS.store, data).then(res => {
        document.getElementById('globalModal').classList.add('hidden');
        toast('success', res.data.message);
        loadAdvances();
        loadAdvKPIs();
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

function approveAdvance(id) {
    const m = document.getElementById('globalModal');
    document.getElementById('modalTitle').textContent = 'Avans Onayı — Geri Ödeme Planı';
    document.getElementById('modalBody').innerHTML = `
        <div class="space-y-4">
            <p class="text-sm text-gray-500">Geri ödeme şeklini belirleyin.</p>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ödeme Şekli</label>
                <select id="repayMethod" onchange="toggleRepayPlan()" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
                    <option value="single">Tek Seferde</option>
                    <option value="installment">Taksitli (Maaştan Kesinti)</option>
                </select>
            </div>
            <div id="installmentCountWrap" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">Taksit Sayısı</label>
                <select id="installmentCount" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
                    ${[2,3,4,5,6,7,8,9,10,11,12].map(n => `<option value="${n}">${n} Ay</option>`).join('')}
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ödeme Başlangıç Tarihi</label>
                <input type="date" id="repayStartDate" value="${new Date().toISOString().slice(0,7)+'-01'}"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
            </div>
        </div>`;
    document.getElementById('modalFooter').innerHTML = `
        <button onclick="document.getElementById('globalModal').classList.add('hidden')" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
        <button onclick="submitApprove(${id})" class="px-4 py-2 text-sm text-white bg-green-600 hover:bg-green-700 rounded-lg font-medium">Onayla</button>`;
    m.classList.remove('hidden');
}

function toggleRepayPlan() {
    document.getElementById('installmentCountWrap').classList.toggle('hidden', document.getElementById('repayMethod').value === 'single');
}

function submitApprove(id) {
    const plan = {
        method: document.getElementById('repayMethod').value,
        start_date: document.getElementById('repayStartDate').value,
    };
    if (plan.method === 'installment') {
        plan.installments = parseInt(document.getElementById('installmentCount').value);
    }
    axios.post(ADV_URLS.approve(id), { repayment_plan: plan }).then(res => {
        document.getElementById('globalModal').classList.add('hidden');
        toast('success', res.data.message);
        loadAdvances();
        loadAdvKPIs();
    }).catch(err => {
        const msg = err.response?.data?.message || 'Onaylama başarısız.';
        toast('error', msg);
    });
}

function rejectAdvance(id) {
    Swal.fire({ title: 'Avansı Reddet', input: 'textarea', inputPlaceholder: 'Ret gerekçesi...',
        showCancelButton: true, confirmButtonColor: '#FA6001', cancelButtonColor: '#6B7280',
        confirmButtonText: 'Reddet', cancelButtonText: 'İptal', inputValidator: v => !v && 'Gerekçe zorunludur!'
    }).then(r => {
        if (r.isConfirmed) {
            axios.post(ADV_URLS.reject(id), { reason: r.value })
                .then(res => { toast('success', res.data.message); loadAdvances(); loadAdvKPIs(); })
                .catch(err => toast('error', err.response?.data?.message || 'Reddetme başarısız.'));
        }
    });
}

function markRepaid(id) {
    Swal.fire({ title: 'Ödendi Olarak İşaretle?', text: 'Bu avansın ödemesi tamamlandı mı?', icon: 'question',
        showCancelButton: true, confirmButtonColor: '#02E0FB', cancelButtonColor: '#6B7280',
        confirmButtonText: 'Evet, Ödendi', cancelButtonText: 'İptal'
    }).then(r => {
        if (r.isConfirmed) {
            axios.post(ADV_URLS.repaid(id))
                .then(res => { toast('success', res.data.message); loadAdvances(); loadAdvKPIs(); })
                .catch(err => toast('error', err.response?.data?.message || 'İşlem başarısız.'));
        }
    });
}
</script>
@endpush
