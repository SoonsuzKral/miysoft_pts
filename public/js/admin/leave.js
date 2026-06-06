const LEAVE_URLS = {
    list: '/admin/leave/list',
    create: '/admin/leave/requests/create',
    store: '/admin/leave/requests',
    edit: id => `/admin/leave/requests/${id}/edit`,
    update: id => `/admin/leave/requests/${id}`,
    destroy: id => `/admin/leave/requests/${id}`,
    approve: id => `/admin/leave/requests/${id}/approve`,
    reject: id => `/admin/leave/requests/${id}/reject`,
    cancel: id => `/admin/leave/requests/${id}/cancel`,
    export: '/admin/leave/export/excel',
    exportPdf: '/admin/leave/export/pdf',
};

const LEAVE_TYPES_URLS = {
    list: '/admin/leave/types',
    create: '/admin/leave/types/create',
    store: '/admin/leave/types',
    edit: id => `/admin/leave/types/${id}/edit`,
    update: id => `/admin/leave/types/${id}`,
    destroy: id => `/admin/leave/types/${id}`,
};

const BALANCE_URLS = {
    list: '/admin/leave/balances',
    recalculate: '/admin/leave/balances/recalculate',
};

document.addEventListener('DOMContentLoaded', () => {
    const page = document.body.dataset.page;
    if (page === 'leaves') { loadLeaveRequests(); loadKpiCounts(); setupLeaveListeners(); }
    if (page === 'leave-types') loadLeaveTypes();
    if (page === 'leave-balances') loadBalances();
});

// ─── Shared ───────────────────────────────────────────────────────────────────

function esc(text) {
    if (!text) return '';
    const d = document.createElement('div');
    d.textContent = text;
    return d.innerHTML;
}

function toast(type, msg) {
    const c = type === 'success' ? 'bg-emerald-500' : type === 'warning' ? 'bg-amber-500' : 'bg-red-500';
    const el = document.createElement('div');
    el.className = `fixed top-5 right-5 z-[999] ${c} text-white px-5 py-3 rounded-xl shadow-2xl text-sm font-medium animate-slide-in max-w-sm`;
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(() => { el.style.opacity = '0'; el.style.transition = 'opacity .3s'; setTimeout(() => el.remove(), 400); }, 3000);
}

function confirmDelete(url, cb) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: 'Bu işlem geri alınamaz.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Sil',
        cancelButtonText: 'İptal',
    }).then(result => {
        if (result.isConfirmed) {
            axios.delete(url).then(r => { toast('success', r.data.message || 'Silindi'); if (cb) cb(); })
                .catch(e => toast('error', e.response?.data?.message || 'Silme başarısız'));
        }
    });
}

// ─── Leave Requests ──────────────────────────────────────────────────────────

function setupLeaveListeners() {
    const fs = document.getElementById('filterSearch');
    const fd = document.getElementById('filterPersonel');
    const fl = document.getElementById('filterLeaveType');
    const fst = document.getElementById('filterStatus');
    const ff = document.getElementById('filterDateFrom');
    const ft = document.getElementById('filterDateTo');
    if (fs) { let t; fs.addEventListener('input', () => { clearTimeout(t); t = setTimeout(() => loadLeaveRequests(), 300); }); }
    if (fd) fd.addEventListener('change', () => loadLeaveRequests());
    if (fl) fl.addEventListener('change', () => loadLeaveRequests());
    if (fst) fst.addEventListener('change', () => loadLeaveRequests());
    if (ff) ff.addEventListener('change', () => loadLeaveRequests());
    if (ft) ft.addEventListener('change', () => loadLeaveRequests());
}

const STATUS_CONFIG = {
    pending: { label: 'Bekliyor', bg: 'bg-amber-50 text-amber-700', dot: '#f59e0b' },
    approved: { label: 'Onaylandı', bg: 'bg-emerald-50 text-emerald-700', dot: '#10b981' },
    rejected: { label: 'Reddedildi', bg: 'bg-red-50 text-red-700', dot: '#ef4444' },
    cancelled: { label: 'İptal', bg: 'bg-gray-100 text-gray-600', dot: '#6b7280' },
};

