@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-[#02E0FB] focus:ring-[#02E0FB] rounded-xl shadow-sm block mt-1 w-full']) }}>
