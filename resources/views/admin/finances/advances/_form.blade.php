<form id="advanceForm" class="space-y-4" novalidate>
    @csrf
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Personel <span class="text-red-500">*</span></label>
        <div class="relative" id="personelSelectWrap">
            <input type="text" id="personelSearchInput" autocomplete="off" placeholder="Personel ara..."
                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
            <input type="hidden" name="personel_id" id="personelIdInput" value="">
            <div id="personelDropdown" class="hidden absolute z-50 top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto"></div>
        </div>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Avans Tutarı <span class="text-red-500">*</span></label>
            <input type="number" name="amount" required min="1" step="0.01"
                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]"
                placeholder="0.00">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Para Birimi</label>
            <select name="currency" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
                <option value="TRY" selected>₺ TRY</option>
                <option value="USD">$ USD</option>
                <option value="EUR">€ EUR</option>
            </select>
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Avans Gerekçesi <span class="text-red-500">*</span></label>
        <textarea name="reason" rows="4" required
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]"
            placeholder="Avans talebinizin gerekçesini açıklayınız..."></textarea>
    </div>
</form>
