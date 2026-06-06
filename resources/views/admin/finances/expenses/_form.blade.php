<form id="expenseForm" class="space-y-4" novalidate enctype="multipart/form-data">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Personel <span class="text-red-500">*</span></label>
            <div class="relative" id="expPersonelSelectWrap">
                <input type="text" id="expPersonelSearch" autocomplete="off" placeholder="Personel ara..."
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
                <input type="hidden" name="personel_id" id="expPersonelId" value="">
                <div id="expPersonelDropdown" class="hidden absolute z-50 top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto"></div>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Masraf Kategorisi <span class="text-red-500">*</span></label>
            <div class="flex gap-2">
                <select name="category_id" required id="expCategorySelect" onchange="checkCategoryLimit()"
                    class="flex-1 w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
                    <option value="">— Kategori seçin —</option>
                    @foreach($categories ?? [] as $c)
                        <option value="{{ $c->id }}" data-limit="{{ $c->limit_per_item }}" data-receipt="{{ $c->requires_receipt ? '1' : '0' }}">
                            {{ $c->name }} {{ $c->limit_per_item ? '(Max: '.number_format($c->limit_per_item, 2).' TRY)' : '' }}
                        </option>
                    @endforeach
                </select>
                <button type="button" onclick="openCreateCategoryModal()" title="Yeni Kategori Ekle"
                    class="shrink-0 px-3 py-2 text-sm font-medium text-white bg-gray-400 hover:bg-gray-500 rounded-lg transition-colors">+</button>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tutar <span class="text-red-500">*</span></label>
            <div class="flex gap-2">
                <input type="number" name="amount" required min="0.01" step="0.01" id="expAmountInput" onchange="checkCategoryLimit()"
                    class="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]"
                    placeholder="0.00">
                <select name="currency" class="w-24 px-2 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
                    <option value="TRY">TRY</option>
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                </select>
            </div>
            <div id="limitWarning" class="hidden mt-1 text-xs text-orange-600">⚠ Kategori limitini aşıyorsunuz.</div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Masraf Tarihi <span class="text-red-500">*</span></label>
            <input type="date" name="expense_date" required max="{{ today()->toDateString() }}"
                value="{{ today()->toDateString() }}"
                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Açıklama <span class="text-red-500">*</span></label>
        <textarea name="description" rows="3" required
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]"
            placeholder="Masrafın amacını ve detaylarını açıklayın..."></textarea>
    </div>

    {{-- Fiş/Belge Yükleme --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Fiş / Belge Yükle
            <span id="receiptRequired" class="hidden text-red-500 text-xs ml-1">(Bu kategori için zorunlu)</span>
        </label>
        <div id="fileDropZone"
            class="border-2 border-dashed border-gray-200 rounded-xl p-5 cursor-pointer text-center hover:border-[#02E0FB] hover:bg-[#02E0FB]/5 transition-all"
            onclick="document.getElementById('expAttachments').click()"
            ondragover="event.preventDefault(); this.classList.add('border-[#02E0FB]')"
            ondrop="handleDrop(event)">
            <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            <p class="text-sm text-gray-500">Tıklayın veya dosyayı buraya sürükleyin</p>
            <p class="text-xs text-gray-400 mt-1">PDF, JPG, PNG, WEBP — Max 10MB (birden fazla dosya yüklenebilir)</p>
        </div>
        <input type="file" id="expAttachments" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp" class="hidden"
            onchange="updateFileList(this.files)">
        <div id="fileList" class="mt-2 space-y-1"></div>
    </div>
</form>

<script>
function checkCategoryLimit() {
    const cat    = document.getElementById('expCategorySelect');
    const amount = parseFloat(document.getElementById('expAmountInput').value) || 0;
    const option = cat.options[cat.selectedIndex];
    const limit  = parseFloat(option?.dataset.limit) || 0;
    const receipt= option?.dataset.receipt === '1';
    const warn   = document.getElementById('limitWarning');
    const rreq   = document.getElementById('receiptRequired');

    if (limit && amount > limit) {
        warn.classList.remove('hidden');
    } else {
        warn.classList.add('hidden');
    }

    if (receipt) {
        rreq.classList.remove('hidden');
    } else {
        rreq.classList.add('hidden');
    }
}

function updateFileList(files) {
    const list = document.getElementById('fileList');
    list.innerHTML = Array.from(files).map((f, i) => `
        <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-lg text-sm">
            <svg class="w-4 h-4 text-[#02E0FB] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span class="flex-1 truncate text-gray-700">${f.name}</span>
            <span class="text-xs text-gray-400">${(f.size/1024/1024).toFixed(1)} MB</span>
        </div>`).join('');
}

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('expAttachments').files = e.dataTransfer.files;
    updateFileList(e.dataTransfer.files);
    e.currentTarget.classList.remove('border-[#02E0FB]');
}
</script>
