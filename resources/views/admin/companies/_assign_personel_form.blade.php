<form id="assignPersonelForm" class="space-y-4" novalidate>
    @csrf

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Departman <span class="text-red-500">*</span></label>
        <select id="assignDeptSelect" name="department_id" required
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]"
            onchange="filterAssignPersonels()">
            <option value="">— Departman secin —</option>
            @foreach($departments ?? [] as $dept)
                <option value="{{ $dept->id }}" {{ ($departmentId ?? '') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex items-center gap-3 border-b border-gray-100 pb-3">
        <button type="button" onclick="showUnassigned()"
            class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors"
            id="tabUnassignedBtn"
            style="background:#02E0FB;color:white">
            Atanmamis Personeller
        </button>
        <button type="button" onclick="showDeptPersonels()"
            class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors bg-gray-100 text-gray-600 hover:bg-gray-200"
            id="tabDeptBtn">
            Departmandakiler
        </button>
    </div>

    {{-- Atanmamis Personeller --}}
    <div id="unassignedPanel">
        <label class="block text-sm font-medium text-gray-700 mb-2">Atanacak Personeller</label>
        @if($unassignedPersonels->isEmpty())
            <p class="text-sm text-gray-400 text-center py-4">Atanmamis personel bulunmuyor.</p>
        @else
            <div class="max-h-60 overflow-y-auto space-y-1.5 border border-gray-100 rounded-lg p-2">
                @foreach($unassignedPersonels as $p)
                <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="personel_ids[]" value="{{ $p->id }}"
                        class="w-4 h-4 text-[#02E0FB] border-gray-300 rounded focus:ring-[#02E0FB]">
                    <span class="text-sm text-gray-700">{{ $p->first_name }} {{ $p->last_name }}</span>
                </label>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Departmandaki Personeller --}}
    <div id="deptPersonelsPanel" class="hidden">
        <label class="block text-sm font-medium text-gray-700 mb-2">Departmandaki Personeller</label>
        @if(isset($deptPersonels) && $deptPersonels->isNotEmpty())
            <div class="max-h-60 overflow-y-auto space-y-1.5 border border-gray-100 rounded-lg p-2">
                @foreach($deptPersonels as $p)
                <div class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50">
                    <span class="text-sm text-gray-700">{{ $p->first_name }} {{ $p->last_name }}</span>
                    <button type="button" onclick="removeFromDept({{ $p->id }}, '{{ $p->first_name }} {{ $p->last_name }}')"
                        class="text-xs text-red-500 hover:text-red-700 font-medium">Cikar</button>
                </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-400 text-center py-4">
                {{ isset($departmentId) ? 'Bu departmanda personel bulunmuyor.' : 'Once departman secin.' }}
            </p>
        @endif
    </div>
</form>
