{{-- MİYSOFT PTS — Sidebar Nav Item --}}
@php $isOn = isset($active) ? $active : false; @endphp
<a href="{{ route($route) }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
          {{ $isOn
              ? 'bg-[#02E0FB]/15 text-[#02E0FB] font-semibold'
              : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
    </svg>
    <span class="truncate">{{ $label }}</span>
    @if($isOn)
    <span class="ml-auto w-1.5 h-1.5 rounded-full bg-[#02E0FB] flex-shrink-0"></span>
    @endif
</a>
