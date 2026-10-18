@props([
    'terisi' => 0,
    'tersisa' => 0,
    'labelTerisi' => 'Terisi',
    'labelTersisa' => 'Tersisa',
    'colorTerisi' => '#2563EB',
    'colorTersisa' => '#E2E8F0',
    'showLegend' => true,
    'unit' => '',
])

@php
    $terisiInt = max(0, (int) $terisi);
    $tersisaInt = max(0, (int) $tersisa);
    $total = $terisiInt + $tersisaInt;
    $percentage = $total > 0 ? round(($terisiInt / $total) * 100) : 0;

    // SVG parameters
    $r = 38;
    $circ = 2 * pi() * $r; // ~238.76
    $dashTerisi = $total > 0 ? ($terisiInt / $total) * $circ : 0;
    $dashTersisa = $circ - $dashTerisi;
@endphp

<div class="flex items-center gap-6">
    <div class="relative w-28 h-28 shrink-0 flex items-center justify-center">
        <svg class="w-full h-full -rotate-90 transform" viewBox="0 0 100 100">
            <!-- Background / Tersisa -->
            <circle
                cx="50"
                cy="50"
                r="{{ $r }}"
                class="stroke-slate-100"
                stroke-width="12"
                fill="none"
            />
            @if ($total > 0 && $terisiInt > 0)
                <circle
                    cx="50"
                    cy="50"
                    r="{{ $r }}"
                    stroke="{{ $colorTerisi }}"
                    stroke-width="12"
                    fill="none"
                    stroke-dasharray="{{ $dashTerisi }} {{ $circ - $dashTerisi }}"
                    stroke-dashoffset="0"
                    stroke-linecap="round"
                    class="transition-all duration-700 ease-out"
                />
            @endif
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
            <span class="text-xl font-extrabold text-[#1A2E5E] leading-none tracking-tight">
                {{ $percentage }}%
            </span>
            <span class="text-[10px] font-semibold text-slate-400 mt-0.5 uppercase tracking-wider">
                Terisi
            </span>
        </div>
    </div>

    @if ($showLegend)
        <div class="space-y-2 min-w-0 flex-1 text-xs">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 truncate">
                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $colorTerisi }}"></span>
                    <span class="text-slate-600 truncate">{{ $labelTerisi }}</span>
                </div>
                <span class="font-bold text-[#1A2E5E] tabular-nums">
                    {{ number_format($terisiInt) }} {{ $unit }}
                </span>
            </div>
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 truncate">
                    <span class="w-2.5 h-2.5 rounded-full shrink-0 bg-slate-200"></span>
                    <span class="text-slate-600 truncate">{{ $labelTersisa }}</span>
                </div>
                <span class="font-bold text-slate-500 tabular-nums">
                    {{ number_format($tersisaInt) }} {{ $unit }}
                </span>
            </div>
            @if ($total > 0)
                <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400 font-medium">
                    <span>Total Kapasitas</span>
                    <span class="font-bold text-slate-700 tabular-nums">{{ number_format($total) }} {{ $unit }}</span>
                </div>
            @endif
        </div>
    @endif
</div>
