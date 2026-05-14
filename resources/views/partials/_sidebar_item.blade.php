{{-- Sidebar Nav Item Partial — date: 2026-03-15 --}}
@php
$isActive = str_starts_with(request()->route()?->getName() ?? '', $route);
@endphp
<a href="{{ route($route) }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
   {{ $isActive ? 'bg-[#02E0FB] text-gray-900 shadow-lg shadow-cyan-500/20 font-semibold' : 'text-gray-400 hover:bg-gray-800/80 hover:text-white' }}">
    <svg class="w-4 h-4 shrink-0 {{ $isActive ? 'text-gray-900' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
    </svg>
    {{ $label }}
</a>
