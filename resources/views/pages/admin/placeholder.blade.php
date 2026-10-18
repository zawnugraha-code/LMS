<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Administrator LMS Arunika' }} - Universitas Nusantara Indonesia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col justify-between">
    <header class="bg-[#1A2E5E] text-white px-6 py-4 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-500/20 border border-white/20 flex items-center justify-center font-bold text-lg text-white">
                A
            </div>
            <div>
                <h1 class="text-base font-bold tracking-tight">LMS Arunika - Portal Administrator</h1>
                <p class="text-xs text-blue-200">Universitas Nusantara Indonesia</p>
            </div>
        </div>
        <div class="flex items-center gap-4 text-xs">
            <span class="text-blue-200">Masuk sebagai: <strong>{{ auth()->user()?->name ?? 'Administrator' }}</strong></span>
            <a href="{{ route('marketing.home') }}" class="px-3 py-1.5 bg-white/10 hover:bg-white/20 rounded-lg text-white font-medium transition">
                Ke Beranda Utama
            </a>
        </div>
    </header>

    <main class="flex-1 max-w-4xl mx-auto w-full px-6 py-12 flex flex-col items-center justify-center text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 mb-4">
            <span>{{ $badge ?? 'Dalam Pengembangan' }}</span>
        </div>

        <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1A2E5E] mb-3">
            {{ $title ?? 'Fitur Sedang Disiapkan' }}
        </h2>

        <p class="text-slate-600 max-w-xl text-sm sm:text-base mb-8 leading-relaxed">
            {{ $description ?? 'Modul ini sedang dalam tahap perancangan dan akan segera hadir pada pembaruan rilis berikutnya.' }}
        </p>

        <div class="p-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm text-left max-w-lg w-full mb-8">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Informasi Rute</h3>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Nama Rute</span>
                    <span class="font-mono font-medium text-slate-800">{{ Route::currentRouteName() }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">URI</span>
                    <span class="font-mono font-medium text-slate-800">/{{ request()->path() }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-500">Akses Pengguna</span>
                    <span class="font-semibold text-emerald-600">Terotorisasi (Administrator)</span>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-3 justify-center">
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-[#1A2E5E] hover:bg-[#142347] text-white text-xs font-semibold rounded-xl shadow-sm transition">
                Kembali ke Beranda Admin
            </a>
        </div>
    </main>

    <footer class="text-center py-4 text-xs text-slate-400 border-t border-slate-200">
        &copy; 2026 Arunika LMS - Universitas Nusantara Indonesia.
    </footer>
</body>
</html>
