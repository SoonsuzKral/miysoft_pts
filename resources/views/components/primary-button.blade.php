<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-[#02E0FB] hover:bg-[#00b8d9] border border-transparent rounded-xl font-bold text-sm text-gray-900 uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-[#02E0FB] focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg shadow-cyan-500/30']) }}>
    {{ $slot }}
</button>
