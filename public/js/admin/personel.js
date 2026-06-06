const PERSONEL_CONFIG = {
    list: '/admin/personel/list',
    create: '/admin/personel/create',
    store: '/admin/personel',
    edit: (id) => `/admin/personel/${id}/edit`,
    update: (id) => `/admin/personel/${id}`,
    show: (id) => `/admin/personel/${id}/show`,
    destroy: (id) => `/admin/personel/${id}`,
    card: (id) => `/admin/personel/${id}/card`,
    exportExcel: '/admin/personel/export/excel',
    exportPdf: (id) => `/admin/personel/${id}/export/pdf`,
    toggleActive: (id) => `/admin/personel/${id}/toggle-active`,
};

let CURRENT_PAGE = 1;

document.addEventListener('DOMContentLoaded', function() {
    loadPersonelTable(1);
    setupListeners();
});

function setupListeners() {
    const fs = document.getElementById('filterSearch');
    const fd = document.getElementById('filterDept');
    const fst = document.getElementById('filterStatus');
    if (fs) { let timer; fs.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(reloadTable, 300); }); }
    if (fd) fd.addEventListener('change', reloadTable);
    if (fst) fst.addEventListener('change', reloadTable);
    const bd = document.getElementById('modalBackdrop');
    const cl = document.getElementById('modalClose');
    if (bd) bd.addEventListener('click', closeModal);
    if (cl) cl.addEventListener('click', closeModal);
}

function loadPersonelTable(page = 1) {
    CURRENT_PAGE = page;
    const params = {
        page,
        search: document.getElementById('filterSearch')?.value || '',
        department_id: document.getElementById('filterDept')?.value || '',
        status: document.getElementById('filterStatus')?.value || '',
        per_page: 15,
    };
    axios.get(PERSONEL_CONFIG.list, { params })
        .then(res => { renderTable(res.data); renderPagination(res.data, page); })
        .catch(() => toast('error', 'Tablo yüklenemedi'));
}

