@props([
    'icon' => 'inbox',
    'title' => 'Belum ada data',
    'description' => 'Data untuk bagian ini belum tersedia atau belum ditambahkan.',
    'actionUrl' => null,
    'actionLabel' => null,
])

<div {{ $attributes->merge(['class' => 'p-8 text-center bg-surface border border-dashed border-line rounded-[8px] my-2']) }}>
    <div class="w-12 h-12 rounded-full bg-slate-100 text-text-muted flex items-center justify-center mx-auto mb-3">
        <x-icon :name="$icon" :size="22" />
    </div>
    <h4 class="text-sm font-semibold text-text mb-1">{{ $title }}</h4>
    <p class="text-xs text-text-muted max-w-sm mx-auto leading-relaxed">{{ $description }}</p>
    @if ($actionUrl && $actionLabel)
        <div class="mt-4">
            <a href="{{ $actionUrl }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[6px] bg-[#2563EB] text-white text-xs font-medium hover:bg-blue-700 transition-colors shadow-2xs">
                <span>{{ $actionLabel }}</span>
            </a>
        </div>
    @endif
</div>
