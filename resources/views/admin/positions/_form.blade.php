<form id="positionForm" class="space-y-4" novalidate>
    @csrf

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Pozisyon Adı <span class="text-red-500">*</span></label>
        <input type="text" name="title" value="{{ $position->title ?? '' }}" required
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB] focus:ring-1 focus:ring-[#02E0FB]"
            placeholder="Örn: Yazılım Geliştirici">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Pozisyon Kodu</label>
        <input type="text" name="code" value="{{ $position->code ?? '' }}"
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB] font-mono"
            placeholder="YD-001" maxlength="50">
        <p class="mt-1 text-xs text-gray-400">Kısa tanımlayıcı (opsiyonel)</p>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Seviye (Level)</label>
            <input type="number" name="level" value="{{ $position->level ?? '' }}" min="1" max="99"
                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]"
                placeholder="1">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Maaş Derecesi</label>
            <input type="text" name="salary_grade" value="{{ $position->salary_grade ?? '' }}"
                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]"
                placeholder="A-1" maxlength="20">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Açıklama</label>
        <textarea name="description" rows="3"
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]"
            placeholder="Pozisyon açıklaması...">{{ $position->description ?? '' }}</textarea>
    </div>

    <div class="flex items-center gap-3">
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                {{ !isset($position) || $position->is_active ? 'checked' : '' }}>
            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#02E0FB] transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
        </label>
        <span class="text-sm text-gray-700">Aktif pozisyon</span>
    </div>
</form>
