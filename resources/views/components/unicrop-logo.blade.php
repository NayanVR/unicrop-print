@props(['textColor' => 'text-[#1b5e2e]', 'variant' => 'dark'])

<div {{ $attributes->merge(['class' => 'inline-flex items-center']) }}>
    <img src="{{ asset('unicrop-logo.png') }}" alt="Unicrop Biochem" class="h-10 w-auto object-contain {{ $variant === 'light' ? 'brightness-0 invert' : '' }}">
</div>
