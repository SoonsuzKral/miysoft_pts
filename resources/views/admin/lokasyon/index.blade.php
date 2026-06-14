@extends('layouts.app')
@section('title', 'Lokasyon Yönetimi')
@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#02E0FB] transition-colors">Dashboard</a>
    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-800 font-medium">Lokasyon Yönetimi</span>
@endsection
@section('page_header')
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Lokasyon Yönetimi</h1>
        <p class="text-sm text-gray-500 mt-0.5">Konum tanımlama, personel atama ve harita görüntüleme</p>
    </div>
    <div class="flex items-center gap-2">
        @can('location.create')
        <button onclick="openLocationModal()"
            class="flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-[#02E0FB] to-cyan-500 hover:from-cyan-500 hover:to-[#02E0FB] rounded-xl shadow-md hover:shadow-lg transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Yeni Konum
        </button>
        @endcan
    </div>
@endsection
@section('content')

<style>
.animate-scale-in { animation: scaleIn .25s ease-out; }
@keyframes scaleIn { from { opacity: 0; transform: scale(0.95) translateY(-10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
.location-card { transition: all .2s ease; }
.location-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,.08); }
#map { height: 550px; border-radius: 16px; z-index: 1; }
.cluster-icon { display: flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: 700; font-size: 12px; color: #fff; box-shadow: 0 2px 8px rgba(0,0,0,.2); }
.marker-popup .leaflet-popup-content-wrapper { border-radius: 12px; padding: 4px; }
.marker-popup .leaflet-popup-content { margin: 10px 12px; font-size: 13px; }
</style>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

{{-- Tab Navigation --}}
<div class="flex flex-wrap gap-1 border-b border-gray-200 mb-5">
    <button onclick="setLokasyonTab('map')" id="ltab-map" class="px-4 py-2.5 text-sm font-medium border-b-2 border-[#02E0FB] text-[#02E0FB] transition-all">Harita</button>
    <button onclick="setLokasyonTab('list')" id="ltab-list" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-all">Liste</button>
</div>

{{-- HARITA TAB --}}
<div id="view-map">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div id="map"></div>
    </div>
</div>

{{-- LISTE TAB --}}
<div id="view-list" class="hidden">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Arama</label>
                <input type="text" id="filterSearch" placeholder="Konum adı veya adres..."
                    class="filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Tür</label>
                <select id="filterType" class="filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
                    <option value="">Tümü</option>
                    @foreach($types as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Durum</label>
                <select id="filterStatus" class="filter-card w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-[#02E0FB] transition-all">
                    <option value="">Tümü</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Pasif</option>
                </select>
            </div>
            <div class="flex items-end">
                <button onclick="loadLocationTable()" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-[#02E0FB] to-cyan-500 hover:from-cyan-500 hover:to-[#02E0FB] rounded-xl shadow-sm transition-all">Filtrele</button>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Konum</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Tür</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Adres</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Personel</th>
                        <th class="px-4 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Durum</th>
                        <th class="px-4 py-3.5 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">İşlemler</th>
                    </tr>
                </thead>
                <tbody id="locationTableBody" class="divide-y divide-gray-50">
                    <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400 text-sm">Yükleniyor...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3.5 border-t border-gray-50 flex flex-col sm:flex-row items-center justify-between gap-2 bg-gray-50/30">
            <div class="text-xs text-gray-400 font-medium" id="locationTableInfo">—</div>
            <div id="locationPagination" class="flex items-center gap-1.5"></div>
        </div>
    </div>
</div>

{{-- Konum Modal --}}
<div id="locationModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div id="modalBackdrop" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto border border-gray-100">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white sticky top-0 bg-white z-10">
            <h2 id="locationModalTitle" class="text-lg font-bold text-gray-900">Yeni Konum</h2>
            <button onclick="closeLocationModal()" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="locationModalBody" class="px-6 py-5"></div>
        <div id="locationModalFooter" class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-2 sticky bottom-0 bg-white"></div>
    </div>
</div>

{{-- Personel Atama Modal --}}
<div id="assignModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeAssignModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto border border-gray-100">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white sticky top-0 bg-white z-10">
            <h2 class="text-lg font-bold text-gray-900" id="assignModalTitle">Personel Ata</h2>
            <button onclick="closeAssignModal()" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="assignModalBody" class="px-6 py-5"></div>
        <div id="assignModalFooter" class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-2 sticky bottom-0 bg-white"></div>
    </div>
</div>

{{-- Global Modal (Personel Detay) --}}
<div id="globalModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div id="modalBackdrop" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto border border-gray-100">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <h2 id="modalTitle" class="text-lg font-bold text-gray-900">Personeller</h2>
            <button onclick="closeModal()" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="modalBody" class="px-6 py-5"></div>
        <div id="modalFooter" class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-2"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.body.dataset.page = 'lokasyon';

const LOKASYON_URLS = {
    list: '{{ route("admin.lokasyon.list") }}',
    create: '{{ route("admin.lokasyon.create") }}',
    store: '{{ route("admin.lokasyon.store") }}',
    edit: id => '{{ route("admin.lokasyon.edit", ":id") }}'.replace(':id', id),
    update: id => '{{ route("admin.lokasyon.update", ":id") }}'.replace(':id', id),
    destroy: id => '{{ route("admin.lokasyon.destroy", ":id") }}'.replace(':id', id),
    mapData: '{{ route("admin.lokasyon.map-data") }}',
    assignPersonels: id => '{{ route("admin.lokasyon.assign-personels", ":id") }}'.replace(':id', id),
    removePersonel: (locId, perId) => '{{ route("admin.lokasyon.remove-personel", [":loc", ":per"]) }}'.replace(':loc', locId).replace(':per', perId),
    personels: id => '{{ route("admin.lokasyon.personels", ":id") }}'.replace(':id', id),
    assignByDepartment: id => '{{ route("admin.lokasyon.assign-by-department", ":id") }}'.replace(':id', id),
    checkDistance: '{{ route("admin.lokasyon.check-distance") }}',
    types: '{{ route("admin.lokasyon.types.store") }}',
};

let map = null;
let mapMarkers = [];
let currentPage = 1;

document.addEventListener('DOMContentLoaded', () => {
    loadMapData();
    loadLocationTable();
    document.getElementById('filterSearch')?.addEventListener('input', debounce(loadLocationTable, 300));
    document.getElementById('filterType')?.addEventListener('change', loadLocationTable);
    document.getElementById('filterStatus')?.addEventListener('change', loadLocationTable);
});

function debounce(fn, ms) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
}

function setLokasyonTab(tab) {
    ['map','list'].forEach(t => {
        const view = document.getElementById('view-' + t);
        if (view) view.classList.toggle('hidden', t !== tab);
        const btn = document.getElementById('ltab-' + t);
        if (btn) {
            btn.classList.toggle('border-[#02E0FB]', t === tab);
            btn.classList.toggle('text-[#02E0FB]', t === tab);
            btn.classList.toggle('border-transparent', t !== tab);
            btn.classList.toggle('text-gray-500', t !== tab);
        }
    });
    if (tab === 'map' && map) setTimeout(() => map.invalidateSize(), 100);
}

// ─── Harita ───────────────────────────────────────────────

function loadMapData() {
    axios.get(LOKASYON_URLS.mapData).then(res => {
        initMap(res.data.data || []);
    });
}

function initMap(locations) {
    if (map) { map.remove(); map = null; }

    map = L.map('map').setView([39.0, 35.5], 6);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);

    if (!locations.length) return;

    const bounds = [];
    locations.forEach(loc => {
        const iconHtml = `<div class="cluster-icon" style="background:${loc.color}; width:42px; height:42px; display:flex; align-items:center; justify-content:center; border-radius:50%; font-size:16px;">${loc.type_icon}</div>`;

        const marker = L.marker([loc.latitude, loc.longitude], {
            icon: L.divIcon({
                html: iconHtml,
                className: 'marker-popup',
                iconSize: [42, 42],
                iconAnchor: [21, 21],
                popupAnchor: [0, -24],
            })
        }).bindPopup(`
            <div style="min-width:220px;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                    <span style="font-size:24px;">${loc.type_icon}</span>
                    <div>
                        <strong style="font-size:15px;">${escHtml(loc.name)}</strong>
                        <div style="font-size:11px;color:#6b7280;">${escHtml(loc.type)}</div>
                    </div>
                </div>
                <div style="font-size:12px;color:#4b5563;margin-bottom:8px;">
                    ${loc.address ? '<div>📍 ' + escHtml(loc.address) + '</div>' : ''}
                    ${loc.city ? '<div>🏙️ ' + escHtml(loc.city) + '</div>' : ''}
                    <div>📏 ${loc.radius}m çap · 📌 ${loc.latitude.toFixed(4)}, ${loc.longitude.toFixed(4)}</div>
                </div>
                <div style="display:flex;gap:8px;margin-bottom:10px;font-size:13px;">
                    <span style="background:#e0f2fe;padding:2px 10px;border-radius:20px;font-weight:600;color:#0369a1;">👤 ${loc.personel_count} kişi</span>
                    <span style="background:#d1fae5;padding:2px 10px;border-radius:20px;font-weight:600;color:#059669;">⬆ ${loc.today_ins} giriş</span>
                    <span style="background:#fee2e2;padding:2px 10px;border-radius:20px;font-weight:600;color:#dc2626;">⬇ ${loc.today_outs} çıkış</span>
                </div>
                <div style="display:flex;gap:6px;">
                    <button onclick="showLocationPersonels(${loc.id})" style="flex:1;padding:6px;font-size:11px;font-weight:600;background:#02E0FB;color:#fff;border:none;border-radius:8px;cursor:pointer;">👥 Personel</button>
                    <button onclick="openAssignModal(${loc.id})" style="flex:1;padding:6px;font-size:11px;font-weight:600;background:#f3f4f6;color:#374151;border:none;border-radius:8px;cursor:pointer;">➕ Ata</button>
                </div>
            </div>
        `, { className: 'marker-popup', maxWidth: 300 });

        marker._locationId = loc.id;
        mapMarkers.push(marker);
        marker.addTo(map);
        bounds.push([loc.latitude, loc.longitude]);
    });

    if (bounds.length) map.fitBounds(bounds, { padding: [50, 50], maxZoom: 12 });

    map.on('popupopen', function() {
        setTimeout(() => map.invalidateSize(), 50);
    });
}

// ─── Modal ────────────────────────────────────────────────

function openLocationModal(id) {
    const url = id ? LOKASYON_URLS.edit(id) : LOKASYON_URLS.create;
    axios.get(url).then(res => {
        document.getElementById('locationModalTitle').textContent = id ? 'Konum Düzenle' : 'Yeni Konum';
        document.getElementById('locationModalBody').innerHTML = res.data.html;
        const types = res.data.types;
        document.getElementById('locationModalFooter').innerHTML = `
            <button onclick="closeLocationModal()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">İptal</button>
            <button onclick="submitLocationForm(${id ? "'" + LOKASYON_URLS.update(id) + "','PUT'" : ''})" class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-[#02E0FB] to-cyan-500 hover:from-cyan-500 hover:to-[#02E0FB] rounded-xl shadow-sm transition-all">${id ? 'Güncelle' : 'Kaydet'}</button>`;
        document.getElementById('locationModal').classList.remove('hidden');
        initFormMap();
    });
}

function closeLocationModal() {
    document.getElementById('locationModal').classList.add('hidden');
}

function submitLocationForm(url = LOKASYON_URLS.store, method = 'POST') {
    const form = document.getElementById('locationForm');
    if (!form) return;
    const data = Object.fromEntries(new FormData(form).entries());
    data.is_active = form.querySelector('[name="is_active"]')?.checked ? 1 : 0;

    axios({ method, url, data }).then(res => {
        closeLocationModal();
        toast('success', res.data.message);
        loadLocationTable();
        loadMapData();
    }).catch(err => {
        const msg = err.response?.data?.message || err.response?.data?.errors || 'Kaydetme başarısız';
        toast('error', typeof msg === 'string' ? msg : Object.values(msg).flat().join(', '));
    });
}

// ─── Yeni Tür Ekle ────────────────────────────────────────

function toggleNewTypeForm() {
    const el = document.getElementById('newTypeForm');
    if (!el) return;
    el.classList.toggle('hidden');
    if (!el.classList.contains('hidden')) document.getElementById('newTypeName')?.focus();
}

function saveNewType() {
    const nameEl = document.getElementById('newTypeName');
    const iconEl = document.getElementById('newTypeIcon');
    const colorEl = document.getElementById('newTypeColor');
    const name = nameEl?.value?.trim();
    if (!name) { toast('error', 'Tür adı gerekli.'); return; }

    axios.post(LOKASYON_URLS.types, { name, icon: iconEl?.value || '📍', color: colorEl?.value || '#6B7280' }).then(res => {
        const t = res.data.data;
        const sel = document.getElementById('locationTypeSelect');
        const opt = document.createElement('option');
        opt.value = t.id;
        opt.textContent = (t.icon || '📍') + ' ' + t.name;
        opt.selected = true;
        sel.appendChild(opt);
        toggleNewTypeForm();
        nameEl.value = '';
        toast('success', 'Tür eklendi.');
    }).catch(err => {
        const msg = err.response?.data?.message || err.response?.data?.errors || 'Ekleme başarısız';
        toast('error', typeof msg === 'string' ? msg : Object.values(msg).flat().join(', '));
    });
}

// ─── Form Harita ──────────────────────────────────────────

function initFormMap() {
    const latInput = document.getElementById('locLatitude');
    const lngInput = document.getElementById('locLongitude');
    if (!latInput || !lngInput) return;

    const hasCoords = parseFloat(latInput.value) && parseFloat(lngInput.value);
    const lat = parseFloat(latInput.value) || 39.0;
    const lng = parseFloat(lngInput.value) || 35.5;
    const formMap = L.map('formMap').setView([lat, lng], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(formMap);

    let marker = L.marker([lat, lng], { draggable: true }).addTo(formMap);

    function updateCoords(lat, lng) {
        latInput.value = lat.toFixed(7);
        lngInput.value = lng.toFixed(7);
        showStreetViewBtn(lat, lng);
    }

    function showStreetViewBtn(lat, lng) {
        const sv = document.getElementById('streetViewContainer');
        if (sv) {
            sv.classList.remove('hidden');
            sv.dataset.lat = lat;
            sv.dataset.lng = lng;
        }
    }

    marker.on('dragend', () => {
        const pos = marker.getLatLng();
        updateCoords(pos.lat, pos.lng);
    });

    formMap.on('click', e => {
        marker.setLatLng(e.latlng);
        updateCoords(e.latlng.lat, e.latlng.lng);
    });

    // Address search
    const searchInput = document.getElementById('addressSearch');
    const searchResults = document.getElementById('addressSearchResults');
    let searchTimer = null;

    if (searchInput && searchResults) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimer);
            const q = searchInput.value.trim();
            if (q.length < 3) { searchResults.classList.add('hidden'); return; }

            searchTimer = setTimeout(() => {
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&limit=5&countrycodes=tr`)
                    .then(r => r.json())
                    .then(data => {
                        if (!data.length) { searchResults.classList.add('hidden'); return; }
                        searchResults.innerHTML = data.map(p => `
                            <button type="button" class="w-full text-left px-3 py-2.5 text-sm hover:bg-gray-50 border-b border-gray-50 last:border-0 transition-colors"
                                data-lat="${p.lat}" data-lng="${p.lon}">
                                <span class="block font-medium text-gray-800 truncate">${p.display_name.split(',')[0]}</span>
                                <span class="block text-[11px] text-gray-400 truncate">${p.display_name}</span>
                            </button>
                        `).join('');
                        searchResults.classList.remove('hidden');

                        searchResults.querySelectorAll('button').forEach(btn => {
                            btn.addEventListener('click', () => {
                                const slat = parseFloat(btn.dataset.lat);
                                const slng = parseFloat(btn.dataset.lng);
                                searchInput.value = btn.querySelector('span:first-child')?.textContent || '';
                                searchResults.classList.add('hidden');
                                marker.setLatLng([slat, slng]);
                                formMap.setView([slat, slng], 16);
                                updateCoords(slat, slng);

                                // Reverse geocode to fill address fields
                                fillAddress(slat, slng);
                            });
                        });
                    }).catch(() => searchResults.classList.add('hidden'));
            }, 400);
        });

        document.addEventListener('click', e => {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.add('hidden');
            }
        });
    }

    if (hasCoords) showStreetViewBtn(lat, lng);

    setTimeout(() => formMap.invalidateSize(), 100);
}

function fillAddress(lat, lng) {
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=tr`)
        .then(r => r.json())
        .then(data => {
            const adr = data.address || {};
            const addrInput = document.querySelector('[name="address"]');
            const cityInput = document.querySelector('[name="city"]');
            const districtInput = document.querySelector('[name="district"]');
            if (addrInput && adr.road) addrInput.value = [adr.road, adr.house_number].filter(Boolean).join(' No:');
            if (cityInput) cityInput.value = adr.city || adr.town || adr.province || adr.state || '';
            if (districtInput) districtInput.value = adr.suburb || adr.county || adr.town || '';
        })
        .catch(() => {});
}

function openStreetView() {
    const sv = document.getElementById('streetViewContainer');
    if (!sv || sv.classList.contains('hidden')) return;
    const lat = sv.dataset.lat;
    const lng = sv.dataset.lng;
    if (lat && lng) {
        const a = document.createElement('a');
        a.href = `https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=${lat},${lng}`;
        a.target = '_blank';
        a.rel = 'noopener';
        a.click();
    }
}

// ─── Atama Modal ──────────────────────────────────────────

let currentAssignLocationId = null;
let currentAssignType = 'inout';

const ASSIGN_TYPES = [
    { key: 'in',       label: 'Giriş',          color: 'bg-blue-50 text-blue-700 border-blue-200' },
    { key: 'out',      label: 'Çıkış',          color: 'bg-cyan-50 text-cyan-700 border-cyan-200' },
    { key: 'inout',    label: 'Giriş/Çıkış',    color: 'bg-indigo-50 text-indigo-700 border-indigo-200' },
    { key: 'shift',    label: 'Vardiya',         color: 'bg-amber-50 text-amber-700 border-amber-200' },
    { key: 'overtime', label: 'Fazla Mesai',     color: 'bg-purple-50 text-purple-700 border-purple-200' },
];

function openAssignModal(locationId, type) {
    currentAssignLocationId = locationId;
    currentAssignType = type || 'inout';
    document.getElementById('assignModalTitle').textContent = 'Personel Ata';

    loadAssignPersonels(locationId, currentAssignType);
}

function loadAssignPersonels(locationId, type) {
    axios.get(LOKASYON_URLS.personels(locationId), { params: { type } }).then(res => {
        const grouped = res.data.data || {};
        const assigned = grouped[type] || [];

        axios.get('/admin/personel/list', { params: { per_page: 500 } }).then(pRes => {
            const allPersonels = pRes.data.data || [];
            const assignedIds = assigned.map(p => p.id);
            let html = '';

            // Type tabs
            html += `<div class="flex gap-2 mb-4" id="assignTypeTabs">`;
            ASSIGN_TYPES.forEach(t => {
                const active = t.key === type ? 'bg-[#02E0FB] text-white border-[#02E0FB] shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50';
                html += `<button type="button" onclick="switchAssignType('${t.key}')" class="flex-1 text-xs font-medium px-3 py-2 rounded-lg border transition-all ${active}">${t.label}</button>`;
            });
            html += `</div>`;

            // Department bulk assign
            html += `
            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Departmana Göre Toplu Ata (${ASSIGN_TYPES.find(t=>t.key===type).label})</label>
                <div class="flex gap-2">
                    <select id="bulkDepartment" class="flex-1 text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-[#02E0FB]">
                        <option value="">Departman Seçin</option>
                        @foreach($departments as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                    <button onclick="assignByDepartment(${locationId})" class="px-4 py-2 text-sm font-medium text-white bg-[#FA6001] hover:bg-orange-600 rounded-xl transition-colors">Toplu Ata</button>
                </div>
            </div>
            <div class="mb-3">
                <input type="text" id="assignSearch" placeholder="Personel ara..." class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-[#02E0FB]">
            </div>
            <div class="space-y-1.5 max-h-64 overflow-y-auto" id="assignPersonelList">`;

            allPersonels.forEach(p => {
                const checked = assignedIds.includes(p.id) ? 'checked' : '';
                const initials = (p.first_name?.[0] || '') + (p.last_name?.[0] || '');
                html += `<label class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors">
                    <input type="checkbox" class="assign-check rounded border-gray-300 text-[#02E0FB] focus:ring-[#02E0FB]" value="${p.id}" ${checked}>
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#02E0FB] to-cyan-500 text-white text-xs font-bold flex items-center justify-center shrink-0">${initials}</div>
                    <div><p class="text-sm font-medium text-gray-800">${escHtml(p.first_name)} ${escHtml(p.last_name)}</p><p class="text-xs text-gray-400">${p.department?.name || '—'}</p></div>
                    ${checked ? '<span class="ml-auto text-xs text-emerald-600 font-medium bg-emerald-50 px-2 py-0.5 rounded-full">Atanmış</span>' : ''}
                </label>`;
            });

            html += `</div>`;
            document.getElementById('assignModalBody').innerHTML = html;
            document.getElementById('assignModalFooter').innerHTML = `
                <button onclick="closeAssignModal()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">İptal</button>
                <button onclick="submitAssign(${locationId})" class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-[#02E0FB] to-cyan-500 hover:from-cyan-500 hover:to-[#02E0FB] rounded-xl shadow-sm transition-all">Kaydet</button>`;
            document.getElementById('assignModal').classList.remove('hidden');

            document.getElementById('assignSearch')?.addEventListener('input', function() {
                const q = this.value.toLowerCase();
                document.querySelectorAll('#assignPersonelList label').forEach(el => {
                    el.style.display = el.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
            });
        });
    });
}

function switchAssignType(type) {
    currentAssignType = type;
    loadAssignPersonels(currentAssignLocationId, type);
}

function closeAssignModal() {
    document.getElementById('assignModal').classList.add('hidden');
    currentAssignLocationId = null;
}

function submitAssign(locationId) {
    const checked = document.querySelectorAll('.assign-check:checked');
    const ids = Array.from(checked).map(cb => parseInt(cb.value));
    if (!ids.length) { toast('warning', 'Lütfen en az bir personel seçin.'); return; }

    axios.post(LOKASYON_URLS.assignPersonels(locationId), { personel_ids: ids, type: currentAssignType }).then(res => {
        closeAssignModal();
        toast('success', res.data.message);
        loadLocationTable();
        loadMapData();
    }).catch(e => toast('error', e.response?.data?.message || 'Atama başarısız'));
}

function assignByDepartment(locationId) {
    const deptId = document.getElementById('bulkDepartment')?.value;
    if (!deptId) { toast('warning', 'Lütfen bir departman seçin.'); return; }

    axios.post(LOKASYON_URLS.assignByDepartment(locationId), { department_id: deptId, type: currentAssignType }).then(res => {
        toast('success', res.data.message);
        loadAssignPersonels(locationId, currentAssignType);
        loadMapData();
    }).catch(e => toast('error', e.response?.data?.message || 'Atama başarısız'));
}

// ─── Personel Listesi (Popup'tan) ────────────────────────

let personelPopupCache = null;
let personelPopupLocationId = null;
let personelPopupType = null;

function showLocationPersonels(locationId) {
    personelPopupLocationId = locationId;
    personelPopupType = null;
    personelPopupCache = null;
    document.getElementById('globalModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Konum Personelleri';
    document.getElementById('modalBody').innerHTML = '<div class="flex items-center justify-center py-10"><div class="w-6 h-6 border-2 border-[#02E0FB] border-t-transparent rounded-full animate-spin"></div></div>';
    document.getElementById('modalFooter').innerHTML = '<button onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Kapat</button>';

    axios.get(LOKASYON_URLS.personels(locationId)).then(res => {
        personelPopupCache = res.data.data || {};
        renderPersonelPopup(null);
    });
}

function renderPersonelPopup(type) {
    const grouped = personelPopupCache;
    if (!grouped) return;

    const allKeys = Object.keys(grouped).filter(k => grouped[k]?.length);
    const totalPersonels = allKeys.reduce((sum, k) => sum + (grouped[k].length), 0);

    document.getElementById('modalTitle').textContent = 'Konum Personelleri (' + totalPersonels + ')';

    let html = '';

    html += `<div class="flex gap-2 mb-4 flex-wrap">`;
    html += `<button type="button" onclick="renderPersonelPopup(null)" class="text-xs font-medium px-3 py-2 rounded-lg border transition-all ${!type ? 'bg-[#02E0FB] text-white border-[#02E0FB] shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'}">Tümü (${totalPersonels})</button>`;
    ASSIGN_TYPES.forEach(t => {
        const cnt = (grouped[t.key] || []).length;
        if (!cnt && type !== t.key) return;
        const active = t.key === type;
        html += `<button type="button" onclick="renderPersonelPopup('${t.key}')" class="text-xs font-medium px-3 py-2 rounded-lg border transition-all ${active ? 'bg-[#02E0FB] text-white border-[#02E0FB] shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'}">${t.label} (${cnt})</button>`;
    });
    html += `</div>`;

    html += '<div class="space-y-2">';

    const keysToShow = type ? [type] : allKeys;
    let hadAny = false;

    keysToShow.forEach(tKey => {
        const list = grouped[tKey] || [];
        if (!list.length) return;
        hadAny = true;
        const tDef = ASSIGN_TYPES.find(at => at.key === tKey);

        list.forEach(p => {
            html += `<div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#02E0FB] to-cyan-500 text-white text-xs font-bold flex items-center justify-center shadow-sm shrink-0">${p.initials}</div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">${escHtml(p.name)}</p>
                        <p class="text-xs text-gray-400 truncate">${escHtml(p.department)} · ${escHtml(p.position)}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0 ml-2">
                    ${tDef ? `<span class="text-[10px] font-medium px-2 py-0.5 rounded-full ${tDef.color}">${tDef.label}</span>` : ''}
                    <span class="text-xs text-gray-400 hidden sm:inline">${p.assigned_at || ''}</span>
                    <button onclick="removePersonelFromLocation(${personelPopupLocationId}, ${p.id}, '${tKey}')" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Çıkar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>`;
        });
    });

    if (!hadAny) {
        html += '<p class="text-center text-gray-400 py-6 text-sm">Bu türde personel atanmamış</p>';
    }

    html += '</div>';
    document.getElementById('modalBody').innerHTML = html;
}

function removePersonelFromLocation(locationId, personelId, type) {
    if (!confirm('Personeli konumdan çıkarmak istediğinize emin misiniz?')) return;
    axios.delete(LOKASYON_URLS.removePersonel(locationId, personelId), { data: { type } }).then(res => {
        if (personelPopupCache?.[type]) {
            personelPopupCache[type] = personelPopupCache[type].filter(p => p.id !== personelId);
            if (!personelPopupCache[type].length) delete personelPopupCache[type];
        }
        toast('success', res.data.message);
        renderPersonelPopup(personelPopupType);
        loadLocationTable();
        loadMapData();
    }).catch(e => toast('error', e.response?.data?.message || 'Çıkarma başarısız'));
}

// ─── Liste Tablo ─────────────────────────────────────────

function loadLocationTable(page) {
    if (page) currentPage = page;
    const params = {
        page: currentPage,
        search: document.getElementById('filterSearch')?.value || '',
        type_id: document.getElementById('filterType')?.value || '',
        status: document.getElementById('filterStatus')?.value || '',
        per_page: 15,
    };
    axios.get(LOKASYON_URLS.list, { params }).then(res => {
        renderLocationTable(res.data);
        renderPagination(res.data);
    });
}

function renderLocationTable(data) {
    const tbody = document.getElementById('locationTableBody');
    if (!data.data?.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-12 text-center text-gray-400 text-sm">Konum bulunamadı</td></tr>';
        document.getElementById('locationTableInfo').textContent = '0 kayıt';
        return;
    }
    tbody.innerHTML = data.data.map(l => `
        <tr class="hover:bg-gray-50/80 transition-colors group">
            <td data-label="Konum" class="px-4 py-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg shrink-0" style="background:${l.color}20">${l.type_icon}</div>
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">${escHtml(l.name)}</p>
                        <p class="text-[11px] text-gray-400">${l.city ? escHtml(l.city) : ''} ${l.district ? '· ' + escHtml(l.district) : ''}</p>
                    </div>
                </div>
            </td>
            <td data-label="Tür" class="px-4 py-3">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium" style="background:${l.type_color}15;color:${l.type_color}">${l.type_icon} ${escHtml(l.type)}</span>
            </td>
            <td data-label="Adres" class="px-4 py-3 text-xs text-gray-500 max-w-[200px] truncate">${l.address ? escHtml(l.address) : '—'}</td>
            <td data-label="Personel" class="px-4 py-3">
                <div class="flex items-center gap-1 justify-center">
                    ${l.in_count > 0 ? `<span class="inline-flex items-center justify-center min-w-[20px] h-[20px] px-1 rounded-full bg-blue-50 text-blue-700 text-[9px] font-bold" title="Sadece Giriş">G${l.in_count}</span>` : ''}
                    ${l.out_count > 0 ? `<span class="inline-flex items-center justify-center min-w-[20px] h-[20px] px-1 rounded-full bg-cyan-50 text-cyan-700 text-[9px] font-bold" title="Sadece Çıkış">Ç${l.out_count}</span>` : ''}
                    ${l.inout_count > 0 ? `<span class="inline-flex items-center justify-center min-w-[22px] h-[22px] px-1 rounded-full bg-indigo-50 text-indigo-700 text-[10px] font-bold" title="Giriş/Çıkış">${l.inout_count}</span>` : ''}
                    ${l.shift_count > 0 ? `<span class="inline-flex items-center justify-center min-w-[22px] h-[22px] px-1 rounded-full bg-amber-50 text-amber-700 text-[10px] font-bold" title="Vardiya">${l.shift_count}</span>` : ''}
                    ${l.overtime_count > 0 ? `<span class="inline-flex items-center justify-center min-w-[22px] h-[22px] px-1 rounded-full bg-purple-50 text-purple-700 text-[10px] font-bold" title="Fazla Mesai">${l.overtime_count}</span>` : ''}
                    ${!l.in_count && !l.out_count && !l.inout_count && !l.shift_count && !l.overtime_count ? '<span class="text-xs text-gray-400">—</span>' : ''}
                </div>
            </td>
            <td data-label="Durum" class="px-4 py-3 text-center">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ${l.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'}">${l.is_active ? 'Aktif' : 'Pasif'}</span>
            </td>
            <td data-label="İşlemler" class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-0.5 opacity-70 group-hover:opacity-100 transition-opacity">
                    <button onclick="showLocationPersonels(${l.id})" class="p-1.5 text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all" title="Personeller">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                    </button>
                    <button onclick="openAssignModal(${l.id})" class="p-1.5 text-gray-400 hover:text-emerald-500 hover:bg-emerald-50 rounded-lg transition-all" title="Personel Ata">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    </button>
                    <button onclick="openLocationModal(${l.id})" class="p-1.5 text-gray-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-all" title="Düzenle">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button onclick="confirmDeleteLocation(${l.id})" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all" title="Sil">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
    document.getElementById('locationTableInfo').textContent = `${data.total} kayıttan ${Math.min(data.data.length, 15)} gösteriliyor`;
}

function renderPagination(data) {
    const el = document.getElementById('locationPagination');
    if (!el) return;
    const tp = data.pages || 1;
    const page = currentPage;
    let h = '';
    if (page > 1) h += `<button onclick="loadLocationTable(${page-1})" class="px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors">‹</button>`;
    for (let i = Math.max(1, page-2); i <= Math.min(tp, page+2); i++) {
        h += i === page
            ? `<span class="px-2.5 py-1.5 text-xs bg-[#02E0FB] text-white rounded-lg font-semibold shadow-sm">${i}</span>`
            : `<button onclick="loadLocationTable(${i})" class="px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors">${i}</button>`;
    }
    if (page < tp) h += `<button onclick="loadLocationTable(${page+1})" class="px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors">›</button>`;
    el.innerHTML = h;
}

function confirmDeleteLocation(id) {
    if (!confirm('Bu konumu silmek istediğinize emin misiniz?')) return;
    axios.delete(LOKASYON_URLS.destroy(id)).then(res => {
        toast('success', res.data.message);
        loadLocationTable();
        loadMapData();
    }).catch(e => toast('error', e.response?.data?.message || 'Silme başarısız'));
}

function closeModal() {
    document.getElementById('globalModal').classList.add('hidden');
}

function toast(type, msg) {
    const c = type === 'success' ? 'bg-emerald-500' : type === 'warning' ? 'bg-amber-500' : 'bg-red-500';
    const el = document.createElement('div');
    el.className = `fixed top-5 right-5 z-[999] ${c} text-white px-5 py-3 rounded-xl shadow-2xl text-sm font-medium animate-slide-in max-w-sm`;
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3500);
}

function escHtml(text) {
    if (!text) return '';
    const d = document.createElement('div');
    d.textContent = text;
    return d.innerHTML;
}
</script>
@endpush
