{{-- Süreç Şablon Form — date: 2026-03-15 --}}
<div class="space-y-4">
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Şablon Adı <span class="text-red-500">*</span></label>
            <input type="text" id="tplName" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]" placeholder="Örn: Standart İşe Giriş">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Süreç Türü</label>
            <select id="tplType" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
                <option value="onboarding">İşe Giriş (Onboarding)</option>
                <option value="offboarding">İşten Çıkış (Offboarding)</option>
                <option value="custom">Özel</option>
            </select>
        </div>
    </div>
    <div>
        <div class="flex items-center justify-between mb-2">
            <label class="text-sm font-medium text-gray-700">Süreç Adımları <span class="text-red-500">*</span></label>
            <button type="button" onclick="addTemplateStep()" class="text-xs text-[#02E0FB] hover:underline">+ Adım Ekle</button>
        </div>
        <div id="tplSteps" class="space-y-2">
            <div class="tpl-step bg-gray-50 rounded-lg p-3 grid grid-cols-3 gap-2">
                <input type="text" name="step_title" placeholder="Adım Başlığı *" class="col-span-1 px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
                <input type="text" name="step_responsible" placeholder="Sorumlu (IT, IK vb.)" class="col-span-1 px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
                <input type="number" name="step_due" placeholder="Gün" min="0" class="col-span-1 px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-1">Gün: İşlem başlangıcından kaç gün içinde tamamlanmalı</p>
    </div>
</div>
<script>
function addTemplateStep() {
    const div = document.createElement('div');
    div.className = 'tpl-step bg-gray-50 rounded-lg p-3 grid grid-cols-3 gap-2 relative';
    div.innerHTML = `
        <input type="text" name="step_title" placeholder="Adım Başlığı *" class="col-span-1 px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
        <input type="text" name="step_responsible" placeholder="Sorumlu" class="col-span-1 px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
        <div class="flex gap-1">
            <input type="number" name="step_due" placeholder="Gün" min="0" class="flex-1 px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
            <button type="button" onclick="this.closest('.tpl-step').remove()" class="px-2 text-red-400 hover:text-red-600">×</button>
        </div>`;
    document.getElementById('tplSteps').appendChild(div);
}
</script>
