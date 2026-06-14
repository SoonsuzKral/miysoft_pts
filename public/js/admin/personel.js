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

    const btn = document.querySelector('#modalFooter button:last-child');
    if (btn) { btn.disabled = true; btn.textContent = 'Kaydediliyor...'; btn.classList.add('opacity-60', 'cursor-not-allowed'); }

    const docEntries = document.querySelectorAll('.doc-entry');
    const hasDocs = Array.from(docEntries).some(e => e.querySelector('.doc-file').files[0]);

    const doRequest = (config) => {
        axios(config)
            .then(res => { closeModal(); toast('success', res.data.message); loadPersonelTable(CURRENT_PAGE); })
            .catch(err => {
                if (btn) { btn.disabled = false; btn.textContent = method === 'POST' && url !== PERSONEL_CONFIG.store ? 'Güncelle' : 'Kaydet'; btn.classList.remove('opacity-60', 'cursor-not-allowed'); }
                const msg = err.response?.data?.message || err.response?.data?.errors || 'Kaydetme başarısız';
                toast('error', typeof msg === 'string' ? msg : Object.values(msg).flat().join(', '));
            });
    };

    if (hasDocs) {
        const formData = new FormData(form);
        docEntries.forEach((entry, i) => {
            const file = entry.querySelector('.doc-file').files[0];
            const name = entry.querySelector('.doc-name').value.trim();
            if (file && name) {
                formData.append(`documents[${i}][file]`, file);
                formData.append(`documents[${i}][type]`, name);
                const isIndefinite = entry.querySelector('.doc-indefinite')?.checked;
                if (!isIndefinite) {
                    const expiry = entry.querySelector('.doc-expiry').value;
                    if (expiry) formData.append(`documents[${i}][expiry_at]`, expiry);
                }
            }
        });
        doRequest({ method, url, data: formData });
    } else {
        const data = Object.fromEntries(new FormData(form).entries());
        doRequest({ method, url, data, headers: { 'Content-Type': 'application/json' } });
    }
}

// ─── Belge Yönetimi (Personel Oluşturma/Düzenleme Formu) ─────────
let docIndex = 0;

function addDocumentEntry() {
    const container = document.getElementById('documentEntries');
    const emptyMsg = document.getElementById('docEmptyMessage');
    if (emptyMsg) emptyMsg.remove();

    const index = docIndex++;
    const html = `
    <div class="doc-entry bg-white rounded-xl border border-gray-200 p-4 shadow-sm hover:shadow-md transition-shadow" id="docEntry-${index}">
        <div class="flex items-start justify-between gap-3 mb-3">
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Belge Adı</label>
                <input type="text" class="doc-name w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB] focus:ring-1 focus:ring-[#02E0FB]" placeholder="Örn: Banka Sözleşmesi, İş Sözleşmesi, SGK Bildirgesi ...">
            </div>
            <button type="button" onclick="removeDocumentEntry(${index})"
                class="p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all shrink-0" title="Kaldır">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex-1">
                <div class="doc-dropzone relative border-2 border-dashed border-gray-200 rounded-xl p-4 hover:border-[#02E0FB] hover:bg-[#02E0FB]/5 transition-all cursor-pointer text-center"
                     onclick="document.getElementById('docFile-${index}').click()"
                     ondragover="event.preventDefault(); this.classList.add('border-[#02E0FB]', 'bg-[#02E0FB]/5')"
                     ondrop="handleDocDrop(event, ${index})">
                    <input type="file" id="docFile-${index}" class="doc-file hidden" accept=".pdf,.jpg,.jpeg,.png,.docx,.doc,.xlsx,.xls,.csv"
                        onchange="previewDocFile(this, ${index})">
                    <div id="docUploadPlaceholder-${index}">
                        <svg class="w-8 h-8 text-gray-300 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-xs text-gray-400">Dosya seçmek için tıklayın</p>
                    </div>
                    <div id="docFileInfo-${index}" class="hidden flex items-center gap-2">
                        <svg class="w-6 h-6 text-[#02E0FB] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div class="text-left">
                            <p class="text-sm font-medium text-gray-700" id="docFileName-${index}"></p>
                            <p class="text-xs text-gray-400" id="docFileSize-${index}"></p>
                        </div>
                        <button type="button" onclick="clearDocFile(${index})" class="ml-auto p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition-colors" title="Dosyayı değiştir">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-1">PDF, JPG, PNG, DOCX, XLSX — Max 10MB</p>
            </div>
            <div class="shrink-0" style="min-width:210px">
                <label class="block text-xs font-medium text-gray-600 mb-1">Son Geçerlilik</label>
                <div class="flex items-center gap-1.5">
                    <input type="date" class="doc-expiry flex-1 min-w-0 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB] focus:ring-1 focus:ring-[#02E0FB]">
                    <label class="flex items-center gap-1 text-xs text-gray-500 whitespace-nowrap cursor-pointer bg-gray-50 px-2 py-2 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors shrink-0">
                        <input type="checkbox" class="doc-indefinite rounded border-gray-300 text-[#02E0FB] focus:ring-[#02E0FB]" onchange="toggleIndefinite(${index})">
                        Süresiz
                    </label>
                </div>
            </div>
        </div>
    </div>`;
    container.insertAdjacentHTML('beforeend', html);
}

