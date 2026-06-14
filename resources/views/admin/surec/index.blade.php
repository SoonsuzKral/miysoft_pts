@extends('layouts.app')
@section('title', 'Süreç Yönetimi')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#02E0FB]">Dashboard</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-800 font-medium">Süreç Yönetimi</span>
@endsection

@section('page_header')
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Süreç Yönetimi</h1>
        <p class="text-sm text-gray-500 mt-0.5">İşe giriş, işten çıkış ve özel süreçleri checklist ile yönetin.</p>
    </div>
    <button onclick="openCreateTemplate()" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-[#02E0FB] rounded-lg hover:bg-cyan-400 transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Yeni Şablon
    </button>
@endsection

@section('content')

{{-- KPI Kartları --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-cyan-50 flex items-center justify-center text-[#02E0FB]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div>
            <p class="text-xs text-gray-500">Toplam Şablon</p>
            <p class="text-2xl font-bold text-gray-900" id="kpiTemplates">—</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <div>
            <p class="text-xs text-gray-500">Aktif Süreç</p>
            <p class="text-2xl font-bold text-gray-900" id="kpiActive">—</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center text-green-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <p class="text-xs text-gray-500">Bu Ay Tamamlanan</p>
            <p class="text-2xl font-bold text-gray-900" id="kpiCompleted">—</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center text-red-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <p class="text-xs text-gray-500">Geciken Süreç</p>
            <p class="text-2xl font-bold text-gray-900" id="kpiOverdue">—</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Şablonlar --}}
    <div class="lg:col-span-1 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Süreç Şablonları</h2>
            <span class="text-xs text-gray-400" id="templateCount">{{ $templates->count() }} adet</span>
        </div>
        <div id="templatesList" class="space-y-3">
            @forelse($templates as $template)
            <div class="template-card bg-white rounded-xl border border-gray-100 shadow-sm p-4 hover:shadow-md transition-shadow" data-id="{{ $template->id }}">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1 min-w-0">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium mb-1.5
                            {{ $template->type === 'onboarding' ? 'bg-green-100 text-green-700' : ($template->type === 'offboarding' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">
                            {{ $template->type_label }}
                        </span>
                        <h3 class="font-semibold text-gray-800 text-sm truncate">{{ $template->name }}</h3>
                        @if($template->description)
                        <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $template->description }}</p>
                        @endif
                    </div>
                    <span class="text-xs text-gray-400 font-medium ml-2 shrink-0">{{ count($template->steps ?? []) }} adım</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1 text-xs {{ $template->is_active ? 'text-green-600' : 'text-gray-400' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $template->is_active ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                            {{ $template->is_active ? 'Aktif' : 'Pasif' }}
                        </span>
                    </div>
                    <div class="flex items-center gap-0.5">
                        <button onclick="event.stopPropagation(); openInstantiate({{ $template->id }}, '{{ $template->name }}')"
                            class="p-1.5 text-gray-400 hover:text-[#02E0FB] hover:bg-cyan-50 rounded-lg" title="Süreç Başlat">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </button>
                        <button onclick="event.stopPropagation(); openEditTemplate({{ $template->id }})"
                            class="p-1.5 text-gray-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg" title="Düzenle">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button onclick="event.stopPropagation(); toggleTemplate({{ $template->id }})"
                            class="p-1.5 text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded-lg" title="{{ $template->is_active ? 'Pasifleştir' : 'Aktifleştir' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </button>
                        <button onclick="event.stopPropagation(); confirmDelete(PROCESS_URLS.destroy({{ $template->id }}), loadTemplates)"
                            class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg" title="Sil">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-gray-400 bg-white rounded-xl border border-dashed border-gray-200">
                <p class="text-3xl mb-2">📋</p>
                <p class="text-sm">Henüz şablon yok</p>
                <button onclick="openCreateTemplate()" class="mt-3 text-xs text-[#02E0FB] font-medium hover:underline">İlk şablonu oluştur</button>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Süreçler --}}
    <div class="lg:col-span-2">
        {{-- Tabs --}}
        <div class="flex items-center gap-1 mb-4 border-b border-gray-100">
            <button class="instance-tab px-4 py-2.5 text-sm font-medium border-b-2 transition-colors text-gray-900 border-[#02E0FB]" data-status="" onclick="switchTab(this)">Tümü</button>
            <button class="instance-tab px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-colors" data-status="in_progress" onclick="switchTab(this)">Devam Eden</button>
            <button class="instance-tab px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-colors" data-status="completed" onclick="switchTab(this)">Tamamlanan</button>
            <button class="instance-tab px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-colors" data-status="cancelled" onclick="switchTab(this)">İptal</button>
            <div class="flex-1"></div>
            <div class="flex items-center gap-2 pb-2">
                <input type="text" id="instanceSearch" placeholder="Personel ara..."
                    class="w-40 px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]"
                    onkeyup="if(event.key==='Enter') loadInstances()">
                <select id="instanceTypeFilter" onchange="loadInstances()" class="px-2 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
                    <option value="">Tüm Türler</option>
                    <option value="onboarding">İşe Giriş</option>
                    <option value="offboarding">İşten Çıkış</option>
                    <option value="custom">Özel</option>
                </select>
            </div>
        </div>

        <div id="instancesList" class="space-y-4">
            <div class="text-center py-16 text-gray-400 bg-white rounded-xl border border-gray-100 shadow-sm">
                <p class="text-4xl mb-3">🚀</p>
                <p class="font-medium">Yükleniyor...</p>
            </div>
        </div>

        {{-- Pagination --}}
        <div id="instancePagination" class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div class="text-xs text-gray-500" id="instanceTableInfo">—</div>
            <div class="flex items-center gap-1" id="instancePages"></div>
        </div>
    </div>
</div>

{{-- Global Modal --}}
<div id="globalModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div onclick="closeModal()" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto border border-gray-100">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <h2 id="modalTitle" class="text-lg font-bold text-gray-900">Başlık</h2>
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
const PROCESS_URLS = {
    templates:     '{{ route("admin.processes.templates") }}',
    store:         '{{ route("admin.processes.store") }}',
    edit:          id => `/admin/processes/templates/${id}/edit`,
    update:        id => `/admin/processes/templates/${id}`,
    destroy:       id => `/admin/processes/templates/${id}`,
    toggle:        id => `/admin/processes/templates/${id}/toggle`,
    kpi:           '{{ route("admin.processes.kpi") }}',
    instances:     '{{ route("admin.processes.instances") }}',
    instantiate:   '{{ route("admin.processes.instantiate") }}',
    complete:      id => `/admin/processes/instances/${id}/complete-step`,
};

let currentTab = '';
let currentPage = 1;

// ─── Sayfa Yüklenince ───
document.addEventListener('DOMContentLoaded', () => {
    loadKPIs();
    loadInstances();
});

// ─── KPI ───
function loadKPIs() {
    axios.get(PROCESS_URLS.kpi).then(res => {
        document.getElementById('kpiTemplates').textContent = res.data.total_templates;
        document.getElementById('kpiActive').textContent = res.data.active_processes;
        document.getElementById('kpiCompleted').textContent = res.data.completed_this_month;
        document.getElementById('kpiOverdue').textContent = res.data.overdue;
    });
}

// ─── Şablonlar ───
function loadTemplates() {
    axios.get(PROCESS_URLS.templates, { params: { per_page: 50 } }).then(res => {
        const list = document.getElementById('templatesList');
        const count = document.getElementById('templateCount');
        count.textContent = res.data.total + ' adet';
        if (!res.data.data.length) {
            list.innerHTML = `
                <div class="text-center py-8 text-gray-400 bg-white rounded-xl border border-dashed border-gray-200">
                    <p class="text-3xl mb-2">📋</p>
                    <p class="text-sm">Henüz şablon yok</p>
                </div>`;
            return;
        }
        list.innerHTML = res.data.data.map(t => {
            const typeClass = t.type === 'onboarding' ? 'bg-green-100 text-green-700' : (t.type === 'offboarding' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700');
            const typeLabel = t.type === 'onboarding' ? 'İşe Giriş' : (t.type === 'offboarding' ? 'İşten Çıkış' : 'Özel Süreç');
            const stepCount = t.steps ? t.steps.length : 0;
            return `
            <div class="template-card bg-white rounded-xl border border-gray-100 shadow-sm p-4 hover:shadow-md transition-shadow" data-id="${t.id}">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1 min-w-0">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium mb-1.5 ${typeClass}">${typeLabel}</span>
                        <h3 class="font-semibold text-gray-800 text-sm truncate">${escHtml(t.name)}</h3>
                        ${t.description ? `<p class="text-xs text-gray-400 mt-0.5 truncate">${escHtml(t.description)}</p>` : ''}
                    </div>
                    <span class="text-xs text-gray-400 font-medium ml-2 shrink-0">${stepCount} adım</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1 text-xs ${t.is_active ? 'text-green-600' : 'text-gray-400'}">
                            <span class="w-1.5 h-1.5 rounded-full ${t.is_active ? 'bg-green-500' : 'bg-gray-300'}"></span>
                            ${t.is_active ? 'Aktif' : 'Pasif'}
                        </span>
                    </div>
                    <div class="flex items-center gap-0.5">
                        <button onclick="event.stopPropagation(); openInstantiate(${t.id}, '${escHtml(t.name)}')"
                            class="p-1.5 text-gray-400 hover:text-[#02E0FB] hover:bg-cyan-50 rounded-lg" title="Süreç Başlat">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </button>
                        <button onclick="event.stopPropagation(); openEditTemplate(${t.id})"
                            class="p-1.5 text-gray-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg" title="Düzenle">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button onclick="event.stopPropagation(); toggleTemplate(${t.id})"
                            class="p-1.5 text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded-lg" title="${t.is_active ? 'Pasifleştir' : 'Aktifleştir'}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </button>
                        <button onclick="event.stopPropagation(); confirmDelete('${PROCESS_URLS.destroy(t.id)}', loadTemplates)"
                            class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg" title="Sil">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            </div>`;
        }).join('');
    });
}

function toggleTemplate(id) {
    axios.put(PROCESS_URLS.toggle(id)).then(res => {
        toast('success', res.data.message);
        loadTemplates();
        loadKPIs();
    });
}

function openCreateTemplate() {
    document.getElementById('modalTitle').textContent = 'Yeni Süreç Şablonu';
    document.getElementById('modalBody').innerHTML = `
        @include('admin.surec._template_form', ['processTemplate' => null])`;
    document.getElementById('modalFooter').innerHTML = `
        <button onclick="closeModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
        <button onclick="submitTemplate()" class="px-4 py-2 text-sm text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-lg font-medium">Oluştur</button>`;
    document.getElementById('globalModal').classList.remove('hidden');
}

function openEditTemplate(id) {
    axios.get(PROCESS_URLS.edit(id)).then(res => {
        document.getElementById('modalTitle').textContent = 'Şablonu Düzenle';
        document.getElementById('modalBody').innerHTML = res.data.html;
        document.getElementById('modalFooter').innerHTML = `
            <button onclick="closeModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
            <button onclick="updateTemplate(${id})" class="px-4 py-2 text-sm text-white bg-[#02E0FB] hover:bg-cyan-400 rounded-lg font-medium">Güncelle</button>`;
        document.getElementById('globalModal').classList.remove('hidden');
    });
}

function submitTemplate() {
    const name = document.getElementById('tplName')?.value;
    const type = document.getElementById('tplType')?.value;
    const description = document.getElementById('tplDescription')?.value;
    const steps = [...document.querySelectorAll('.tpl-step')].map(el => ({
        title: el.querySelector('[name="step_title"]')?.value,
        responsible: el.querySelector('[name="step_responsible"]')?.value,
        due_days: el.querySelector('[name="step_due"]')?.value,
    })).filter(s => s.title?.trim());

    if (!name) { toast('warning', 'Şablon adı zorunludur.'); return; }
    if (steps.length < 1) { toast('warning', 'En az 1 adım ekleyin.'); return; }

    axios.post(PROCESS_URLS.store, { name, type, description, steps }).then(res => {
        closeModal();
        toast('success', res.data.message);
        loadTemplates();
        loadKPIs();
    });
}

function updateTemplate(id) {
    const name = document.getElementById('tplName')?.value;
    const type = document.getElementById('tplType')?.value;
    const description = document.getElementById('tplDescription')?.value;
    const steps = [...document.querySelectorAll('.tpl-step')].map(el => ({
        title: el.querySelector('[name="step_title"]')?.value,
        responsible: el.querySelector('[name="step_responsible"]')?.value,
        due_days: el.querySelector('[name="step_due"]')?.value,
    })).filter(s => s.title?.trim());

    if (!name) { toast('warning', 'Şablon adı zorunludur.'); return; }
    if (steps.length < 1) { toast('warning', 'En az 1 adım ekleyin.'); return; }

    axios.put(PROCESS_URLS.update(id), { name, type, description, steps }).then(res => {
        closeModal();
        toast('success', res.data.message);
        loadTemplates();
        loadKPIs();
    });
}

// ─── Süreç Örnekleri ───
function switchTab(el) {
    document.querySelectorAll('.instance-tab').forEach(t => {
        t.classList.remove('text-gray-900', 'border-[#02E0FB]');
        t.classList.add('text-gray-500', 'border-transparent');
    });
    el.classList.remove('text-gray-500', 'border-transparent');
    el.classList.add('text-gray-900', 'border-[#02E0FB]');
    currentTab = el.dataset.status;
    currentPage = 1;
    loadInstances();
}

function loadInstances(page) {
    if (page) currentPage = page;
    const params = {
        page: currentPage,
        status: currentTab || undefined,
        search: document.getElementById('instanceSearch')?.value || undefined,
        type: document.getElementById('instanceTypeFilter')?.value || undefined,
        per_page: 10,
    };
    Object.keys(params).forEach(k => params[k] === undefined && delete params[k]);

    axios.get(PROCESS_URLS.instances, { params }).then(res => {
        renderInstances(res.data);
    });
}

function renderInstances(data) {
    const list = document.getElementById('instancesList');
    const info = document.getElementById('instanceTableInfo');
    const pages = document.getElementById('instancePages');

    info.textContent = data.total > 0 ? `${data.total} kayıttan ${data.data.length} gösteriliyor` : 'Kayıt bulunamadı';

    if (!data.data.length) {
        list.innerHTML = `
            <div class="text-center py-16 text-gray-400 bg-white rounded-xl border border-gray-100 shadow-sm">
                <p class="text-4xl mb-3">🔍</p>
                <p class="font-medium">Süreç bulunamadı</p>
                <p class="text-sm mt-1">Bir şablondan süreç başlatarak onboardingleri yönetin.</p>
            </div>`;
        pages.innerHTML = '';
        return;
    }

    list.innerHTML = data.data.map(i => {
        const progress = i.progress || 0;
        const initials = (i.personel?.first_name?.[0] || 'P') + (i.personel?.last_name?.[0] || '');
        const typeClass = i.template?.type === 'onboarding' ? 'bg-green-100 text-green-700' : (i.template?.type === 'offboarding' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700');
        const typeLabel = i.template?.type === 'onboarding' ? 'İşe Giriş' : (i.template?.type === 'offboarding' ? 'İşten Çıkış' : 'Özel Süreç');
        const steps = i.template?.steps || [];
        const completedSteps = i.completed_steps || [];
        const statusLabel = i.status === 'in_progress' ? 'Devam Ediyor' : (i.status === 'completed' ? 'Tamamlandı' : 'İptal');
        const statusClass = i.status === 'in_progress' ? 'bg-blue-100 text-blue-700' : (i.status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500');
        const isOverdue = i.status === 'in_progress' && i.due_date && new Date(i.due_date) < new Date();

        return `
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full ${i.status === 'completed' ? 'bg-green-100 text-green-600' : (isOverdue ? 'bg-red-100 text-red-600' : 'bg-[#02E0FB]/15 text-[#02E0FB]')} font-bold flex items-center justify-center text-sm">
                        ${initials}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <p class="font-semibold text-gray-800">${escHtml(i.personel?.first_name || '')} ${escHtml(i.personel?.last_name || '')}</p>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium ${statusClass}">${statusLabel}</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">
                            <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium ${typeClass} mr-1">${typeLabel}</span>
                            ${escHtml(i.template?.name || '')}
                            ${i.due_date ? ` · Son: ${new Date(i.due_date).toLocaleDateString('tr-TR')}` : ''}
                            ${isOverdue ? '<span class="text-red-500 font-medium ml-1">⏰ Gecikti!</span>' : ''}
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-black ${i.status === 'completed' ? 'text-green-500' : (isOverdue ? 'text-red-500' : 'text-[#02E0FB]')}">${progress}%</p>
                    <p class="text-xs text-gray-400">tamamlandı</p>
                </div>
            </div>

            <div class="h-2 bg-gray-100 rounded-full overflow-hidden mb-4">
                <div class="h-2 rounded-full transition-all ${i.status === 'completed' ? 'bg-green-500' : (isOverdue ? 'bg-red-500' : 'bg-gradient-to-r from-[#02E0FB] to-[#00b8d9]')}" style="width: ${progress}%"></div>
            </div>

            <div class="space-y-1.5">
                ${steps.map((step, si) => {
                    const done = completedSteps.includes(si);
                    const canComplete = i.status === 'in_progress' && !done;
                    return `
                    <div class="flex items-center gap-3 py-1">
                        <button onclick="${canComplete ? `completeStep(${i.id}, ${si})` : ''}"
                            class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors
                            ${done ? 'bg-green-500 border-green-500' : (canComplete ? 'border-gray-300 hover:border-[#02E0FB] cursor-pointer' : 'border-gray-200 cursor-not-allowed')}"
                            ${!canComplete ? 'disabled' : ''}>
                            ${done ? '<svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>' : ''}
                        </button>
                        <span class="text-sm ${done ? 'text-gray-400 line-through' : 'text-gray-700'}">${escHtml(step.title || '')}</span>
                        ${step.responsible ? `<span class="text-xs text-gray-400 ml-auto">${escHtml(step.responsible)}</span>` : ''}
                    </div>`;
                }).join('')}
            </div>
        </div>`;
    }).join('');

    renderPagination(data, pages);
}

function renderPagination(data, container) {
    if (data.last_page <= 1) { container.innerHTML = ''; return; }
    let html = '';
    if (data.current_page > 1) {
        html += `<button onclick="loadInstances(${data.current_page - 1})" class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-50">←</button>`;
    }
    for (let p = 1; p <= data.last_page; p++) {
        if (p === data.current_page) {
            html += `<span class="px-3 py-1.5 text-xs bg-[#02E0FB] text-white rounded-lg font-medium">${p}</span>`;
        } else if (p === 1 || p === data.last_page || Math.abs(p - data.current_page) <= 2) {
            html += `<button onclick="loadInstances(${p})" class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-50">${p}</button>`;
        } else if (Math.abs(p - data.current_page) === 3) {
            html += `<span class="px-1 text-xs text-gray-400">...</span>`;
        }
    }
    if (data.current_page < data.last_page) {
        html += `<button onclick="loadInstances(${data.current_page + 1})" class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-50">→</button>`;
    }
    container.innerHTML = html;
}

// ─── Süreç Başlat ───
function openInstantiate(templateId, templateName) {
    Swal.fire({
        title: `${escHtml(templateName)} — Süreç Başlat`,
        html: `
            <p class="text-sm text-gray-500 mb-3">Bu süreci hangi personel için başlatmak istiyorsunuz?</p>
            <select id="ipPersonel" class="swal2-input" style="display:flex">
                <option value="">— Personel seçin —</option>
            </select>
            <input type="date" id="ipDueDate" class="swal2-input" style="display:flex" placeholder="Bitiş tarihi (isteğe bağlı)" min="{{ today()->toDateString() }}">`,
        showCancelButton: true,
        confirmButtonColor: '#02E0FB',
        confirmButtonText: 'Başlat',
        cancelButtonText: 'İptal',
        didOpen: () => {
            axios.get('/admin/personel/list', { params: { per_page: 200 } }).then(res => {
                const sel = document.getElementById('ipPersonel');
                res.data.data.forEach(p => {
                    sel.innerHTML += `<option value="${p.id}">${escHtml(p.first_name)} ${escHtml(p.last_name)}</option>`;
                });
            });
        },
        preConfirm: () => {
            const personelId = document.getElementById('ipPersonel').value;
            if (!personelId) { Swal.showValidationMessage('Personel seçimi zorunludur.'); return false; }
            return { template_id: templateId, personel_id: personelId, due_date: document.getElementById('ipDueDate').value || null };
        }
    }).then(r => {
        if (r.isConfirmed) {
            axios.post(PROCESS_URLS.instantiate, r.value).then(res => {
                toast('success', res.data.message);
                loadInstances();
                loadKPIs();
            });
        }
    });
}

// ─── Adım Tamamlama ───
function completeStep(instanceId, stepIndex) {
    axios.post(PROCESS_URLS.complete(instanceId), { step_index: stepIndex }).then(res => {
        toast('success', 'Adım tamamlandı.');
        if (res.data.status === 'completed') {
            Swal.fire({ icon: 'success', title: '🎉 Süreç Tamamlandı!', text: 'Tüm adımlar başarıyla tamamlandı.' });
        }
        loadInstances();
        loadKPIs();
    });
}

// ─── Modal ───
function closeModal() {
    document.getElementById('globalModal').classList.add('hidden');
}

// ─── Yardımcı ───
function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}
</script>
@endpush
