@php $location ??= null; @endphp
<form id="locationForm" class="space-y-4">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Konum Adı <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ $location->name ?? '' }}" required
                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB] focus:ring-1 focus:ring-[#02E0FB]"
                placeholder="Örn: Merkez Ofis, Depo 1, Fabrika">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Konum Türü</label>
            <div class="flex gap-2">
                <select name="location_type_id" id="locationTypeSelect" class="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
                    <option value="">— Seçin —</option>
                    @foreach($types as $t)
                    <option value="{{ $t->id }}" {{ isset($location) && $location->location_type_id == $t->id ? 'selected' : '' }}
                        data-icon="{{ $t->icon }}" data-color="{{ $t->color }}">
                        {{ $t->icon }} {{ $t->name }}
                    </option>
                    @endforeach
                </select>
                <button type="button" onclick="toggleNewTypeForm()"
                    class="shrink-0 px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 text-gray-500"
                    title="Yeni tür ekle">+</button>
            </div>
            <div id="newTypeForm" class="hidden mt-2 p-3 bg-gray-50 border border-gray-200 rounded-lg space-y-2">
                <input type="text" id="newTypeName" placeholder="Tür adı"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
                <div class="flex gap-2">
                    <input type="text" id="newTypeIcon" placeholder="📍 Emoji" maxlength="5"
                        class="w-20 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB] text-center">
                    <input type="color" id="newTypeColor" value="#6B7280"
                        class="flex-1 h-10 px-1 border border-gray-200 rounded-lg cursor-pointer">
                    <button type="button" onclick="saveNewType()"
                        class="px-4 py-2 text-sm bg-[#02E0FB] text-white rounded-lg hover:bg-[#02c8e0]">Ekle</button>
                    <button type="button" onclick="toggleNewTypeForm()"
                        class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">İptal</button>
                </div>
            </div>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Adres</label>
            <input type="text" name="address" value="{{ $location->address ?? '' }}"
                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]"
                placeholder="Açık adres girin...">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Şehir</label>
            <input type="text" name="city" value="{{ $location->city ?? '' }}"
                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]"
                placeholder="İstanbul">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">İlçe</label>
            <input type="text" name="district" value="{{ $location->district ?? '' }}"
                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]"
                placeholder="Kadıköy">
        </div>
    </div>

    {{-- Harita --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Konum Seç <span class="text-red-500">*</span></label>
        <div class="relative mb-2">
            <input type="text" id="addressSearch" placeholder="Adres ara (örn: İstiklal Cad. No:1, Taksim)..."
                class="w-full px-3 py-2.5 pl-9 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB] focus:ring-1 focus:ring-[#02E0FB]">
            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <div id="addressSearchResults" class="hidden absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg z-10 max-h-48 overflow-y-auto"></div>
        </div>
        <div id="formMap" style="height: 300px; border-radius: 12px; border: 2px solid #e5e7eb; z-index: 1;"></div>
        <p class="text-xs text-gray-400 mt-1">Haritada tıklayarak, işaretçiyi sürükleyerek veya adres arayarak konum seçin.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Enlem (Latitude)</label>
            <input type="text" id="locLatitude" name="latitude" value="{{ $location->latitude ?? '' }}" readonly
                class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg font-mono text-gray-600">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Boylam (Longitude)</label>
            <input type="text" id="locLongitude" name="longitude" value="{{ $location->longitude ?? '' }}" readonly
                class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg font-mono text-gray-600">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Geçerli Alan (metre)</label>
            <input type="number" name="radius" value="{{ $location->radius ?? 50 }}" min="10" max="5000"
                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
            <p class="text-[10px] text-gray-400 mt-0.5">Giriş/çıkış için izin verilen mesafe (10-5000m)</p>
        </div>
    </div>

    <div id="streetViewContainer" class="hidden">
        <button type="button" onclick="openStreetView()" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            <span>Sokak Görünümü'nde Aç</span>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Renk</label>
            <input type="color" name="color" value="{{ $location->color ?? '#02E0FB' }}"
                class="w-full h-10 px-1 border border-gray-200 rounded-lg cursor-pointer">
        </div>
        <div class="flex items-end pb-2">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ !isset($location) || $location->is_active ? 'checked' : '' }}
                    class="rounded border-gray-300 text-[#02E0FB] focus:ring-[#02E0FB]">
                <span class="text-sm text-gray-700">Aktif</span>
            </label>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Açıklama</label>
        <textarea name="description" rows="2" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]"
            placeholder="Konum hakkında notlar...">{{ $location->description ?? '' }}</textarea>
    </div>
</form>
