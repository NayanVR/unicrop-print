<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#1b5e2e] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#164d26] active:bg-[#0f4023] focus:outline-none focus:ring-2 focus:ring-[#3f9b3f] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
