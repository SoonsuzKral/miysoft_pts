<form id="leaveRequestForm" class="space-y-5" novalidate>
    @csrf

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
            Personel <span class="text-red-500">*</span>
        </label>
        <select name="personel_id" id="leavePersonelSelect" required
            class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#02E0FB] focus:ring-1 focus:ring-[#02E0FB]/20 transition-all"
            onchange="loadPersonelBalance()">
            <option value="">— Personel seçin —</option>
            @foreach($personels ?? [] as $p)
                <option value="{{ $p->id }}" {{ isset($leaveRequest) && $leaveRequest->personel_id == $p->id ? 'selected' : '' }}>
                    {{ $p->first_name }} {{ $p->last_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
            İzin Türü <span class="text-red-500">*</span>
        </label>
        <select name="leave_type_id" id="leaveTypeSelect" required
            class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#02E0FB] focus:ring-1 focus:ring-[#02E0FB]/20 transition-all"
            onchange="loadPersonelBalance()">
            <option value="">— İzin türü seçin —</option>
            @foreach($leaveTypes ?? [] as $lt)
                <option value="{{ $lt->id }}" data-paid="{{ $lt->paid ? '1' : '0' }}" data-approval="{{ $lt->requires_approval ? '1' : '0' }}"
                    {{ isset($leaveRequest) && $leaveRequest->leave_type_id == $lt->id ? 'selected' : '' }}>
                    {{ $lt->name }} @if($lt->paid)(Ücretli) @else(Ücretsiz) @endif
                    @if($lt->max_annual_days) — Max: {{ $lt->max_annual_days }} gün @endif
                </option>
            @endforeach
        </select>

        <div id="balanceInfo" class="hidden mt-2.5 p-3 bg-[#02E0FB]/5 border border-[#02E0FB]/20 rounded-xl text-xs">
            <div class="flex items-center justify-between">
                <span class="text-gray-500">Mevcut Bakiye:</span>
                <span class="font-bold text-[#02E0FB] text-sm" id="balanceRemaining">—</span>
            </div>
            <div class="flex items-center justify-between mt-1">
                <span class="text-gray-500">Kullanılan:</span>
                <span class="text-gray-700 font-medium" id="balanceUsed">—</span>
            </div>
        </div>

        <div id="autoApproveNote" class="hidden mt-2.5 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-700 font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Bu izin türü otomatik onaylanır.
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Başlangıç Tarihi <span class="text-red-500">*</span></label>
            <input type="date" name="start_date" id="leaveStartDate" required
                value="{{ isset($leaveRequest) ? $leaveRequest->start_date?->format('Y-m-d') : '' }}"
                min="{{ today()->toDateString() }}"
                class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#02E0FB] focus:ring-1 focus:ring-[#02E0FB]/20 transition-all"
                onchange="calculateDays()">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Bitiş Tarihi <span class="text-red-500">*</span></label>
            <input type="date" name="end_date" id="leaveEndDate" required
                value="{{ isset($leaveRequest) ? $leaveRequest->end_date?->format('Y-m-d') : '' }}"
                min="{{ today()->toDateString() }}"
                class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#02E0FB] focus:ring-1 focus:ring-[#02E0FB]/20 transition-all"
                onchange="calculateDays()">
        </div>
    </div>

    <div id="dayCountBox" class="hidden p-4 bg-gray-50 border border-gray-200 rounded-xl">
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">Hesaplanan iş günü:</span>
            <span class="font-bold text-gray-900 text-xl" id="calculatedDays">—</span>
        </div>
        <div id="holidayWarnings" class="hidden mt-2.5 space-y-1"></div>
        <div id="balanceWarning" class="hidden mt-2.5 p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            Bakiye yetersiz olabilir!
        </div>
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Açıklama / Gerekçe</label>
        <textarea name="reason" rows="3"
            class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#02E0FB] focus:ring-1 focus:ring-[#02E0FB]/20 transition-all"
            placeholder="İzin talebinizin gerekçesini yazın...">{{ $leaveRequest->reason ?? '' }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Belge / Ek (isteğe bağlı)</label>
        <div class="flex items-center justify-center w-full border-2 border-dashed border-gray-200 rounded-xl p-5 cursor-pointer hover:border-[#02E0FB] hover:bg-[#02E0FB]/5 transition-all"
            onclick="document.getElementById('leaveAttachment').click()">
            <div class="text-center">
                <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                <p class="text-xs text-gray-400">PDF, JPG, PNG — Max 5MB</p>
                <p class="text-xs text-[#02E0FB] font-semibold mt-1" id="attachmentName">Dosya seçmek için tıklayın</p>
            </div>
        </div>
        <input type="file" id="leaveAttachment" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="hidden"
            onchange="document.getElementById('attachmentName').textContent = this.files[0]?.name || 'Dosya seçmek için tıklayın'">
    </div>
</form>

<script>
function loadPersonelBalance() {
    const personelId = document.getElementById('leavePersonelSelect').value;
    const typeId = document.getElementById('leaveTypeSelect').value;
    const autoNote = document.getElementById('autoApproveNote');
    const balanceBox = document.getElementById('balanceInfo');
    const typeOption = document.querySelector(`#leaveTypeSelect option[value="${typeId}"]`);
    if (typeOption?.dataset.approval === '0') { autoNote.classList.remove('hidden'); } else { autoNote.classList.add('hidden'); }
    if (!personelId || !typeId) { balanceBox.classList.add('hidden'); return; }
    const year = new Date().getFullYear();
    axios.get(`/admin/leave/balances?personel_id=${personelId}&leave_type_id=${typeId}&year=${year}&per_page=1`)
        .then(res => {
            const balance = res.data.data?.[0];
            if (balance) {
                document.getElementById('balanceRemaining').textContent = `${balance.remaining_days} gün`;
                document.getElementById('balanceUsed').textContent = `${balance.used_days} gün`;
                balanceBox.classList.remove('hidden');
            } else { balanceBox.classList.add('hidden'); }
        }).catch(() => balanceBox.classList.add('hidden'));
}

function calculateDays() {
    const start = document.getElementById('leaveStartDate').value;
    const end = document.getElementById('leaveEndDate').value;
    const box = document.getElementById('dayCountBox');
    const warning = document.getElementById('balanceWarning');
    const hwarn = document.getElementById('holidayWarnings');
    if (!start || !end || new Date(start) > new Date(end)) { box.classList.add('hidden'); return; }
    let count = 0;
    const s = new Date(start), e = new Date(end);
    for (let d = new Date(s); d <= e; d.setDate(d.getDate() + 1)) { const day = d.getDay(); if (day !== 0 && day !== 6) count++; }
    document.getElementById('calculatedDays').textContent = count + ' gün (hafta içi)';
    box.classList.remove('hidden');
    const remaining = parseFloat(document.getElementById('balanceRemaining')?.textContent) || Infinity;
    if (count > remaining) { warning.classList.remove('hidden'); } else { warning.classList.add('hidden'); }
    axios.get('{{ route("admin.leave.validate-dates") }}', { params: { start_date: start, end_date: end } })
        .then(res => {
            if (res.data.warnings.length) {
                hwarn.classList.remove('hidden');
                hwarn.innerHTML = res.data.warnings.map(w => '<div class="p-2 bg-yellow-50 border border-yellow-200 rounded-lg text-xs text-yellow-700 font-medium">' + w + '</div>').join('');
                document.getElementById('calculatedDays').textContent = res.data.work_days + ' gün (iş günü)';
            } else {
                hwarn.classList.add('hidden');
            }
        }).catch(() => { hwarn.classList.add('hidden'); });
}
</script>
