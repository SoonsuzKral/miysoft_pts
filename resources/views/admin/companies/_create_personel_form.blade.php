<form id="companyPersonelForm" class="space-y-4" novalidate>
    @csrf

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ad <span class="text-red-500">*</span></label>
            <input type="text" name="first_name" required
                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB] focus:ring-1 focus:ring-[#02E0FB]"
                placeholder="Personel adi">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Soyad <span class="text-red-500">*</span></label>
            <input type="text" name="last_name" required
                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB] focus:ring-1 focus:ring-[#02E0FB]"
                placeholder="Personel soyadi">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">E-posta</label>
            <input type="email" name="email"
                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]"
                placeholder="ornek@sirket.com">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
            <input type="text" name="phone"
                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]"
                placeholder="+90 555 000 0000">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Departman</label>
            <select name="department_id" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
                <option value="">— Seciniz —</option>
                @foreach($departments ?? [] as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Pozisyon</label>
            <select name="position_id" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
                <option value="">— Seciniz —</option>
                @foreach($positions ?? [] as $pos)
                    <option value="{{ $pos->id }}">{{ $pos->title }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ise Baslama Tarihi</label>
            <input type="date" name="hire_date"
                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Durum</label>
            <select name="status" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#02E0FB]">
                <option value="active">Aktif</option>
                <option value="on_leave">Izinde</option>
                <option value="suspended">Askida</option>
            </select>
        </div>
    </div>
</form>
