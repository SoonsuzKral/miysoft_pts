@php $travel ??= null; @endphp
<form id="travelForm" class="space-y-4">
    @if($travel) @method('PUT') @endif

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Personel <span class="text-red-500">*</span></label>
        <div class="relative" id="personelSelectWrap">
            <input type="text" id="personelSearchInput" autocomplete="off" placeholder="Personel ara..."
                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
            <input type="hidden" name="personel_id" id="personelIdInput" value="{{ $travel->personel_id ?? '' }}">
            <div id="personelDropdown" class="hidden absolute z-50 top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto"></div>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Gidilecek Yer <span class="text-red-500">*</span></label>
        <input type="text" name="destination" value="{{ $travel->destination ?? '' }}" required
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Gidiş Tarihi <span class="text-red-500">*</span></label>
            <input type="date" name="departure_date" value="{{ $travel->departure_date ?? '' }}" required
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dönüş Tarihi <span class="text-red-500">*</span></label>
            <input type="date" name="return_date" value="{{ $travel->return_date ?? '' }}" required
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Amaç</label>
        <textarea name="purpose" rows="3"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">{{ $travel->purpose ?? '' }}</textarea>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ulaşım Şekli</label>
            <select name="transportation_mode"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
                <option value="">Seçiniz</option>
                <option value="uçak" {{ $travel && $travel->transportation_mode == 'uçak' ? 'selected' : '' }}>Uçak</option>
                <option value="otobüs" {{ $travel && $travel->transportation_mode == 'otobüs' ? 'selected' : '' }}>Otobüs</option>
                <option value="tren" {{ $travel && $travel->transportation_mode == 'tren' ? 'selected' : '' }}>Tren</option>
                <option value="özel_araç" {{ $travel && $travel->transportation_mode == 'özel_araç' ? 'selected' : '' }}>Özel Araç</option>
                <option value="diğer" {{ $travel && $travel->transportation_mode == 'diğer' ? 'selected' : '' }}>Diğer</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Konaklama</label>
            <input type="text" name="accommodation" value="{{ $travel->accommodation ?? '' }}"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tahmini Maliyet</label>
            <input type="number" step="0.01" min="0" name="estimated_cost" value="{{ $travel->estimated_cost ?? '' }}"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Para Birimi</label>
            <select name="currency"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
                <option value="TRY" {{ $travel && $travel->currency == 'TRY' ? 'selected' : '' }}>₺ TRY</option>
                <option value="USD" {{ $travel && $travel->currency == 'USD' ? 'selected' : '' }}>$ USD</option>
                <option value="EUR" {{ $travel && $travel->currency == 'EUR' ? 'selected' : '' }}>€ EUR</option>
            </select>
        </div>
    </div>
</form>

<script>
document.getElementById('travelForm')?.addEventListener('submit', function(e) { e.preventDefault(); });
</script>