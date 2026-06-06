@php $service ??= null; @endphp
<form id="serviceForm" class="space-y-4">
    @if($service) @method('PUT') @endif

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Hizmet Adı <span class="text-red-500">*</span></label>
        <input type="text" name="name" value="{{ $service->name ?? '' }}" required
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Açıklama</label>
        <textarea name="description" rows="4"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">{{ $service->description ?? '' }}</textarea>
    </div>

    <div class="flex items-center gap-3">
        <input type="checkbox" name="is_active" value="1" id="isActive"
            {{ (!$service || $service->is_active) ? 'checked' : '' }}
            class="w-4 h-4 rounded border-gray-300 text-[#02E0FB] focus:ring-[#02E0FB]/30">
        <label for="isActive" class="text-sm font-medium text-gray-700">Aktif</label>
    </div>
</form>

<script>
document.getElementById('serviceForm')?.addEventListener('submit', function(e) { e.preventDefault(); });
</script>
