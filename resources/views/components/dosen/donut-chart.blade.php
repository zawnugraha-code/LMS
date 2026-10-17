@props([
    'total' => 0,
    'published' => 0,
    'draft' => 0,
    'scheduled' => 0,
    'archived' => 0,
])

@php
    $totalCount = max(1, $total ?: ($published + $draft + $scheduled + $archived));
    
    // Percentages
    $pPub = round(($published / $totalCount) * 100);
    $pDraft = round(($draft / $totalCount) * 100);
    $pSched = round(($scheduled / $totalCount) * 100);
    $pArch = max(0, 100 - ($pPub + $pDraft + $pSched));

    // SVG Circle parameters
    $r = 38;
    $circ = 2 * pi() * $r; // ~238.76

    $lenPub = ($pPub / 100) * $circ;
    $lenDraft = ($pDraft / 100) * $circ;
    $lenSched = ($pSched / 100) * $circ;
    $lenArch = ($pArch / 100) * $circ;

    $offsetPub = 0;
    $offsetDraft = -$lenPub;
    $offsetSched = -($lenPub + $lenDraft);
    $offsetArch = -($lenPub + $lenDraft + $lenSched);
@endphp

<div class="flex items-center gap-6">
    <!-- Donut SVG -->
    <div class="relative w-32 h-32 shrink-0 flex items-center justify-center">
        <svg class="w-full h-full -rotate-90 transform" viewBox="0 0 100 100">
            <!-- Background Ring -->
            <circle
                cx="50"
                cy="50"
                r="{{ $r }}"
                class="stroke-slate-100"
                stroke-width="12"
                fill="none"
            />

            @if ($published > 0)
                <!-- Published: Emerald / Teal (#10B981) -->
                <circle
                    cx="50"
                    cy="50"
                    r="{{ $r }}"
                    stroke="#10B981"
                    stroke-width="12"
                    fill="none"
                    stroke-dasharray="{{ $lenPub }} {{ $circ - $lenPub }}"
                    stroke-dashoffset="{{ $offsetPub }}"
                    class="transition-all duration-500"
                />
            @endif

            @if ($draft > 0)
                <!-- Draft: Amber (#F59E0B) -->
                <circle
                    cx="50"
                    cy="50"
                    r="{{ $r }}"
                    stroke="#F59E0B"
                    stroke-width="12"
                    fill="none"
                    stroke-dasharray="{{ $lenDraft }} {{ $circ - $lenDraft }}"
                    stroke-dashoffset="{{ $offsetDraft }}"
                    class="transition-all duration-500"
                />
            @endif

            @if ($scheduled > 0)
                <!-- Scheduled: Indigo / Blue (#3B82F6) -->
                <circle
                    cx="50"
                    cy="50"
                    r="{{ $r }}"
                    stroke="#3B82F6"
                    stroke-width="12"
                    fill="none"
                    stroke-dasharray="{{ $lenSched }} {{ $circ - $lenSched }}"
                    stroke-dashoffset="{{ $offsetSched }}"
                    class="transition-all duration-500"
                />
            @endif

            @if ($archived > 0)
                <!-- Archived: Slate (#94A3B8) -->
                <circle
                    cx="50"
                    cy="50"
                    r="{{ $r }}"
                    stroke="#94A3B8"
                    stroke-width="12"
                    fill="none"
                    stroke-dasharray="{{ $lenArch }} {{ $circ - $lenArch }}"
                    stroke-dashoffset="{{ $offsetArch }}"
                    class="transition-all duration-500"
                />
            @endif
        </svg>

        <!-- Center Text -->
        <div class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none">
            <span class="text-xl font-bold text-text tabular-nums leading-none tracking-tight">
                {{ $total }}
            </span>
            <span class="text-[10px] uppercase font-semibold text-text-muted mt-1 tracking-wider">
                Total
            </span>
        </div>
    </div>

    <!-- Legend -->
    <div class="space-y-2 text-xs flex-1 min-w-0">
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 min-w-0">
                <span class="w-2.5 h-2.5 rounded-full bg-[#10B981] shrink-0"></span>
                <span class="text-text-muted truncate">Dipublikasikan</span>
            </div>
            <span class="font-bold text-text tabular-nums">{{ $published }} <span class="font-normal text-text-subtle text-[11px]">({{ $pPub }}%)</span></span>
        </div>

        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 min-w-0">
                <span class="w-2.5 h-2.5 rounded-full bg-[#F59E0B] shrink-0"></span>
                <span class="text-text-muted truncate">Draft</span>
            </div>
            <span class="font-bold text-text tabular-nums">{{ $draft }} <span class="font-normal text-text-subtle text-[11px]">({{ $pDraft }}%)</span></span>
        </div>

        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 min-w-0">
                <span class="w-2.5 h-2.5 rounded-full bg-[#3B82F6] shrink-0"></span>
                <span class="text-text-muted truncate">Terjadwal</span>
            </div>
            <span class="font-bold text-text tabular-nums">{{ $scheduled }} <span class="font-normal text-text-subtle text-[11px]">({{ $pSched }}%)</span></span>
        </div>

        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 min-w-0">
                <span class="w-2.5 h-2.5 rounded-full bg-[#94A3B8] shrink-0"></span>
                <span class="text-text-muted truncate">Arsip</span>
            </div>
            <span class="font-bold text-text tabular-nums">{{ $archived }} <span class="font-normal text-text-subtle text-[11px]">({{ $pArch }}%)</span></span>
        </div>
    </div>
</div>