let CURRENT_LEAVE_PAGE = 1;

function loadLeaveRequests(page) {
    if (page) CURRENT_LEAVE_PAGE = page;
    const params = {
        page: CURRENT_LEAVE_PAGE,
        search: document.getElementById('filterSearch')?.value || '',
        personel_id: document.getElementById('filterPersonel')?.value || '',
        leave_type_id: document.getElementById('filterLeaveType')?.value || '',
        status: document.getElementById('filterStatus')?.value || '',
        date_from: document.getElementById('filterDateFrom')?.value || '',
        date_to: document.getElementById('filterDateTo')?.value || '',
        per_page: 15,
    };
    axios.get(LEAVE_URLS.list, { params })
        .then(res => { renderLeaveTable(res.data); renderLeavePagination(res.data); })
        .catch(() => toast('error', 'Tablo yüklenemedi'));
}

function renderLeaveTable(data) {
    const tbody = document.getElementById('leaveTableBody');
    if (!data.data?.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-12 text-center text-gray-400 text-sm">Kayıt bulunamadı</td></tr>`;
        document.getElementById('leaveTableInfo').textContent = '0 kayıt';
        return;
    }
    tbody.innerHTML = data.data.map(r => {
        const sc = STATUS_CONFIG[r.status] || { label: r.status, bg: 'bg-gray-100 text-gray-600', dot: '#6b7280' };
        const isPending = r.status === 'pending';
        const p = r.personel || {};
        const initials = (p.first_name?.[0] || '') + (p.last_name?.[0] || '');
        return `<tr class="hover:bg-gray-50/80 transition-colors group ${isPending ? 'bg-amber-50/20' : ''}">
            <td data-label="Personel" class="px-4 py-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#02E0FB] to-cyan-500 text-white font-bold text-xs flex items-center justify-center shadow-sm">${esc(initials)}</div>
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">${esc(p.first_name)} ${esc(p.last_name)}</p>
                        <p class="text-[11px] text-gray-400">${esc(r.leave_type?.name || '')}</p>
                    </div>
                </div>
            </td>
            <td data-label="Tarih" class="px-4 py-3 text-sm text-gray-600">
                <span>${new Date(r.start_date).toLocaleDateString('tr-TR')}</span>
                <span class="text-gray-300 mx-1">→</span>
                <span>${new Date(r.end_date).toLocaleDateString('tr-TR')}</span>
            </td>
            <td data-label="Gün" class="px-4 py-3 text-center">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-800 text-xs font-bold">${r.total_days ?? '—'}</span>
            </td>
            <td data-label="Durum" class="px-4 py-3">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold ${sc.bg}">
                    <span class="w-1.5 h-1.5 rounded-full" style="background:${sc.dot}"></span>
                    ${sc.label}
                </span>
            </td>
            <td data-label="Onaylayan" class="px-4 py-3 text-sm text-gray-500">${r.approver ? esc(r.approver.name) : '—'}</td>
            <td data-label="İşlemler" class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-0.5 opacity-70 group-hover:opacity-100 transition-opacity">
                    ${isPending ? `
                    <button onclick="openEditLeaveModal(${r.id})" class="p-1.5 text-gray-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-all" title="Düzenle">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button onclick="openApproveModal(${r.id})" class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-all" title="Onayla">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </button>
                    <button onclick="openRejectModal(${r.id})" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Reddet">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    ` : ''}
                    ${r.status !== 'cancelled' ? `
                    <button onclick="cancelLeave(${r.id})" class="p-1.5 text-gray-400 hover:text-orange-500 hover:bg-orange-50 rounded-lg transition-all" title="İptal Et">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    </button>
                    ` : ''}
                </div>
            </td>
        </tr>`;
    }).join('');
    document.getElementById('leaveTableInfo').textContent = `${data.total} kayıttan ${Math.min(data.data.length, 15)} gösteriliyor`;
}

function renderLeavePagination(data) {
    const el = document.getElementById('leavePagination');
    if (!el) return;
    const tp = data.pages || 1;
    const page = CURRENT_LEAVE_PAGE;
    let h = '';
    if (page > 1) h += `<button onclick="loadLeaveRequests(${page-1})" class="px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors">‹</button>`;
    for (let i = Math.max(1, page-2); i <= Math.min(tp, page+2); i++) {
        h += i === page
            ? `<span class="px-2.5 py-1.5 text-xs bg-[#02E0FB] text-white rounded-lg font-semibold shadow-sm">${i}</span>`
            : `<button onclick="loadLeaveRequests(${i})" class="px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors">${i}</button>`;
    }
    if (page < tp) h += `<button onclick="loadLeaveRequests(${page+1})" class="px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors">›</button>`;
    el.innerHTML = h;
}

// ─── KPI Widgets ────────────────────────────────────────────────────────────

function loadKpiCounts() {
    ['pending', 'approved', 'rejected', 'cancelled'].forEach(s => {
        axios.get(LEAVE_URLS.list, { params: { status: s, per_page: 1 } })
            .then(res => {
                const el = document.getElementById(`kpi-${s}`);
                if (el) el.textContent = res.data.total ?? 0;
            })
            .catch(() => {
                const el = document.getElementById(`kpi-${s}`);
                if (el) el.textContent = '0';
            });
    });
}

// ─── Approval Modals ─────────────────────────────────────────────────────────

function openApproveModal(id) {
    document.getElementById('approvalModalTitle').textContent = 'İzni Onayla';
    document.getElementById('approvalModalContent').innerHTML = `
        <p class="text-sm text-gray-600 mb-4">Bu izin talebini onaylamak istiyor musunuz?</p>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Onay Notu <span class="text-gray-400">(isteğe bağlı)</span></label>
        <textarea id="approvalNote" rows="2" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#02E0FB] focus:ring-1 focus:ring-[#02E0FB]/20" placeholder="Onay notu..."></textarea>
    `;
    const btn = document.getElementById('approvalModalBtn');
    btn.textContent = 'Onayla';
    btn.className = 'px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-emerald-500 to-green-600 hover:from-green-500 hover:to-emerald-600 rounded-xl shadow-sm transition-all';
    btn.onclick = () => submitApproval(id, 'approve');
    document.getElementById('approvalModal').classList.remove('hidden');
}

function openRejectModal(id) {
    document.getElementById('approvalModalTitle').textContent = 'İzni Reddet';
    document.getElementById('approvalModalContent').innerHTML = `
        <p class="text-sm text-gray-600 mb-4">Red gerekçesini belirtiniz.</p>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Ret Gerekçesi <span class="text-red-500">*</span></label>
        <textarea id="approvalNote" rows="3" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-red-400 focus:ring-1 focus:ring-red-200" placeholder="Reddetme gerekçesini yazınız..."></textarea>
    `;
    const btn = document.getElementById('approvalModalBtn');
    btn.textContent = 'Reddet';
    btn.className = 'px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-500 rounded-xl shadow-sm transition-all';
    btn.onclick = () => submitApproval(id, 'reject');
    document.getElementById('approvalModal').classList.remove('hidden');
}

function submitApproval(id, action) {
    const note = document.getElementById('approvalNote')?.value;
    if (action === 'reject' && !note?.trim()) {
        toast('warning', 'Ret gerekçesi zorunludur.');
        return;
    }
    const url = action === 'approve' ? LEAVE_URLS.approve(id) : LEAVE_URLS.reject(id);
    const payload = action === 'approve' ? { note } : { reason: note };
    axios.post(url, payload).then(res => {
        closeApprovalModal();
        toast(res.data.success ? 'success' : 'error', res.data.message);
        loadLeaveRequests(CURRENT_LEAVE_PAGE);
        loadKpiCounts();
    }).catch(e => toast('error', e.response?.data?.message || 'İşlem başarısız'));
}

function closeApprovalModal() {
    document.getElementById('approvalModal').classList.add('hidden');
}

function cancelLeave(id) {
    Swal.fire({
        title: 'İzni İptal Et',
        input: 'textarea',
        inputPlaceholder: 'İptal gerekçesi (isteğe bağlı)...',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'İptal Et',
        cancelButtonText: 'Vazgeç',
    }).then(result => {
        if (result.isConfirmed) {
            axios.post(LEAVE_URLS.cancel(id), { reason: result.value }).then(res => {
                toast(res.data.success ? 'success' : 'error', res.data.message);
                loadLeaveRequests(CURRENT_LEAVE_PAGE);
                loadKpiCounts();
            }).catch(e => toast('error', e.response?.data?.message || 'İptal başarısız'));
        }
    });
}

// ─── Create / Edit Leave Modal ────────────────────────────────────────────────

function openCreateLeaveModal() {
    axios.get(LEAVE_URLS.create).then(res => {
        document.getElementById('modalTitle').textContent = 'Yeni İzin Talebi';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modalFooter').innerHTML = `
            <button onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">İptal</button>
            <button onclick="submitLeaveForm()" class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-[#02E0FB] to-cyan-500 hover:from-cyan-500 hover:to-[#02E0FB] rounded-xl shadow-sm transition-all">Talep Oluştur</button>`;
        openModal();
    }).catch(() => toast('error', 'Form yüklenemedi'));
}

function openEditLeaveModal(id) {
    axios.get(LEAVE_URLS.edit(id)).then(res => {
        document.getElementById('modalTitle').textContent = 'İzin Talebini Düzenle';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modalFooter').innerHTML = `
            <button onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">İptal</button>
            <button onclick="submitLeaveForm('${LEAVE_URLS.update(id)}','PUT')" class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-[#02E0FB] to-cyan-500 hover:from-cyan-500 hover:to-[#02E0FB] rounded-xl shadow-sm transition-all">Güncelle</button>`;
        openModal();
    }).catch(() => toast('error', 'Form yüklenemedi'));
}

function submitLeaveForm(url = LEAVE_URLS.store, method = 'POST') {
    const form = document.getElementById('leaveRequestForm');
    if (!form) { toast('error', 'Form bulunamadı'); return; }
    const data = Object.fromEntries(new FormData(form).entries());
    axios({ method, url, data, headers: { 'Content-Type': 'application/json' } })
        .then(res => {
            closeModal();
            toast(res.data.auto_approved ? 'success' : 'info', res.data.message);
            loadLeaveRequests(CURRENT_LEAVE_PAGE);
            loadKpiCounts();
        })
        .catch(err => {
            const msg = err.response?.data?.message || err.response?.data?.errors || 'Kaydetme başarısız';
            toast('error', typeof msg === 'string' ? msg : Object.values(msg).flat().join(', '));
        });
}

function closeModal() {
    const m = document.getElementById('globalModal');
    if (m) m.classList.add('hidden');
}

function openModal() {
    const m = document.getElementById('globalModal');
    if (m) { m.classList.remove('hidden'); m.querySelector('.relative')?.classList.add('animate-scale-in'); }
}

// ─── Export ──────────────────────────────────────────────────────────────────

function exportLeaves(format) {
    const params = new URLSearchParams({
        personel_id: document.getElementById('filterPersonel')?.value || '',
        leave_type_id: document.getElementById('filterLeaveType')?.value || '',
        status: document.getElementById('filterStatus')?.value || '',
        date_from: document.getElementById('filterDateFrom')?.value || '',
        date_to: document.getElementById('filterDateTo')?.value || '',
    });
    const url = format === 'pdf' ? LEAVE_URLS.exportPdf : LEAVE_URLS.export;
    window.open(url + '?' + params.toString(), '_blank');
}

// ─── Leave Types ─────────────────────────────────────────────────────────────

function loadLeaveTypes() {
    axios.get(LEAVE_TYPES_URLS.list, { params: { per_page: 50 } }).then(res => {
        const tbody = document.getElementById('leaveTypesBody');
        if (!res.data.data?.length) {
            tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-12 text-center text-gray-400 text-sm">Henüz izin türü tanımlanmamış</td></tr>`;
            return;
        }
        tbody.innerHTML = res.data.data.map(t => `
            <tr class="hover:bg-gray-50/80 transition-colors group">
                <td data-label="İzin Türü" class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full ${t.paid ? 'bg-emerald-400' : 'bg-gray-300'}"></span>
                        <span class="font-semibold text-gray-800 text-sm">${esc(t.name)}</span>
                    </div>
                </td>
                <td data-label="Ücretli" class="px-4 py-3 text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ${t.paid ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600'}">${t.paid ? 'Evet' : 'Hayır'}</span>
                </td>
                <td data-label="Max Gün" class="px-4 py-3 text-center font-semibold text-gray-800 text-sm">${t.max_annual_days || '—'}</td>
                <td data-label="Onay" class="px-4 py-3 text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ${t.requires_approval ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700'}">${t.requires_approval ? 'Gerekli' : 'Otomatik'}</span>
                </td>
                <td data-label="Kullanım" class="px-4 py-3 text-center text-sm font-medium text-gray-700">${t.leave_requests_count ?? 0}</td>
                <td data-label="Durum" class="px-4 py-3 text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ${t.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'}">${t.is_active ? 'Aktif' : 'Pasif'}</span>
                </td>
                <td data-label="İşlemler" class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-0.5 opacity-70 group-hover:opacity-100 transition-opacity">
                        <button onclick="openEditTypeModal(${t.id})" class="p-1.5 text-gray-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-all" title="Düzenle">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button onclick="confirmDelete(LEAVE_TYPES_URLS.destroy(${t.id}), loadLeaveTypes)" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all" title="Sil">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </td>
            </tr>`).join('');
    });
}

function openCreateTypeModal() {
    axios.get(LEAVE_TYPES_URLS.create).then(res => {
        document.getElementById('modalTitle').textContent = 'Yeni İzin Türü';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modalFooter').innerHTML = `
            <button onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">İptal</button>
            <button onclick="submitLeaveTypeForm()" class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-[#02E0FB] to-cyan-500 hover:from-cyan-500 hover:to-[#02E0FB] rounded-xl shadow-sm transition-all">Kaydet</button>`;
        openModal();
    }).catch(() => toast('error', 'Form yüklenemedi'));
}

function openEditTypeModal(id) {
    axios.get(LEAVE_TYPES_URLS.edit(id)).then(res => {
        document.getElementById('modalTitle').textContent = 'İzin Türünü Düzenle';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modalFooter').innerHTML = `
            <button onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">İptal</button>
            <button onclick="submitLeaveTypeForm('${LEAVE_TYPES_URLS.update(id)}','PUT')" class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-[#02E0FB] to-cyan-500 hover:from-cyan-500 hover:to-[#02E0FB] rounded-xl shadow-sm transition-all">Güncelle</button>`;
        openModal();
    }).catch(() => toast('error', 'Form yüklenemedi'));
}

function submitLeaveTypeForm(url = LEAVE_TYPES_URLS.store, method = 'POST') {
    const form = document.getElementById('leaveTypeForm');
    if (!form) { toast('error', 'Form bulunamadı'); return; }
    const data = Object.fromEntries(new FormData(form).entries());
    data.paid = form.querySelector('[name="paid"]')?.checked ? 1 : 0;
    data.requires_approval = form.querySelector('[name="requires_approval"]')?.checked ? 1 : 0;
    data.is_active = form.querySelector('[name="is_active"]')?.checked ? 1 : 0;
    axios({ method, url, data }).then(res => {
        closeModal();
        toast('success', res.data.message);
        loadLeaveTypes();
    }).catch(e => toast('error', e.response?.data?.message || 'Kaydetme başarısız'));
}

// ─── Leave Balances ──────────────────────────────────────────────────────────

function loadBalances(page = 1) {
    const year = document.getElementById('balanceYear')?.value || new Date().getFullYear();
    axios.get(BALANCE_URLS.list, { params: { year, page, per_page: 25 } }).then(res => renderBalances(res.data, page));
}

function renderBalances(data, page) {
    const tbody = document.getElementById('balancesBody');
    if (!data.data?.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-12 text-center text-gray-400 text-sm">Bakiye kaydı bulunamadı</td></tr>`;
        document.getElementById('balancesInfo').textContent = '0 kayıt';
        return;
    }
    tbody.innerHTML = data.data.map(b => {
        const usedDays = Number(b.used_days) || 0;
        const pct = b.entitled_days > 0 ? Math.round((usedDays / b.entitled_days) * 100) : 0;
        const barColor = pct >= 90 ? 'bg-red-400' : pct >= 70 ? 'bg-amber-400' : 'bg-[#02E0FB]';
        const remaining = parseFloat(b.remaining_days) || 0;
        const remainColor = remaining <= 0 ? 'text-red-600 font-bold' : remaining <= 3 ? 'text-amber-600 font-semibold' : 'text-emerald-700 font-semibold';
        const p = b.personel || {};
        const initials = (p.first_name?.[0] || '') + (p.last_name?.[0] || '');
        return `<tr class="hover:bg-gray-50/80 transition-colors">
            <td data-label="Personel" class="px-4 py-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#02E0FB] to-cyan-500 text-white font-bold text-xs flex items-center justify-center shadow-sm">${esc(initials)}</div>
                    <span class="font-semibold text-gray-800 text-sm">${esc(p.first_name)} ${esc(p.last_name)}</span>
                </div>
            </td>
            <td data-label="İzin Türü" class="px-4 py-3 text-sm text-gray-600">${esc(b.leave_type?.name || '—')}</td>
            <td data-label="Hak" class="px-4 py-3 text-center font-semibold text-gray-800 text-sm">${b.entitled_days}</td>
            <td data-label="Kullanılan" class="px-4 py-3 text-center text-sm text-gray-600">${b.used_days}</td>
            <td data-label="Kalan" class="px-4 py-3 text-center text-sm ${remainColor}">${b.remaining_days}</td>
            <td data-label="Oran" class="px-4 py-3">
                <div class="flex items-center gap-2">
                    <div class="flex-1 bg-gray-100 rounded-full h-2.5 overflow-hidden">
                        <div class="h-2.5 rounded-full ${barColor} transition-all duration-500" style="width:${Math.min(pct, 100)}%"></div>
                    </div>
                    <span class="text-xs font-medium text-gray-500 w-8 text-right">${pct}%</span>
                </div>
            </td>
        </tr>`;
    }).join('');
    document.getElementById('balancesInfo').textContent = `${data.total} kayıt`;
}

function recalculateAll() {
    Swal.fire({
        title: 'Bakiyeleri Yeniden Hesapla',
        text: 'Tüm personellerin izin bakiyeleri güncel onaylı izinlere göre hesaplanacak.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#FA6001',
        confirmButtonText: 'Hesapla',
        cancelButtonText: 'İptal',
    }).then(result => {
        if (result.isConfirmed) {
            const year = document.getElementById('balanceYear')?.value || new Date().getFullYear();
            axios.post(BALANCE_URLS.recalculate, { year }).then(res => {
                toast(res.data.success ? 'success' : 'error', res.data.message);
                loadBalances();
            }).catch(e => toast('error', e.response?.data?.message || 'Hesaplama başarısız'));
        }
    });
}