function renderTable(data) {
    const tbody = document.getElementById('personelTableBody');
    if (!data.data || !data.data.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-12 text-center text-gray-400 text-sm">Kayıt bulunamadı</td></tr>`;
        return;
    }
    tbody.innerHTML = data.data.map(p => {
        const initials = (p.first_name?.[0] || '') + (p.last_name?.[0] || '');
        return `<tr class="hover:bg-gray-50/80 transition-colors group">
            <td data-label="Personel" class="px-4 py-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#02E0FB] to-cyan-500 text-white font-bold flex items-center justify-center text-sm shadow-sm">${initials}</div>
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">${esc(p.first_name)} ${esc(p.last_name)}</p>
                        <p class="text-[11px] text-gray-400">${p.email ? esc(p.email) : '—'}</p>
                    </div>
                </div>
            </td>
            <td data-label="Departman" class="px-4 py-3 text-sm text-gray-600">${p.department?.name ? esc(p.department.name) : '—'}</td>
            <td data-label="Pozisyon" class="px-4 py-3 text-sm text-gray-600">${p.position?.title ? esc(p.position.title) : '—'}</td>
            <td data-label="İşe Giriş" class="px-4 py-3 text-sm text-gray-500">${p.hire_date ? new Date(p.hire_date).toLocaleDateString('tr-TR') : '—'}</td>
            <td data-label="Durum" class="px-4 py-3">${statusBadge(p.status)}</td>
            <td data-label="İşlemler" class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-0.5 opacity-70 group-hover:opacity-100 transition-opacity">
                    <button onclick="openCardView(${p.id})" class="p-1.5 text-gray-400 hover:text-[#02E0FB] hover:bg-[#02E0FB]/10 rounded-lg transition-all" title="Detay">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                    <button onclick="openEditModal(${p.id})" class="p-1.5 text-gray-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-all" title="Düzenle">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button onclick="confirmDelete(PERSONEL_CONFIG.destroy(${p.id}), () => loadPersonelTable(CURRENT_PAGE))" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all" title="Sil">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
    document.getElementById('tableInfo').textContent = `${data.total} kayıttan ${Math.min(data.data.length, 15)} gösteriliyor`;
}

function statusBadge(status) {
    const m = {
        active: { label: 'Aktif', bg: 'bg-emerald-50 text-emerald-700' },
        terminated: { label: 'Ayrılmış', bg: 'bg-red-50 text-red-700' },
        on_leave: { label: 'İzinde', bg: 'bg-amber-50 text-amber-700' },
        suspended: { label: 'Askıda', bg: 'bg-gray-100 text-gray-600' },
    };
    const s = m[status] || { label: status, bg: 'bg-gray-100 text-gray-600' };
    return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold ${s.bg}">${dot(status)} ${s.label}</span>`;
}

function dot(status) {
    const c = { active: '#10b981', terminated: '#ef4444', on_leave: '#f59e0b', suspended: '#6b7280' };
    return `<span class="w-1.5 h-1.5 rounded-full inline-block" style="background:${c[status]||'#6b7280'}"></span>`;
}

function renderPagination(data, page) {
    const el = document.getElementById('tablePagination');
    if (!el) return;
    const tp = data.pages || 1;
    let h = '';
    if (page > 1) h += `<button onclick="loadPersonelTable(${page-1})" class="px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors">‹</button>`;
    for (let i = Math.max(1, page-2); i <= Math.min(tp, page+2); i++) {
        h += i === page
            ? `<span class="px-2.5 py-1.5 text-xs bg-[#02E0FB] text-white rounded-lg font-semibold shadow-sm">${i}</span>`
            : `<button onclick="loadPersonelTable(${i})" class="px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors">${i}</button>`;
    }
    if (page < tp) h += `<button onclick="loadPersonelTable(${page+1})" class="px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors">›</button>`;
    el.innerHTML = h;
}

function openCreateModal() {
    axios.get(PERSONEL_CONFIG.create).then(res => {
        document.getElementById('modalTitle').textContent = 'Yeni Personel Ekle';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modalFooter').innerHTML = `
            <button onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">İptal</button>
            <button onclick="submitPersonelForm()" class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-[#02E0FB] to-cyan-500 hover:from-cyan-500 hover:to-[#02E0FB] rounded-lg shadow-sm transition-all">Kaydet</button>`;
        openModal();
    }).catch(() => toast('error', 'Form yüklenemedi'));
}

function openEditModal(id) {
    axios.get(PERSONEL_CONFIG.edit(id)).then(res => {
        document.getElementById('modalTitle').textContent = 'Personel Düzenle';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modalFooter').innerHTML = `
            <button onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">İptal</button>
            <button onclick="submitPersonelForm('${PERSONEL_CONFIG.update(id)}','POST')" class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-[#02E0FB] to-cyan-500 hover:from-cyan-500 hover:to-[#02E0FB] rounded-lg shadow-sm transition-all">Güncelle</button>`;
        openModal();
    }).catch(() => toast('error', 'Form yüklenemedi'));
}

function submitPersonelForm(url = PERSONEL_CONFIG.store, method = 'POST') {
    const form = document.getElementById('personelForm');
    if (!form) { toast('error', 'Form bulunamadı'); return; }
    const data = Object.fromEntries(new FormData(form).entries());
    axios({ method, url, data, headers: { 'Content-Type': 'application/json' } })
        .then(res => { closeModal(); toast('success', res.data.message); loadPersonelTable(CURRENT_PAGE); })
        .catch(err => {
            const msg = err.response?.data?.message || err.response?.data?.errors || 'Kaydetme başarısız';
            toast('error', typeof msg === 'string' ? msg : Object.values(msg).flat().join(', '));
        });
}

function openCardView(id) {
    axios.get(PERSONEL_CONFIG.card(id)).then(res => {
        const area = document.getElementById('personelCardArea');
        area.innerHTML = res.data.html;
        area.classList.remove('hidden');
        area.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }).catch(() => toast('error', 'Kart yüklenemedi'));
}

function exportPersonel(type) {
    if (type === 'excel') { window.location.href = PERSONEL_CONFIG.exportExcel; }
}

function reloadTable() { loadPersonelTable(1); }

function openModal() {
    const m = document.getElementById('globalModal');
    if (m) { m.classList.remove('hidden'); m.querySelector('.relative').classList.add('animate-scale-in'); }
}

function closeModal() {
    const m = document.getElementById('globalModal');
    if (m) m.classList.add('hidden');
}

function confirmDelete(url, cb) {
    if (confirm('Bu işlem geri alınamaz. Silmek istediğinize emin misiniz?')) {
        axios.delete(url).then(r => { toast('success', r.data.message || 'Silindi'); if (cb) cb(); })
            .catch(e => toast('error', e.response?.data?.message || 'Silme başarısız'));
    }
}

function toast(type, msg) {
    const c = type === 'success' ? 'bg-emerald-500' : type === 'warning' ? 'bg-amber-500' : 'bg-red-500';
    const el = document.createElement('div');
    el.className = `fixed top-5 right-5 z-[999] ${c} text-white px-5 py-3 rounded-xl shadow-2xl text-sm font-medium animate-slide-in max-w-sm`;
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(() => { el.style.opacity = '0'; el.style.transition = 'opacity .3s'; setTimeout(() => el.remove(), 400); }, 3000);
}

function esc(text) {
    if (!text) return '';
    const d = document.createElement('div');
    d.textContent = text;
    return d.innerHTML;
}
