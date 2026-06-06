@php $visitor ??= null; @endphp
<form id="visitorForm" class="space-y-4">
    @if($visitor) @method('PUT') @endif
    <input type="hidden" name="visit_date" value="{{ $visitor->visit_date ?? now()->format('Y-m-d') }}">

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Ad Soyad <span class="text-red-500">*</span></label>
        <input type="text" name="name" value="{{ $visitor->name ?? '' }}" required
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Firma</label>
        <input type="text" name="visitor_company" value="{{ $visitor->visitor_company ?? '' }}"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
            <input type="text" name="phone" value="{{ $visitor->phone ?? '' }}"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">E-posta</label>
            <input type="email" name="email" value="{{ $visitor->email ?? '' }}"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Ziyaret Edilecek Kişi</label>
        <select name="host_personel_id"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
            <option value="">Seçiniz</option>
            @foreach($personels as $p)
                <option value="{{ $p->id }}" {{ $visitor && $visitor->host_personel_id == $p->id ? 'selected' : '' }}>
                    {{ $p->first_name }} {{ $p->last_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kimlik No</label>
            <input type="text" name="document_no_enc" value="{{ $visitor ? $visitor->document_no_decrypted : '' }}"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Belge Türü</label>
            <select name="document_type"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">
                <option value="">Seçiniz</option>
                <option value="tc" {{ $visitor && $visitor->document_type == 'tc' ? 'selected' : '' }}>TC Kimlik</option>
                <option value="passport" {{ $visitor && $visitor->document_type == 'passport' ? 'selected' : '' }}>Pasaport</option>
                <option value="other" {{ $visitor && $visitor->document_type == 'other' ? 'selected' : '' }}>Diğer</option>
            </select>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Ziyaret Amacı</label>
        <textarea name="purpose" rows="3"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#02E0FB]/30 focus:border-[#02E0FB]">{{ $visitor->purpose ?? '' }}</textarea>
    </div>
</form>

<script>
document.getElementById('visitorForm')?.addEventListener('submit', function(e) { e.preventDefault(); });
</script>
