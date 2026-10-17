@props([
    'status' => '',
    'size' => 'sm',
])

@php
    $normalized = strtolower(trim($status));
    $label = match ($normalized) {
        'tepat_waktu', 'tepat waktu' => 'Tepat Waktu',
        'terlambat' => 'Terlambat',
        'belum_kumpul', 'belum mengumpulkan' => 'Belum Mengumpulkan',
        'dipublikasikan', 'published' => 'Dipublikasikan',
        'draft' => 'Draft',
        'terjadwal' => 'Terjadwal',
        'arsip' => 'Arsip',
        'menunggu', 'menunggu penilaian' => 'Menunggu',
        'sudah dinilai', 'dinilai' => 'Sudah Dinilai',
        'aktif' => 'Aktif',
        'selesai' => 'Selesai',
        default => ucfirst($status),
    };

    $colors = match ($normalized) {
        'tepat_waktu', 'tepat waktu', 'sudah dinilai', 'dinilai', 'aktif' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'terlambat', 'menunggu', 'menunggu penilaian' => 'bg-amber-50 text-amber-700 border-amber-200',
        'dipublikasikan', 'published' => 'bg-blue-50 text-blue-700 border-blue-200',
        'terjadwal' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'draft', 'selesai', 'belum_kumpul', 'belum mengumpulkan' => 'bg-slate-100 text-slate-700 border-slate-200',
        'arsip' => 'bg-zinc-100 text-zinc-600 border-zinc-200',
        default => 'bg-slate-100 text-slate-700 border-slate-200',
    };

    $padding = $size === 'xs' ? 'px-1.5 py-0.5 text-[10px]' : 'px-2 py-0.5 text-xs';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-medium rounded-full border {$colors} {$padding} leading-none whitespace-nowrap"]) }}>
    {{ $label }}
</span>