function previewDocFile(input, index) {
    if (!input.files.length) return;
    const file = input.files[0];

    const allowed = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel',
        'text/csv'];
    if (!allowed.includes(file.type) && !file.name.match(/\.(pdf|jpg|jpeg|png|docx|doc|xlsx|xls|csv)$/i)) {
        toast('warning', 'Geçersiz dosya türü. PDF, JPG, PNG, DOCX, XLSX dosyaları kabul edilir.');
        input.value = '';
        return;
    }

    document.getElementById(`docUploadPlaceholder-${index}`).classList.add('hidden');
    const info = document.getElementById(`docFileInfo-${index}`);
    info.classList.remove('hidden');
    document.getElementById(`docFileName-${index}`).textContent = file.name;
    document.getElementById(`docFileSize-${index}`).textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
    document.getElementById(`docFile-${index}`).closest('.doc-dropzone').classList.remove('border-gray-200');
    document.getElementById(`docFile-${index}`).closest('.doc-dropzone').classList.add('border-[#02E0FB]', 'bg-[#02E0FB]/5');
}

function handleDocDrop(e, index) {
    e.preventDefault();
    const input = document.getElementById(`docFile-${index}`);
    input.files = e.dataTransfer.files;
    previewDocFile(input, index);
}

function clearDocFile(index) {
    const input = document.getElementById(`docFile-${index}`);
    input.value = '';
    document.getElementById(`docUploadPlaceholder-${index}`).classList.remove('hidden');
    document.getElementById(`docFileInfo-${index}`).classList.add('hidden');
    const dz = document.getElementById(`docFile-${index}`).closest('.doc-dropzone');
    dz.classList.remove('border-[#02E0FB]', 'bg-[#02E0FB]/5');
    dz.classList.add('border-gray-200');
}

function toggleIndefinite(index) {
    const entry = document.getElementById(`docEntry-${index}`);
    if (!entry) return;
    const dateInput = entry.querySelector('.doc-expiry');
    const cb = entry.querySelector('.doc-indefinite');
    if (cb.checked) {
        dateInput.disabled = true;
        dateInput.value = '';
    } else {
        dateInput.disabled = false;
    }
}

function removeDocumentEntry(index) {
    const el = document.getElementById(`docEntry-${index}`);
    if (el) {
        el.remove();
        const container = document.getElementById('documentEntries');
        if (!container.querySelector('.doc-entry')) {
            container.innerHTML = `<p class="text-xs text-gray-400 text-center py-3 bg-gray-50 rounded-lg border border-dashed border-gray-200" id="docEmptyMessage">Henüz belge eklenmedi. "Belge Ekle" butonuna tıklayarak belge yükleyebilirsiniz.</p>`;
        }
    }
}

function openCardView(id) {
    _docsLoaded[id] = false;
    axios.get(PERSONEL_CONFIG.card(id)).then(res => {
        const area = document.getElementById('personelCardArea');
        area.innerHTML = res.data.html;
        area.classList.remove('hidden');
        area.scrollIntoView({ behavior: 'smooth', block: 'start' });
        if (window.Alpine) {
            const root = area.querySelector('[x-data]');
            if (root) Alpine.initTree(root);
        }
    }).catch(() => toast('error', 'Kart yüklenemedi'));
}

function getDocIcon(ext) {
    const map = { pdf: '📄', jpg: '🖼️', jpeg: '🖼️', png: '🖼️', docx: '📝', doc: '📝', xlsx: '📊', xls: '📊', csv: '📊' };
    return map[ext] || '📎';
}

var _docsLoaded = {};

function loadBelgeler() {
    const root = document.querySelector('[x-data*="tab"]');
    if (!root) return;
    const personelId = root.dataset.personelId;
    if (!personelId) return;
    if (_docsLoaded[personelId]) return;
    _docsLoaded[personelId] = true;

    const container = document.getElementById('belgelerContainer');
    if (!container) return;
    container.innerHTML = '<p class="text-gray-400 text-sm text-center py-8">Yükleniyor...</p>';

    axios.get('/admin/personel/' + personelId + '/documents').then(res => {
        const docs = res.data.data;
        if (!docs || !docs.length) {
            container.innerHTML = '<p class="text-gray-400 text-sm text-center py-8">Henüz belge eklenmemiş</p>';
            return;
        }

        let html = '<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">';
        docs.forEach(doc => {
            const icon = getDocIcon(doc.ext);
            html += '<div class="flex items-center gap-3 p-3 bg-gray-50/50 rounded-xl border border-gray-50 hover:border-gray-200 transition-all group">' +
                '<div class="w-9 h-9 rounded-xl bg-[#02E0FB]/10 flex items-center justify-center shrink-0">' +
                '<span class="text-base">' + icon + '</span></div>' +
                '<div class="min-w-0 flex-1">' +
                '<p class="text-sm font-semibold text-gray-800 truncate">' + esc(doc.type) + '</p>' +
                '<p class="text-xs text-gray-400">' + esc(doc.original_name || doc.file_path.split('/').pop()) +
                ' <span class="' + doc.display_class + '">· ' + doc.display_text + '</span></p></div>' +
                '<div class="opacity-0 group-hover:opacity-100 transition-opacity flex gap-1">' +
                (doc.view_url ? '<a href="' + doc.view_url + '" target="_blank" class="p-1.5 text-gray-400 hover:text-[#02E0FB] hover:bg-blue-50 rounded-lg transition-all" title="Görüntüle">' +
                '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>' +
                '</a>' : '') +
                '<a href="' + doc.download_url + '" class="p-1.5 text-gray-400 hover:text-[#02E0FB] hover:bg-blue-50 rounded-lg transition-all" title="İndir">' +
                '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>' +
                '</a></div></div>';
        });
        html += '</div>';
        container.innerHTML = html;
    }).catch(() => {
        container.innerHTML = '<p class="text-red-400 text-sm text-center py-8">Belgeler yüklenemedi</p>';
        _docsLoaded[personelId] = false;
    });
}

function deleteDocument(docId) {
    confirmDelete(`/admin/personel/documents/${docId}`, () => {
        const el = document.getElementById(`doc-${docId}`);
        if (el) el.remove();
    });
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
