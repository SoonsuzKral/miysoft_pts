@extends('layouts.app')

@section('title', 'Özel Saat')
@section('pageTitle', 'Özel Saat Yönetimi')

@section('content')
<style>
.personel-search-item:hover { background: #f3f4f6; }
.personel-search-item.selected { background: #e0f7fa; }
</style>

<div id="specialHourApp" class="max-w-6xl mx-auto">
    {{-- Şifre Ekranı --}}
    <div id="passwordScreen" class="flex items-center justify-center min-h-[60vh]">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 w-full max-w-md">
            <div class="text-center mb-6">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-[#02E0FB] to-cyan-500 flex items-center justify-center text-3xl mb-4 shadow-sm">🔒</div>
                <h2 class="text-xl font-bold text-gray-900">Özel Saat Modülü</h2>
                <p class="text-sm text-gray-500 mt-1">Bu modül şifre korumalıdır</p>
            </div>
            @if(!$hasPassword)
            <div id="firstTimeSetup">
                <p class="text-sm text-amber-700 bg-amber-50 border border-amber-200 px-4 py-3 rounded-xl mb-4">🔐 İlk kullanım — lütfen bir şifre belirleyin.</p>
                <input type="password" id="newPassword" placeholder="Yeni şifre (en az 4 karakter)"
                    class="w-full px-4 py-3 text-sm border border-gray-200 rounded-xl mb-3 focus:outline-none focus:border-[#02E0FB] focus:ring-1 focus:ring-[#02E0FB]">
                <button onclick="setPassword()" class="w-full px-5 py-3 text-sm font-semibold text-white bg-gradient-to-r from-[#02E0FB] to-cyan-500 rounded-xl shadow-sm hover:shadow-md transition-all">Şifre Oluştur</button>
            </div>
            @endif
            <div id="passwordLogin" class="{{ $hasPassword ? '' : 'hidden' }}">
                <input type="password" id="loginPassword" placeholder="Şifre girin"
                    class="w-full px-4 py-3 text-sm border border-gray-200 rounded-xl mb-3 focus:outline-none focus:border-[#02E0FB] focus:ring-1 focus:ring-[#02E0FB]"
                    onkeydown="if(event.key==='Enter')verifyPassword()">
                <button onclick="verifyPassword()" class="w-full px-5 py-3 text-sm font-semibold text-white bg-gradient-to-r from-[#02E0FB] to-cyan-500 rounded-xl shadow-sm hover:shadow-md transition-all">Giriş</button>
            </div>
            <p id="passwordError" class="text-xs text-red-600 mt-3 text-center hidden"></p>
        </div>
    </div>

    {{-- Dashboard --}}
    <div id="dashboardScreen" class="hidden space-y-6">
        {{-- İstatistik --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Toplam Kayıt</p>
                <p class="text-3xl font-bold text-gray-900 mt-1" id="totalCount">0</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Giriş</p>
                <p class="text-3xl font-bold text-emerald-600 mt-1" id="inCount">0</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Çıkış</p>
                <p class="text-3xl font-bold text-rose-600 mt-1" id="outCount">0</p>
            </div>
        </div>

        {{-- Toplu Ekle --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">📋 Departmana Toplu Ekle</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                <select id="bulkDepartment" class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-[#02E0FB]">
                    <option value="">Departman Seç</option>
                </select>
                <select id="bulkType" class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-[#02E0FB]">
                    <option value="in">Giriş</option>
                    <option value="out">Çıkış</option>
                    <option value="all">Tamamı (Giriş + Çıkış)</option>
                </select>
                <input type="time" id="bulkTime" class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-[#02E0FB]">
                <input type="date" id="bulkStartDate" class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-[#02E0FB]">
                <div class="flex gap-2 items-center">
                    <input type="date" id="bulkEndDate" class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-[#02E0FB]">
                    <label class="flex items-center gap-1.5 text-xs text-gray-500 whitespace-nowrap cursor-pointer">
                        <input type="checkbox" id="bulkUnlimited" onchange="toggleEndDate('bulk')" class="rounded border-gray-300 text-[#02E0FB] focus:ring-[#02E0FB]">
                        Süresiz
                    </label>
                </div>
                <button onclick="bulkStore()" class="col-span-2 sm:col-span-1 px-4 py-2.5 text-sm font-medium text-white bg-[#FA6001] hover:bg-orange-600 rounded-xl transition-colors">Toplu Ekle</button>
            </div>
        </div>

        {{-- Tablo --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Kayıtlı Personeller</h3>
                <button onclick="showAddForm()" class="px-4 py-2 text-xs font-medium text-white bg-gradient-to-r from-[#02E0FB] to-cyan-500 hover:from-cyan-500 hover:to-[#02E0FB] rounded-xl shadow-sm transition-all">+ Yeni Ekle</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Personel</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Birim</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Tür</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Saat</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Tarih Aralığı</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Durum</th>
                            <th class="px-4 py-3.5 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">İşlem</th>
                        </tr>
                    </thead>
                    <tbody id="hourTableBody" class="divide-y divide-gray-50"></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Kayıt Modal (Ekle/Düzenle) --}}
    <div id="recordModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeRecordModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg border border-gray-100">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-gray-900 font-bold" id="recordModalTitle">Yeni Kayıt</h3>
                <button onclick="closeRecordModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <input type="hidden" id="editId" value="">

                {{-- Searchable Personel --}}
                <div class="relative" id="personelSearchContainer">
                    <input type="text" id="formPersonelInput" placeholder="Personel ara..."
                        autocomplete="off"
                        class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-[#02E0FB] disabled:bg-gray-50 disabled:cursor-not-allowed"
                        oninput="filterPersonel(this.value)"
                        onfocus="showPersonelDropdown()"
                        onblur="hidePersonelDropdown()">
                    <input type="hidden" id="formPersonelId" value="">
                    <div id="personelDropdown" class="hidden absolute z-10 top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto"></div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <select id="formType" class="text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-[#02E0FB] disabled:bg-gray-50 disabled:cursor-not-allowed disabled:text-gray-400">
                        <option value="in">Giriş</option>
                        <option value="out">Çıkış</option>
                        <option value="all">Tamamı (Giriş + Çıkış)</option>
                    </select>
                    <input type="time" id="formTime" class="text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-[#02E0FB]">
                </div>

                <div>
                    <p class="text-xs text-gray-500 mb-2 font-medium">Tarih Aralığı</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[10px] text-gray-400 mb-1 block">Başlangıç</label>
                            <input type="date" id="formStartDate" class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-[#02E0FB]">
                        </div>
                        <div>
                            <label class="text-[10px] text-gray-400 mb-1 block">Bitiş</label>
                            <div class="flex gap-1.5 items-center">
                                <input type="date" id="formEndDate" class="flex-1 text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-[#02E0FB]">
                                <label class="flex items-center gap-1 text-xs text-gray-500 whitespace-nowrap cursor-pointer">
                                    <input type="checkbox" id="formUnlimited" onchange="toggleEndDate('form')" class="rounded border-gray-300 text-[#02E0FB] focus:ring-[#02E0FB]">
                                    Süresiz
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="text" id="formNote" placeholder="Not (opsiyonel)" class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-[#02E0FB] placeholder-gray-400">
            </div>
            <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                <button onclick="closeRecordModal()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">İptal</button>
                <button onclick="saveRecord()" class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-[#02E0FB] to-cyan-500 hover:from-cyan-500 hover:to-[#02E0FB] rounded-xl shadow-sm transition-all">Kaydet</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let allData = null;

function setPassword() {
    const pw = document.getElementById('newPassword').value;
    if (pw.length < 4) { showError('En az 4 karakter'); return; }
    axios.post('{{ route("admin.special-hour.set-password") }}', { password: pw })
        .then(res => { toast('success', res.data.message); location.reload(); })
        .catch(e => showError(e.response?.data?.message || 'Hata'));
}

function verifyPassword() {
    const pw = document.getElementById('loginPassword').value;
    if (!pw) { showError('Şifre girin'); return; }
    document.getElementById('passwordError').classList.add('hidden');
    axios.post('{{ route("admin.special-hour.verify-password") }}', { password: pw })
        .then(res => { allData = res.data.data; renderDashboard(); })
        .catch(e => showError(e.response?.data?.message || 'Şifre hatalı'));
}

function showError(msg) {
    document.getElementById('passwordError').textContent = msg;
    document.getElementById('passwordError').classList.remove('hidden');
}

function renderDashboard() {
    document.getElementById('passwordScreen').classList.add('hidden');
    document.getElementById('dashboardScreen').classList.remove('hidden');
    const hours = allData.hours || [];
    document.getElementById('totalCount').textContent = hours.length;
    document.getElementById('inCount').textContent = hours.filter(h => h.type === 'in').length;
    document.getElementById('outCount').textContent = hours.filter(h => h.type === 'out').length;
    document.getElementById('bulkDepartment').innerHTML = '<option value="">Departman Seç</option>' +
        (allData.departments || []).map(d => `<option value="${d.id}">${escHtml(d.name)}</option>`).join('');
    document.getElementById('bulkStartDate').value = new Date().toISOString().slice(0,10);
    renderTable(hours);
}

function toggleEndDate(prefix) {
    const unlimited = document.getElementById(prefix + 'Unlimited').checked;
    const endDate = document.getElementById(prefix + 'EndDate');
    endDate.disabled = unlimited;
    if (unlimited) endDate.value = '';
}

function getDateRange(prefix) {
    const unlimited = document.getElementById(prefix + 'Unlimited').checked;
    return {
        start_date: document.getElementById(prefix + 'StartDate').value,
        end_date: unlimited ? null : document.getElementById(prefix + 'EndDate').value || null,
    };
}

function setDateRange(prefix, startDate, endDate) {
    document.getElementById(prefix + 'StartDate').value = startDate || '';
    document.getElementById(prefix + 'EndDate').value = endDate || '';
    const unlimited = !endDate;
    document.getElementById(prefix + 'Unlimited').checked = unlimited;
    document.getElementById(prefix + 'EndDate').disabled = unlimited;
}

function escHtml(t) { if (!t) return ''; const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

function renderTable(hours) {
    const tbody = document.getElementById('hourTableBody');
    if (!hours.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-gray-400 text-sm">Kayıt bulunamadı</td></tr>';
        return;
    }
    tbody.innerHTML = hours.map(h => {
        const dateStr = h.start_date
            ? `${h.start_date}${h.end_date ? ' → ' + h.end_date : ' → ∞'}`
            : (h.days_of_week || []).map(d => DAY_NAMES[d] || d).join(', ');
        const typeLabel = h.type === 'in' ? '⬆ Giriş' : '⬇ Çıkış';
        const typeClass = h.type === 'in' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700';
        return `<tr class="hover:bg-gray-50/80 transition-colors">
            <td class="px-4 py-3 text-sm text-gray-800 font-medium">${escHtml(h.personel_name)}</td>
            <td class="px-4 py-3 text-xs text-gray-500">${escHtml(h.department)}</td>
            <td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ${typeClass}">${typeLabel}</span></td>
            <td class="px-4 py-3 text-sm text-gray-700 font-mono">${h.scheduled_time}</td>
            <td class="px-4 py-3 text-xs text-gray-500">${dateStr}</td>
            <td class="px-4 py-3 text-center"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold ${h.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'}">${h.is_active ? 'Aktif' : 'Pasif'}</span></td>
            <td class="px-4 py-3 text-right whitespace-nowrap">
                <button onclick="editHour(${h.id})" class="px-2.5 py-1.5 text-xs font-medium text-amber-600 hover:bg-amber-50 rounded-lg transition-colors mr-1">✎</button>
                <button onclick="deleteHour(${h.id})" class="px-2.5 py-1.5 text-xs font-medium text-red-500 hover:bg-red-50 rounded-lg transition-colors">✕</button>
            </td>
        </tr>`;
    }).join('');
}

// ── Searchable Personel ──
let selectedPersonelId = null;

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('personelDropdown').addEventListener('mousedown', function (e) {
        const item = e.target.closest('.personel-search-item');
        if (!item) return;
        const id = parseInt(item.dataset.id);
        const name = item.dataset.name;
        selectedPersonelId = id;
        document.getElementById('formPersonelInput').value = name;
        document.getElementById('formPersonelId').value = id;
        document.getElementById('personelDropdown').classList.add('hidden');
    });
});

function filterPersonel(query) {
    const dropdown = document.getElementById('personelDropdown');
    let items = (allData?.personels || []);
    if (query) {
        const q = query.toLowerCase();
        items = items.filter(p => {
            const full = (p.first_name + ' ' + p.last_name + ' ' + (p.department || '')).toLowerCase();
            return full.includes(q);
        });
    } else {
        items = items.slice(0, 20);
    }
    if (items.length === 0) {
        dropdown.classList.add('hidden');
        return;
    }
    dropdown.innerHTML = items.map(p =>
        `<div class="personel-search-item px-3 py-2 text-sm cursor-pointer ${p.id === selectedPersonelId ? 'selected' : ''}"
              data-id="${p.id}" data-name="${escHtml(p.first_name)} ${escHtml(p.last_name)}">
            ${escHtml(p.first_name)} ${escHtml(p.last_name)} <span class="text-gray-400 text-xs">— ${escHtml(p.department)}</span>
         </div>`
    ).join('');
    dropdown.classList.remove('hidden');
}

function showPersonelDropdown() {
    filterPersonel(document.getElementById('formPersonelInput').value);
}

function hidePersonelDropdown() {
    setTimeout(() => document.getElementById('personelDropdown').classList.add('hidden'), 200);
}

function resetPersonelSearch() {
    selectedPersonelId = null;
    document.getElementById('formPersonelInput').value = '';
    document.getElementById('formPersonelId').value = '';
    document.getElementById('formPersonelInput').disabled = false;
    document.getElementById('personelDropdown').classList.add('hidden');
}

// ── Modal ──
function showAddForm() {
    document.getElementById('recordModalTitle').textContent = 'Yeni Kayıt';
    document.getElementById('editId').value = '';
    resetPersonelSearch();
    document.getElementById('formType').value = 'in';
    document.getElementById('formType').disabled = false;
    document.getElementById('formTime').value = '';
    setDateRange('form', new Date().toISOString().slice(0,10), '');
    document.getElementById('formNote').value = '';
    document.getElementById('recordModal').classList.remove('hidden');
}

function editHour(id) {
    const h = (allData.hours || []).find(x => x.id === id);
    if (!h) return;
    document.getElementById('recordModalTitle').textContent = 'Kaydı Düzenle';
    document.getElementById('editId').value = h.id;
    selectedPersonelId = h.personel_id;
    document.getElementById('formPersonelInput').value = h.personel_name || '';
    document.getElementById('formPersonelInput').disabled = true;
    document.getElementById('formPersonelId').value = h.personel_id;
    document.getElementById('formType').value = h.type;
    document.getElementById('formType').disabled = true;
    document.getElementById('formTime').value = h.scheduled_time;
    setDateRange('form', h.start_date, h.end_date);
    document.getElementById('formNote').value = h.note || '';
    document.getElementById('recordModal').classList.remove('hidden');
}

function closeRecordModal() {
    document.getElementById('recordModal').classList.add('hidden');
}

function saveRecord() {
    const id = document.getElementById('editId').value;
    const p = document.getElementById('formPersonelId').value;
    const t = document.getElementById('formType').value;
    const ti = document.getElementById('formTime').value;
    const { start_date, end_date } = getDateRange('form');
    const n = document.getElementById('formNote').value;

    if (!p && !id) { toast('error', 'Personel seçin'); return; }
    if (!ti) { toast('error', 'Saat seçin'); return; }
    if (!start_date && !id) { toast('error', 'Başlangıç tarihi seçin'); return; }

    if (id) {
        axios.put('{{ route("admin.special-hour.update", ":id") }}'.replace(':id', id), {
            scheduled_time: ti, start_date, end_date, note: n
        }).then(r => { toast('success', r.data.message); closeRecordModal(); refreshData(); })
          .catch(e => toast('error', e.response?.data?.message || 'Hata'));
    } else {
        axios.post('{{ route("admin.special-hour.store") }}', {
            personel_id: parseInt(p), type: t, scheduled_time: ti, start_date, end_date, note: n
        }).then(r => { toast('success', r.data.message); closeRecordModal(); refreshData(); })
          .catch(e => toast('error', e.response?.data?.message || 'Hata'));
    }
}

function bulkStore() {
    const d = document.getElementById('bulkDepartment').value;
    const t = document.getElementById('bulkType').value;
    const ti = document.getElementById('bulkTime').value;
    const { start_date, end_date } = getDateRange('bulk');

    if (!d) { toast('error', 'Departman seçin'); return; }
    if (!ti) { toast('error', 'Saat seçin'); return; }
    if (!start_date) { toast('error', 'Başlangıç tarihi seçin'); return; }

    axios.post('{{ route("admin.special-hour.bulk-store") }}', {
        department_id: parseInt(d), type: t, scheduled_time: ti, start_date, end_date
    }).then(r => { toast('success', r.data.message); refreshData(); })
      .catch(e => toast('error', e.response?.data?.message || 'Hata'));
}

function deleteHour(id) {
    if (!confirm('Bu kaydı silmek istediğinize emin misiniz?')) return;
    axios.delete('{{ route("admin.special-hour.destroy", ":id") }}'.replace(':id', id))
        .then(r => { toast('success', r.data.message); refreshData(); })
        .catch(e => toast('error', e.response?.data?.message || 'Hata'));
}

function refreshData() {
    axios.post('{{ route("admin.special-hour.verify-password") }}', { password: '___refresh___' })
        .then(r => { allData = r.data.data; renderTable(allData.hours || []); })
        .catch(() => {});
}

function toast(type, msg) {
    const c = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
    const el = document.createElement('div');
    el.className = `fixed top-5 right-5 z-[999] ${c} text-white px-5 py-3 rounded-xl shadow-2xl text-sm font-medium max-w-sm animate-slide-in`;
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3500);
}

const DAY_NAMES = {1:'Pt',2:'Sa',3:'Ça',4:'Pe',5:'Cu',6:'Ct',7:'Pz'};
</script>
@endpush
