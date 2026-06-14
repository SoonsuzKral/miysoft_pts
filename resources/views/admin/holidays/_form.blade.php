@php $holiday ??= null; @endphp
<form id="holidayForm" class="space-y-4">
    @if($holiday) @method('PUT') @endif

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tatil Adı <span class="text-red-500">*</span></label>
        <input type="text" name="name" value="{{ $holiday->name ?? '' }}" required
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tarih <span class="text-red-500">*</span></label>
        <input type="date" name="date" value="{{ $holiday ? $holiday->date->format('Y-m-d') : '' }}" required
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tür</label>
        <select name="type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
            <option value="national" {{ ($holiday->is_national ?? true) ? 'selected' : '' }}>Resmi Tatil</option>
            <option value="religious" {{ (($holiday->is_national ?? false) === false && ($holiday->country_code ?? '') === 'TR') ? 'selected' : '' }}>Dini Bayram</option>
            <option value="custom" {{ (($holiday->company_id ?? false) && ($holiday->is_national ?? false) === false) ? 'selected' : '' }}>Şirket Tatili</option>
        </select>
    </div>
</form>

<script>
document.getElementById('holidayForm')?.addEventListener('submit', function(e) { e.preventDefault(); });
</script>
