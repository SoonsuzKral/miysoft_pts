<div class="space-y-4">
    <input type="hidden" id="tplId" value="{{ $processTemplate->id ?? '' }}">
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Şablon Adı <span class="text-red-500">*</span></label>
            <input type="text" id="tplName" value="{{ $processTemplate->name ?? '' }}"
                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]" placeholder="Örn: Standart İşe Giriş">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Süreç Türü</label>
            <select id="tplType" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
                <option value="onboarding" {{ isset($processTemplate) && $processTemplate->type === 'onboarding' ? 'selected' : '' }}>İşe Giriş (Onboarding)</option>
                <option value="offboarding" {{ isset($processTemplate) && $processTemplate->type === 'offboarding' ? 'selected' : '' }}>İşten Çıkış (Offboarding)</option>
                <option value="custom" {{ isset($processTemplate) && $processTemplate->type === 'custom' ? 'selected' : '' }}>Özel</option>
            </select>
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Açıklama</label>
        <textarea id="tplDescription" rows="2" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]" placeholder="Şablon açıklaması (opsiyonel)">{{ $processTemplate->description ?? '' }}</textarea>
    </div>
    <div>
        <div class="flex items-center justify-between mb-2">
            <label class="text-sm font-medium text-gray-700">Süreç Adımları <span class="text-red-500">*</span></label>
            <button type="button" onclick="addTemplateStep()" class="text-xs text-[#02E0FB] hover:underline font-medium">+ Adım Ekle</button>
        </div>
        <div id="tplSteps" class="space-y-2">
            @if(isset($processTemplate) && $processTemplate->steps)
                @foreach($processTemplate->steps as $i => $step)
                <div class="tpl-step bg-gray-50 rounded-lg p-3 grid grid-cols-3 gap-2">
                    <input type="text" name="step_title" value="{{ $step['title'] ?? '' }}" placeholder="Adım Başlığı *" class="col-span-1 px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
                    <input type="text" name="step_responsible" value="{{ $step['responsible'] ?? '' }}" placeholder="Sorumlu (IT, IK vb.)" class="col-span-1 px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
                    <div class="flex gap-1">
                        <input type="number" name="step_due" value="{{ $step['due_days'] ?? '' }}" placeholder="Gün" min="0" class="flex-1 px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
                        <button type="button" onclick="this.closest('.tpl-step').remove()" class="px-2 text-red-400 hover:text-red-600">×</button>
                    </div>
                </div>
                @endforeach
            @else
            <div class="tpl-step bg-gray-50 rounded-lg p-3 grid grid-cols-3 gap-2">
                <input type="text" name="step_title" placeholder="Adım Başlığı *" class="col-span-1 px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
                <input type="text" name="step_responsible" placeholder="Sorumlu (IT, IK vb.)" class="col-span-1 px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
                <input type="number" name="step_due" placeholder="Gün" min="0" class="col-span-1 px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
            </div>
            @endif
        </div>
        <p class="text-xs text-gray-400 mt-1">Gün: İşlem başlangıcından kaç gün içinde tamamlanmalı</p>
    </div>
</div>
