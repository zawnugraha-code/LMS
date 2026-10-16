<!DOCTYPE html>
<html lang="id" class="h-full bg-canvas">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Akses Dibatasi | Arunika</title>
    <link rel="icon" href="/favicon.ico">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-canvas text-text font-sans antialiased flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-surface border border-line rounded-[10px] p-8 text-center shadow-2xs space-y-4">
        <div class="flex justify-center pb-2">
            <x-logo variant="primary" />
        </div>
        <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-600 mx-auto flex items-center justify-center">
            <x-icon name="shield-alert" :size="24" />
        </div>
        <div class="space-y-1">
            <h1 class="text-xl font-bold text-text">Akses Dibatasi</h1>
            <p class="text-xs text-text-muted leading-relaxed">
                Anda tidak memiliki hak akses untuk membuka halaman atau berkas perkuliahan ini.
            </p>
        </div>
        <div class="pt-2">
            <a href="{{ route('app.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-[6px] bg-primary hover:bg-primary-hover text-white text-xs font-medium transition-colors">
                <x-icon name="arrow-left" :size="14" class="text-white" />
                <span>Kembali ke Beranda</span>
            </a>
        </div>
    </div>
</body>
</html>
