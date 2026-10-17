@props([
    'title' => '',
    'value' => '0',
    'unit' => '',
    'icon' => 'book-open',
    'badge' => null,
    'badgeColor' => 'bg-red-500 text-white',
    'iconBg' => 'bg-slate-100 text-navy-900',
])

<div {{ $attributes->merge(['class' => 'bg-surface border border-line rounded-[10px] p-4 sm:p-5 flex items-center gap-4 shadow-2xs hover:border-slate-300 transition-colors']) }}>
    <div class="w-12 h-12 rounded-[10px] {{ $iconBg }} flex items-center justify-center shrink-0">
        <x-icon :name="$icon" :size="22" />
    </div>
    <div class="min-w-0 flex-1">
        <p class="text-xs font-medium text-text-muted truncate">{{ $title }}</p>
        <div class="flex items-center gap-2 mt-0.5">
            <span class="text-2xl font-bold text-text tabular-nums tracking-tight leading-tight">
                {{ $value }}
            </span>
            @if ($badge !== null)
                <span class="px-1.5 py-0.5 text-[11px] font-bold rounded-full {{ $badgeColor }} leading-none">
                    {{ $badge }}
                </span>
            @endif
        </div>
        @if ($unit)
            <p class="text-[11px] text-text-muted truncate mt-0.5">{{ $unit }}</p>
        @endif
    </div>
</div>
