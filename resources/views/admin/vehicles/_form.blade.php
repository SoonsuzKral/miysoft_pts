@php $vehicle ??= null; @endphp
<form id="vehicleForm" class="space-y-4">
    @if($vehicle) @method('PUT') @endif

    <div class="grid grid-cols-3 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Plaka <span class="text-red-500">*</span></label>
            <input type="text" name="plate" value="{{ $vehicle->plate ?? '' }}" required
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Marka <span class="text-red-500">*</span></label>
            <input type="text" name="brand" value="{{ $vehicle->brand ?? '' }}" required list="brandList"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
            <datalist id="brandList">
                <option value="BMW"><option value="Citroen"><option value="Fiat"><option value="Ford">
                <option value="Honda"><option value="Hyundai"><option value="Mercedes"><option value="Nissan">
                <option value="Opel"><option value="Peugeot"><option value="Renault"><option value="Skoda">
                <option value="Subaru"><option value="Suzuki"><option value="Toyota"><option value="Volkswagen">
                <option value="Volvo">
            </datalist>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Model <span class="text-red-500">*</span></label>
            <input type="text" name="model" value="{{ $vehicle->model ?? '' }}" required
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
        </div>
    </div>

    <div class="grid grid-cols-3 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Yıl</label>
            <input type="number" name="year" value="{{ $vehicle->year ?? '' }}" min="1900" max="2099"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Renk</label>
            <input type="text" name="color" value="{{ $vehicle->color ?? '' }}"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Şasi No (VIN)</label>
            <input type="text" name="vin" value="{{ $vehicle->vin ?? '' }}"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Motor Türü</label>
            <select name="engine_type"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
                <option value="">Seçiniz</option>
                <option value="benzin" {{ $vehicle && $vehicle->engine_type == 'benzin' ? 'selected' : '' }}>Benzin</option>
                <option value="dizel" {{ $vehicle && $vehicle->engine_type == 'dizel' ? 'selected' : '' }}>Dizel</option>
                <option value="elektrik" {{ $vehicle && $vehicle->engine_type == 'elektrik' ? 'selected' : '' }}>Elektrik</option>
                <option value="hibrit" {{ $vehicle && $vehicle->engine_type == 'hibrit' ? 'selected' : '' }}>Hibrit</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Yakıt Türü</label>
            <select name="fuel_type"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
                <option value="">Seçiniz</option>
                <option value="benzin" {{ $vehicle && $vehicle->fuel_type == 'benzin' ? 'selected' : '' }}>Benzin</option>
                <option value="dizel" {{ $vehicle && $vehicle->fuel_type == 'dizel' ? 'selected' : '' }}>Dizel</option>
                <option value="lpg" {{ $vehicle && $vehicle->fuel_type == 'lpg' ? 'selected' : '' }}>LPG</option>
                <option value="elektrik" {{ $vehicle && $vehicle->fuel_type == 'elektrik' ? 'selected' : '' }}>Elektrik</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Motor Hacmi (L)</label>
            <input type="number" step="0.1" min="0" max="99" name="engine_capacity" value="{{ $vehicle->engine_capacity ?? '' }}"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ort. Yakıt Tüketimi (L/100km)</label>
            <input type="number" step="0.1" min="0" max="99" name="fuel_consumption_avg" value="{{ $vehicle->fuel_consumption_avg ?? '' }}"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Depo Kapasitesi (L)</label>
            <input type="number" step="0.1" min="0" max="9999" name="fuel_tank_capacity" value="{{ $vehicle->fuel_tank_capacity ?? '' }}"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
        </div>
    </div>

    <div class="border-t border-gray-100 pt-4">
        <h4 class="text-sm font-semibold text-gray-700 mb-3">KM & Bakım Bilgileri</h4>
        <div class="grid grid-cols-3 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mevcut KM</label>
                <input type="number" step="1" min="0" name="current_km" value="{{ $vehicle->current_km ?? '' }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Son Bakım KM</label>
                <input type="number" step="1" min="0" name="last_maintenance_km" value="{{ $vehicle->last_maintenance_km ?? '' }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Durum</label>
                <select name="status"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
                    <option value="active" {{ !$vehicle || $vehicle->status == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="maintenance" {{ $vehicle && $vehicle->status == 'maintenance' ? 'selected' : '' }}>Bakımda</option>
                    <option value="out_of_service" {{ $vehicle && $vehicle->status == 'out_of_service' ? 'selected' : '' }}>Hizmet Dışı</option>
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 mt-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Son Bakım Tarihi</label>
                <input type="date" name="last_maintenance_date" value="{{ $vehicle->last_maintenance_date ?? '' }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sonraki Bakım Tarihi</label>
                <input type="date" name="next_maintenance_date" value="{{ $vehicle->next_maintenance_date ?? '' }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
            </div>
        </div>
    </div>

    <div class="border-t border-gray-100 pt-4">
        <h4 class="text-sm font-semibold text-gray-700 mb-3">Sigorta & Muayene</h4>
        <div class="grid grid-cols-3 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sigorta Bitiş</label>
                <input type="date" name="insurance_date" value="{{ $vehicle->insurance_date ?? '' }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Muayene Bitiş</label>
                <input type="date" name="traffic_date" value="{{ $vehicle->traffic_date ?? '' }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Egzoz Muayene</label>
                <input type="date" name="examination_date" value="{{ $vehicle->examination_date ?? '' }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
            </div>
        </div>
    </div>

    <div class="border-t border-gray-100 pt-4">
        <h4 class="text-sm font-semibold text-gray-700 mb-3">Atama & Alım</h4>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Atanan Personel / Şoför</label>
                <div class="relative" id="personelSelectWrap">
                    <input type="text" id="personelSearchInput" autocomplete="off" placeholder="Personel ara..."
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
                    <input type="hidden" name="assigned_personel_id" id="personelIdInput" value="{{ $vehicle->assigned_personel_id ?? '' }}">
                    <div id="personelDropdown" class="hidden absolute z-50 top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto"></div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Alım Bedeli (₺)</label>
                <input type="number" step="0.01" min="0" name="acquisition_cost" value="{{ $vehicle->acquisition_cost ?? '' }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 mt-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Alım Tarihi</label>
                <input type="date" name="acquisition_date" value="{{ $vehicle->acquisition_date ?? '' }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
            </div>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Notlar</label>
        <textarea name="notes" rows="3"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">{{ $vehicle->notes ?? '' }}</textarea>
    </div>
</form>

<script>
document.getElementById('vehicleForm')?.addEventListener('submit', function(e) { e.preventDefault(); });
</script>