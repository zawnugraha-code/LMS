@props([
    'title' => '',
    'value' => '0',
    'subtitle' => '',
    'icon' => 'book-open',
    'iconBg' => 'bg-blue-50 text-blue-700',
    'badge' => null,
    'badgeColor' => 'bg-emerald-100 text-emerald-800',
])

<div {{ $attributes->merge(['class' => 'bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs hover:border-slate-300 transition-all duration-200']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 truncate">{{ $title }}</p>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-3xl font-extrabold text-[#1A2E5E] tracking-tight tabular-nums">
                    {{ $value }}
                </span>
                @if ($badge !== null)
                    <span class="px-2 py-0.5 text-xs font-bold rounded-full {{ $badgeColor }}">
                        {{ $badge }}
                    </span>
                @endif
            </div>
            @if ($subtitle)
                <p class="text-xs text-slate-500 mt-1 truncate">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="w-12 h-12 rounded-xl {{ $iconBg }} flex items-center justify-center shrink-0 shadow-2xs">
            <x-icon :name="$icon" :size="24" />
        </div>
    </div>
</div>
