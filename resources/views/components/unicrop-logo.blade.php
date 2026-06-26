@props(['textColor' => 'text-[#1b5e2e]'])

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
    <svg viewBox="0 0 100 100" class="w-10 h-10 shrink-0">
        <defs>
            <linearGradient id="ubcGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#0f4023" />
                <stop offset="100%" stop-color="#6fbf3f" />
            </linearGradient>
        </defs>
        <rect x="8" y="8" width="60" height="60" rx="10" transform="rotate(45 38 38)" fill="url(#ubcGrad)" />
        <rect x="14" y="14" width="48" height="48" rx="8" transform="rotate(45 38 38)" fill="white" />
        <text x="38" y="46" text-anchor="middle" font-family="Figtree, sans-serif" font-weight="800" font-size="22" fill="#1b5e2e">UBC</text>
        <path d="M50 22c-4 1-7 4-7 9 0-5 3-8 7-9z" fill="#3f9b3f" />
    </svg>
    <div class="leading-tight">
        <div class="flex items-baseline gap-1">
            <span class="text-2xl font-extrabold {{ $textColor }}">Unicrop</span>
            <span class="text-[10px] font-bold {{ $textColor }} -translate-y-2">TM</span>
        </div>
        <div class="border-t border-current opacity-30 my-0.5"></div>
        <span class="text-sm font-bold {{ $textColor }} tracking-wide">Biochem</span>
    </div>
</div>
