<div class="bg-white rounded-2xl border border-gray-100 shadow-lg overflow-hidden" x-data="{ tab: 'info' }" data-personel-id="{{ $personel->id }}">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 px-6 py-6 relative">
        <div class="absolute top-0 right-0 w-48 h-48 bg-[#02E0FB]/5 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>
        <button onclick="document.getElementById('personelCardArea').classList.add('hidden')"
            class="absolute top-4 right-4 text-gray-500 hover:text-white hover:bg-white/10 rounded-xl p-1.5 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#02E0FB] to-cyan-500 flex items-center justify-center text-white text-2xl font-bold shadow-lg shadow-[#02E0FB]/20">
                {{ strtoupper(substr($personel->first_name, 0, 1) . substr($personel->last_name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">{{ $personel->full_name }}</h2>
                <p class="text-[#02E0FB] font-medium text-sm mt-0.5">{{ $personel->position?->title ?? 'Pozisyon Belirtilmemiş' }}</p>
                <p class="text-gray-400 text-xs mt-0.5">{{ $personel->department?->name ?? 'Departman Belirtilmemiş' }}</p>
                <div class="mt-2 flex gap-2">
                    @php
                        $statusMap = ['active' => ['Aktif', 'bg-emerald-500/20 text-emerald-400'], 'on_leave' => ['İzinde', 'bg-yellow-500/20 text-yellow-400'], 'terminated' => ['Ayrılmış', 'bg-red-500/20 text-red-400'], 'suspended' => ['Askıda', 'bg-gray-500/20 text-gray-400']];
                        $st = $statusMap[$personel->status] ?? [$personel->status, 'bg-gray-500/20 text-gray-400'];
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $st[1] }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $personel->status === 'active' ? 'bg-emerald-400' : ($personel->status === 'on_leave' ? 'bg-yellow-400' : ($personel->status === 'terminated' ? 'bg-red-400' : 'bg-gray-400')) }}"></span>
                        {{ $st[0] }}
                    </span>
                    @if($personel->is_active)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400">Aktif Hesap</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="border-b border-gray-100 px-6 overflow-x-auto">
        <div class="flex gap-4 -mb-px min-w-max">
            @foreach([
                ['info', 'Genel Bilgiler', 'M20 12H4', null],
                ['contact', 'İletişim', 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8', null],
                ['leaves', 'İzinler', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', null],
                ['attendance', 'Puantaj', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', null],
                ['assets', 'Zimmetler', 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', null],
                ['docs', 'Belgeler', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'loadBelgeler'],
                ['activity', 'Aktivite', 'M13 10V3L4 14h7v7l9-11h-7z', null],
            ] as [$key, $label, $icon, $onShow])
            <button @click="tab = '{{ $key }}'; {{ $onShow ? $onShow . '()' : '' }}"
                :class="tab === '{{ $key }}' ? 'border-[#02E0FB] text-[#02E0FB]' : 'border-transparent text-gray-400 hover:text-gray-600'"
                class="py-3 px-1 text-xs font-semibold border-b-2 transition-all whitespace-nowrap flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- Tab Contents --}}
    <div class="p-6">

        {{-- INFO TAB --}}
        <div x-show="tab === 'info'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wider flex items-center gap-2">
                    <span class="w-5 h-5 rounded bg-blue-50 flex items-center justify-center"><svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></span>
                    Kişisel Bilgiler
                </h3>
                <div class="bg-gray-50/50 rounded-xl p-4 space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-gray-400">Ad Soyad</span><span class="text-gray-800 font-medium">{{ $personel->full_name }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Doğum Tarihi</span><span class="text-gray-800 font-medium">{{ $personel->birth_date?->format('d.m.Y') ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Cinsiyet</span><span class="text-gray-800 font-medium">{{ ['M'=>'Erkek','F'=>'Kadın','other'=>'Diğer'][$personel->gender] ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Kan Grubu</span><span class="text-gray-800 font-medium">{{ $personel->blood_type ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">TC Kimlik</span><span class="text-gray-800 font-medium font-mono">{{ $personel->masked_national_id }}</span></div>
                </div>
            </div>
            <div class="space-y-4">
                <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wider flex items-center gap-2">
                    <span class="w-5 h-5 rounded bg-emerald-50 flex items-center justify-center"><svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></span>
                    İş Bilgileri
                </h3>
                <div class="bg-gray-50/50 rounded-xl p-4 space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-gray-400">Departman</span><span class="text-gray-800 font-medium">{{ $personel->department?->name ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Pozisyon</span><span class="text-gray-800 font-medium">{{ $personel->position?->title ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">İşe Giriş</span><span class="text-gray-800 font-medium">{{ $personel->hire_date?->format('d.m.Y') ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Kıdem</span><span class="text-gray-800 font-medium">{{ $personel->hire_date ? $personel->hire_date->diffForHumans(now(), ['parts' => 2, 'short' => false]) : '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Maaş</span><span class="text-gray-800 font-medium">{{ $personel->salary ? number_format($personel->salary, 2, ',', '.') . ' ' . ($personel->currency ?? 'TRY') : '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Durum</span><span class="text-gray-800 font-medium">{{ $st[0] }}</span></div>
                    @if($personel->termination_date)
                    <div class="flex justify-between"><span class="text-gray-400">Çıkış Tarihi</span><span class="text-red-500 font-medium">{{ $personel->termination_date?->format('d.m.Y') }}</span></div>
                    @endif
                </div>
            </div>
        </div>

        {{-- CONTACT TAB --}}
        <div x-show="tab === 'contact'" class="max-w-md">
            <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wider flex items-center gap-2 mb-4">
                <span class="w-5 h-5 rounded bg-purple-50 flex items-center justify-center"><svg class="w-3 h-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg></span>
                İletişim Bilgileri
            </h3>
            <div class="bg-gray-50/50 rounded-xl p-5 space-y-4">
                <div class="flex items-center gap-3 text-sm">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center shrink-0"><svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></div>
                    <div><p class="text-xs text-gray-400">E-posta</p><p class="font-medium text-gray-800">{{ $personel->email ?? '—' }}</p></div>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <div class="w-9 h-9 rounded-xl bg-green-50 flex items-center justify-center shrink-0"><svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg></div>
                    <div><p class="text-xs text-gray-400">Telefon</p><p class="font-medium text-gray-800">{{ $personel->phone ?? '—' }}</p></div>
                </div>
            </div>
        </div>

        {{-- LEAVES TAB --}}
        <div x-show="tab === 'leaves'">
            @if($leaveBalances && $leaveBalances->count() > 0)
            <div class="mb-5">
                <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wider mb-3">İzin Bakiyesi ({{ now()->year }})</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($leaveBalances as $lb)
                    <div class="bg-gray-50/50 rounded-xl p-3 border border-gray-100">
                        <p class="text-xs text-gray-400 font-medium">{{ $lb->name }}</p>
                        <div class="flex items-end gap-2 mt-1">
                            <span class="text-lg font-black text-gray-800">{{ $lb->remaining_days }}</span>
                            <span class="text-xs text-gray-400 mb-0.5">/ {{ $lb->entitled_days }} gün</span>
                        </div>
                        <div class="mt-2 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full rounded-full bg-[#02E0FB] transition-all" style="width:{{ $lb->entitled_days > 0 ? ($lb->used_days/$lb->entitled_days*100) : 0 }}%"></div>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">{{ $lb->used_days }} gün kullanıldı</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wider mb-3">İzin Geçmişi</h3>
            @if($personel->leaveRequests && $personel->leaveRequests->count() > 0)
                <div class="space-y-2">
                    @foreach($personel->leaveRequests->take(10) as $leave)
                    <div class="flex items-center justify-between p-3 bg-gray-50/50 rounded-xl text-sm border border-gray-50 hover:border-gray-200 transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-base shadow-sm">
                                {{ $leave->status === 'approved' ? '✅' : ($leave->status === 'rejected' ? '❌' : '⏳') }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-700">{{ $leave->leaveType?->name ?? 'İzin' }}</p>
                                <p class="text-xs text-gray-400">{{ $leave->start_date?->format('d.m.Y') }} - {{ $leave->end_date?->format('d.m.Y') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold text-gray-600">{{ $leave->total_days }} gün</span>
                            @php
                                $sc = ['pending' => 'bg-amber-50 text-amber-700', 'approved' => 'bg-emerald-50 text-emerald-700', 'rejected' => 'bg-red-50 text-red-700', 'cancelled' => 'bg-gray-100 text-gray-500'];
                                $sl = ['pending' => 'Bekliyor', 'approved' => 'Onaylandı', 'rejected' => 'Reddedildi', 'cancelled' => 'İptal'];
                            @endphp
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $sc[$leave->status] ?? 'bg-gray-100 text-gray-600' }}">{{ $sl[$leave->status] ?? $leave->status }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-400 py-6 text-sm bg-gray-50/50 rounded-xl">İzin kaydı bulunamadı</p>
            @endif
        </div>

        {{-- ATTENDANCE TAB --}}
        <div x-show="tab === 'attendance'">
            <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wider mb-3">Son 30 Gün Puantaj</h3>
            @if($attendanceSummary && $attendanceSummary->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase text-gray-400">Tarih</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase text-gray-400">Tür</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase text-gray-400">Saat</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase text-gray-400">Kaynak</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($attendanceSummary as $tr)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-3 py-2 text-gray-700 font-medium">{{ Carbon\Carbon::parse($tr->recorded_at)->format('d.m.Y') }}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $tr->type === 'in' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                                        {{ $tr->type === 'in' ? 'Giriş' : 'Çıkış' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-gray-600">{{ Carbon\Carbon::parse($tr->recorded_at)->format('H:i') }}</td>
                                <td class="px-3 py-2 text-gray-400 text-xs">{{ $tr->source ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center text-gray-400 py-6 text-sm bg-gray-50/50 rounded-xl">Puantaj kaydı bulunamadı</p>
            @endif
        </div>

        {{-- ASSETS TAB --}}
        <div x-show="tab === 'assets'">
            <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wider mb-3">Zimmetli Envanterler</h3>
            @if($assignedAssets && $assignedAssets->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($assignedAssets as $asset)
                    <div class="flex items-center gap-3 p-3 bg-gray-50/50 rounded-xl border border-gray-50">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center shrink-0"><svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ $asset->name }}</p>
                            <p class="text-xs text-gray-400">{{ $asset->type_name ?? '—' }} @if($asset->serial) · {{ $asset->serial }} @endif</p>
                        </div>
                        <span class="text-[10px] font-semibold px-2 py-1 rounded-full {{ $asset->status === 'assigned' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $asset->status === 'assigned' ? 'Zimmetli' : $asset->status }}
                        </span>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-400 py-6 text-sm bg-gray-50/50 rounded-xl">Zimmetli envanter bulunamadı</p>
            @endif
        </div>

        {{-- DOCS TAB --}}
        <div x-show="tab === 'docs'">
            <div id="belgelerContainer">
                <p class="text-gray-400 text-sm text-center py-8">Yükleniyor...</p>
            </div>
        </div>

        {{-- ACTIVITY TAB --}}
        <div x-show="tab === 'activity'">
            <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wider mb-3">Son Aktiviteler</h3>
            @if($recentActivity && $recentActivity->count() > 0)
                <div class="space-y-1">
                    @foreach($recentActivity as $act)
                    <div class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 rounded-lg transition-colors px-2 -mx-2">
                        <span class="text-base shrink-0">{{ match($act->action) {'created'=>'🟢', 'updated'=>'🔵', 'deleted'=>'🔴', 'approved'=>'✅', 'rejected'=>'❌', default=>'📋'} }}</span>
                        <div class="flex-1"><p class="text-sm font-medium text-gray-700 capitalize">{{ $act->action }}</p></div>
                        <span class="text-xs text-gray-400 shrink-0">{{ Carbon\Carbon::parse($act->created_at)->diffForHumans() }}</span>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-400 py-6 text-sm bg-gray-50/50 rounded-xl">Aktivite kaydı bulunamadı</p>
            @endif
        </div>

    </div>

    {{-- Footer --}}
    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-2">
        @can('personel.update')
        <button onclick="openEditModal({{ $personel->id }})" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 rounded-xl transition-all shadow-sm">
            Düzenle
        </button>
        @endcan
        @can('personel.export')
        <button onclick="window.open('/admin/personel/{{ $personel->id }}/export/pdf','_blank')" class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-[#FA6001] to-orange-500 hover:from-orange-500 hover:to-[#FA6001] rounded-xl transition-all shadow-sm">
            PDF İndir
        </button>
        @endcan
        <button onclick="document.getElementById('personelCardArea').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-all">
            Kapat
        </button>
    </div>
</div>
