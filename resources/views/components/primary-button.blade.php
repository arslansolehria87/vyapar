<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-[#5813BC] to-[#AC22CB] hover:from-[#673B92] hover:to-[#B82FDE] text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#5813BC]/30']) }}>
    {{ $slot }}
</button>
