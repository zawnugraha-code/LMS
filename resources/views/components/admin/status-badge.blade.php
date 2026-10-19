@props([
    'status' => 'belum_aktif',
    'type' => 'semester', // semester, class, user, general
])

@php
    $normalized = strtolower(trim((string) $status));

    $config = match ($normalized) {
        'aktif' => [
            'label' => 'Aktif',
            'classes' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/80',
            'dot' => 'bg-emerald-500',
        ],
        'belum_aktif' => [
            'label' => 'Belum Aktif',
            'classes' => 'bg-blue-50 text-blue-700 border border-blue-200/80',
            'dot' => 'bg-blue-500',
        ],
        'selesai' => [
            'label' => 'Selesai',
            'classes' => 'bg-slate-100 text-slate-700 border border-slate-200/80',
            'dot' => 'bg-slate-400',
        ],
        'dikeluarkan', 'keluar' => [
            'label' => 'Dikeluarkan',
            'classes' => 'bg-rose-50 text-rose-700 border border-rose-200/80',
            'dot' => 'bg-rose-500',
        ],
        'perlu_tindakan' => [
            'label' => 'Perlu tindakan',
            'classes' => 'bg-amber-50 text-amber-800 border border-amber-200/80',
            'dot' => 'bg-amber-500',
        ],
        'aman' => [
            'label' => 'Aman',
            'classes' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/80',
            'dot' => 'bg-emerald-500',
        ],
        default => [
            'label' => ucfirst($status),
            'classes' => 'bg-slate-50 text-slate-700 border border-slate-200',
            'dot' => 'bg-slate-400',
        ],
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold tracking-wide ' . $config['classes']]) }}>
    <span class="w-1.5 h-1.5 rounded-full {{ $config['dot'] }} shrink-0"></span>
    <span>{{ $config['label'] }}</span>
</span>
