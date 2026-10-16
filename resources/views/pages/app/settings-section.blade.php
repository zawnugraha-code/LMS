@extends('layouts.app')

@section('title', ($sectionConfig['title'] ?? 'Pengaturan') . ' — Arunika')
@section('page_breadcrumb', 'Pengaturan / ' . ($sectionConfig['title'] ?? 'Sub-Halaman'))

@section('content')
<div 
    x-data="{
        toastMessage: '',
        showToast: false,
        ticketModalOpen: false,
        newTokenModalOpen: false,
        triggerToast(msg) {
            this.toastMessage = msg;
            this.showToast = true;
            setTimeout(() => { this.showToast = false; }, 3000);
        }
    }"
    class="space-y-5"
>
    
    <!-- ==========================================
         1. BREADCRUMB & HEADER SECTION
         ========================================== -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <!-- Left: Page Icon, Title & Subtitle -->
        <div class="flex items-start gap-3.5">
            <div class="w-11 h-11 rounded-full bg-[#1A2E5E] flex items-center justify-center text-white shrink-0 shadow-2xs mt-0.5">
                <x-icon :name="$sectionConfig['icon'] ?? 'settings'" :size="20" />
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('app.settings') }}" class="text-xs font-semibold text-[#2563EB] hover:underline inline-flex items-center gap-1">
                        <x-icon name="arrow-left" :size="12" />
                        <span>Kembali ke Pengaturan</span>
                    </a>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-navy-950 tracking-tight mt-1">
                    {{ $sectionConfig['title'] ?? 'Pengaturan Sekunder' }}
                </h1>
                <p class="text-xs text-text-muted mt-0.5">
                    {{ $sectionConfig['subtitle'] ?? 'Kelola preferensi dan konfigurasi lanjutan akun Anda.' }}
                </p>
            </div>
        </div>

        <!-- Right: Action / Status Badge -->
        <div class="flex items-center gap-2 self-start md:self-auto">
            <a 
                href="{{ route('app.settings') }}"
                class="px-3.5 py-1.5 rounded-[6px] border border-line bg-surface hover:bg-canvas text-xs font-semibold text-text shadow-2xs transition-colors inline-flex items-center gap-1.5"
            >
                <x-icon name="settings" :size="13" class="text-text-muted" />
                <span>Semua Pengaturan</span>
            </a>
        </div>

    </div>

    <!-- ==========================================
         2. SECTION CONTENT LAYOUT
         ========================================== -->
    <div class="space-y-4">

        {{-- ========================================================
             CASE A: APLIKASI TERHUBUNG (INTEGRASI)
             ======================================================== --}}
        @if ($sectionKey === 'aplikasi-terhubung')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($sectionConfig['apps'] as $app)
                    <x-ui.card class="p-4 sm:p-5 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                        @if ($app['icon'] === 'google')
                                            <svg viewBox="0 0 24 24" class="w-4 h-4">
                                                <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.66v3.04h3.88c2.27-2.09 3.665-5.17 3.665-9.14z"/>
                                                <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.04c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.26v3.13C3.25 21.37 7.33 24 12 24z"/>
                                                <path fill="#FBBC05" d="M5.28 14.28c-.25-.72-.38-1.49-.38-2.28s.13-1.56.38-2.28V6.59H1.26C.46 8.19 0 9.99 0 12s.46 3.81 1.26 5.41l4.02-3.13z"/>
                                                <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.25 2.63 1.26 6.59l4.02 3.13c.95-2.83 3.6-4.97 6.72-4.97z"/>
                                            </svg>
                                        @elseif ($app['icon'] === 'github')
                                            <svg class="w-4 h-4 fill-current text-slate-800" viewBox="0 0 24 24">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                                            </svg>
                                        @else
                                            <x-icon :name="$app['icon']" :size="16" />
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-xs sm:text-sm text-text">
                                            {{ $app['name'] }}
                                        </h3>
                                        <span class="text-[10px] text-text-muted block">
                                            Izin: {{ $app['scope'] }}
                                        </span>
                                    </div>
                                </div>

                                @if ($app['connected'])
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] bg-emerald-50 text-emerald-700 text-[10px] font-semibold border border-emerald-200">
                                        <x-icon name="check" :size="10" strokeWidth="3" />
                                        <span>Terhubung</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] bg-canvas text-text-muted text-[10px] font-medium border border-line">
                                        <span>Belum Terhubung</span>
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs text-text-muted mb-3 leading-relaxed">
                                {{ $app['description'] }}
                            </p>
                        </div>

                        <div class="pt-3 border-t border-line/70 flex items-center justify-between gap-2 text-xs">
                            <span class="text-[10px] text-text-muted">
                                @if ($app['connected'])
                                    Sinkronisasi: {{ $app['last_sync'] }}
                                @else
                                    Tidak aktif
                                @endif
                            </span>

                            <div class="flex items-center gap-2">
                                @if ($app['connected'])
                                    <button 
                                        type="button" 
                                        @click="triggerToast('Menyinkronkan data {{ $app['name'] }}...')"
                                        class="px-2.5 py-1 rounded-[5px] border border-line bg-surface hover:bg-canvas text-[11px] font-medium text-text transition-colors cursor-pointer"
                                    >
                                        Sinkronkan
                                    </button>
                                    <button 
                                        type="button" 
                                        @click="triggerToast('Koneksi {{ $app['name'] }} telah diputus.')"
                                        class="px-2.5 py-1 rounded-[5px] border border-red-200 bg-red-50/50 hover:bg-red-50 text-[11px] font-semibold text-red-600 transition-colors cursor-pointer"
                                    >
                                        Putuskan
                                    </button>
                                @else
                                    <button 
                                        type="button" 
                                        @click="triggerToast('Menghubungkan ke {{ $app['name'] }}...')"
                                        class="px-3 py-1 rounded-[5px] bg-[#2563EB] hover:bg-blue-700 text-[11px] font-semibold text-white shadow-2xs transition-colors cursor-pointer"
                                    >
                                        Hubungkan Akun
                                    </button>
                                @endif
                            </div>
                        </div>
                    </x-ui.card>
                @endforeach
            </div>

            <!-- Single Sign-On Campus Note -->
            <x-ui.card class="p-4 sm:p-5 bg-blue-50/40 border-blue-100 flex items-start gap-3.5">
                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                    <x-icon name="shield-check" :size="16" />
                </div>
                <div class="text-xs space-y-1">
                    <h4 class="font-bold text-navy-950">Privasi &amp; Akses Kampus Merdeka</h4>
                    <p class="text-text-muted leading-relaxed">
                        Arunika LMS menggunakan protokol OAuth 2.0 standar industri untuk menjaga keamanan data autentikasi Anda. Akses aplikasi eksternal dibatasi sesuai hak akses mahasiswa dan dapat dicabut sewaktu-waktu.
                    </p>
                </div>
            </x-ui.card>

        {{-- ========================================================
             CASE B: WEBHOOK & API
             ======================================================== --}}
        @elseif ($sectionKey === 'webhook-api')
            <!-- Tokens Card -->
            <x-ui.card class="p-4 sm:p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <x-icon name="key" :size="15" />
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-text">Personal Access Tokens</h3>
                            <p class="text-xs text-text-muted mt-0.5">Token otorisasi API untuk automasi dan integrasi script akademik.</p>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        @click="triggerToast('Token API baru berhasil dibuat: arn_pat_live_' + Math.random().toString(36).substring(2, 10))"
                        class="px-3.5 py-1.5 rounded-[6px] bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold shadow-2xs transition-colors cursor-pointer inline-flex items-center gap-1.5"
                    >
                        <x-icon name="plus" :size="13" />
                        <span>Buat Token Baru</span>
                    </button>
                </div>

                <div class="space-y-3">
                    @foreach ($sectionConfig['tokens'] as $tok)
                        <div class="p-3.5 rounded-[8px] border border-line bg-canvas/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="space-y-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-xs text-text">{{ $tok['name'] }}</span>
                                    <code class="px-2 py-0.5 rounded-[4px] bg-slate-200 text-slate-800 text-[10px] font-mono">{{ $tok['prefix'] }}</code>
                                </div>
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[10px] text-text-muted">
                                    <span>Dibuat: {{ $tok['created_at'] }}</span>
                                    <span>&bull;</span>
                                    <span>Terakhir digunakan: {{ $tok['last_used'] }}</span>
                                    <span>&bull;</span>
                                    <span>Kedaluwarsa: {{ $tok['expires_at'] }}</span>
                                </div>
                                <div class="flex items-center gap-1.5 pt-1">
                                    @foreach ($tok['scopes'] as $sc)
                                        <span class="px-1.5 py-0.5 rounded-[3px] bg-blue-50 text-blue-700 text-[9px] font-medium border border-blue-100">
                                            {{ $sc }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                                <button 
                                    type="button" 
                                    @click="triggerToast('Token {{ $tok['name'] }} disalin ke clipboard')"
                                    class="px-2.5 py-1 rounded-[5px] border border-line bg-surface hover:bg-canvas text-xs font-medium text-text transition-colors cursor-pointer"
                                >
                                    Salin
                                </button>
                                <button 
                                    type="button" 
                                    @click="triggerToast('Token {{ $tok['name'] }} telah dicabut.')"
                                    class="px-2.5 py-1 rounded-[5px] border border-red-200 bg-red-50/50 hover:bg-red-50 text-xs font-semibold text-red-600 transition-colors cursor-pointer"
                                >
                                    Cabut
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>

            <!-- API Endpoints Card -->
            <x-ui.card class="p-4 sm:p-5">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <x-icon name="code" :size="15" />
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-text">Daftar Endpoint API Mahasiswa</h3>
                        <p class="text-xs text-text-muted mt-0.5">Dokumentasi RESTful API terstandar untuk integrasi aplikasi pihak ketiga.</p>
                    </div>
                </div>

                <div class="divide-y divide-line/70 border border-line rounded-[8px] overflow-hidden text-xs">
                    @foreach ($sectionConfig['endpoints'] as $ep => $desc)
                        <div class="p-3 bg-surface flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <code class="font-mono text-xs text-[#2563EB] font-bold">{{ $ep }}</code>
                            <span class="text-text-muted text-[11px]">{{ $desc }}</span>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>

        {{-- ========================================================
             CASE C: BANTUAN & DUKUNGAN
             ======================================================== --}}
        @elseif ($sectionKey === 'bantuan-dukungan')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
                
                <!-- Left 2 Cols: FAQ List & Ticket History -->
                <div class="lg:col-span-2 space-y-4">
                    
                    <!-- FAQ Card -->
                    <x-ui.card class="p-4 sm:p-5">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <x-icon name="help-circle" :size="15" />
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-text">Pertanyaan yang Sering Diajukan (FAQ)</h3>
                                <p class="text-xs text-text-muted mt-0.5">Solusi cepat untuk kendala umum penggunaan Arunika LMS.</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @foreach ($sectionConfig['faqs'] as $faq)
                                <div class="p-3.5 rounded-[8px] border border-line bg-canvas/30 space-y-1.5">
                                    <h4 class="font-bold text-xs text-text flex items-start gap-2">
                                        <x-icon name="info" :size="13" class="text-[#2563EB] shrink-0 mt-0.5" />
                                        <span>{{ $faq['q'] }}</span>
                                    </h4>
                                    <p class="text-xs text-text-muted leading-relaxed pl-5">
                                        {{ $faq['a'] }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </x-ui.card>

                    <!-- Ticket History Card -->
                    <x-ui.card class="p-4 sm:p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                    <x-icon name="message-square" :size="15" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-sm text-text">Riwayat Tiket Bantuan Anda</h3>
                                    <p class="text-xs text-text-muted mt-0.5">Pantau status penanganan kendala yang telah Anda ajukan.</p>
                                </div>
                            </div>
                        </div>

                        @if ($sectionConfig['tickets']->count() > 0)
                            <div class="space-y-2.5">
                                @foreach ($sectionConfig['tickets'] as $ticket)
                                    <div class="p-3 rounded-[8px] border border-line bg-canvas/30 flex items-center justify-between gap-3 text-xs">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-text truncate">{{ $ticket->subject }}</span>
                                                <span class="px-2 py-0.5 rounded-[4px] bg-blue-50 text-blue-700 text-[10px] font-semibold">
                                                    #{{ substr($ticket->ulid, -6) }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-text-muted mt-0.5 truncate">{{ $ticket->message }}</p>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <span class="px-2 py-0.5 rounded-[4px] text-[10px] font-bold uppercase {{ $ticket->status === 'resolved' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                                {{ $ticket->status }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="py-8 text-center text-text-muted text-xs">
                                <x-icon name="inbox" :size="24" class="mx-auto text-text-muted/60 mb-1.5" />
                                <p>Belum ada tiket bantuan aktif yang diajukan.</p>
                            </div>
                        @endif
                    </x-ui.card>

                </div>

                <!-- Right 1 Col: Contact Support Form -->
                <div class="space-y-4">
                    <x-ui.card class="p-4 sm:p-5">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <x-icon name="headphones" :size="15" />
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-text">Ajukan Tiket Bantuan</h3>
                                <p class="text-xs text-text-muted mt-0.5">Tim helpdesk akan merespons dalam 1x24 jam kerja.</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('app.settings.support_ticket') }}" enctype="multipart/form-data" class="space-y-3 text-xs">
                            @csrf
                            <div>
                                <label class="block font-medium text-text mb-1">Subjek Masalah</label>
                                <input 
                                    type="text" 
                                    name="subject" 
                                    required 
                                    placeholder="Contoh: Kendala upload tugas PBO" 
                                    class="w-full px-3 py-1.5 rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]"
                                >
                            </div>

                            <div>
                                <label class="block font-medium text-text mb-1">Kategori Kendala</label>
                                <select 
                                    name="category" 
                                    required 
                                    class="w-full px-3 py-1.5 rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]"
                                >
                                    <option value="teknis">Kendala Teknis &amp; Bug Sistem</option>
                                    <option value="akademik">Nilai &amp; Sinkronisasi KHS</option>
                                    <option value="presensi">Presensi &amp; Perizinan</option>
                                    <option value="akun">Autentikasi &amp; Keamanan Akun</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-medium text-text mb-1">Deskripsi Masalah</label>
                                <textarea 
                                    name="message" 
                                    rows="4" 
                                    required 
                                    placeholder="Jelaskan kendala secara rinci beserta langkah yang dilakukan..." 
                                    class="w-full px-3 py-1.5 rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]"
                                ></textarea>
                            </div>

                            <div>
                                <label class="block font-medium text-text mb-1">Lampiran Tangkapan Layar (Opsional)</label>
                                <input 
                                    type="file" 
                                    name="attachment" 
                                    accept="image/*,.pdf" 
                                    class="w-full text-xs text-text-muted file:mr-2 file:py-1 file:px-2.5 file:rounded-[4px] file:border file:border-line file:bg-surface file:text-xs file:font-semibold file:text-text hover:file:bg-canvas cursor-pointer"
                                >
                            </div>

                            <button 
                                type="submit" 
                                class="w-full py-2 rounded-[6px] bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs shadow-2xs transition-colors cursor-pointer"
                            >
                                Kirim Tiket ke Tim Support
                            </button>
                        </form>
                    </x-ui.card>

                    <!-- Campus Support Hotline -->
                    <x-ui.card class="p-4 bg-slate-50 border-slate-200 text-xs space-y-2">
                        <span class="font-bold text-text block">Hotline Biro Teknologi Informasi</span>
                        <p class="text-text-muted text-[11px] leading-relaxed">
                            Gedung Rektorat Lt. 2, Sayap Timur<br>
                            Email: <a href="mailto:support@unus.ac.id" class="text-[#2563EB] underline">support@unus.ac.id</a><br>
                            WhatsApp: +62 812-3456-7890 (Senin–Jumat 08:00–16:00 WIB)
                        </p>
                    </x-ui.card>
                </div>

            </div>

        {{-- ========================================================
             CASE D: JADWAL & PENGINGAT / PREFERENSI PEMBELAJARAN
             ======================================================== --}}
        @else
            <x-ui.card class="p-4 sm:p-5 max-w-2xl">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <x-icon :name="$sectionConfig['icon'] ?? 'settings'" :size="15" />
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-text">{{ $sectionConfig['title'] }}</h3>
                        <p class="text-xs text-text-muted mt-0.5">{{ $sectionConfig['subtitle'] }}</p>
                    </div>
                </div>

                <div class="space-y-3">
                    @php
                        $items = $sectionConfig['reminders'] ?? ($sectionConfig['options'] ?? []);
                    @endphp
                    @foreach ($items as $item)
                        <div class="p-3.5 rounded-[8px] border border-line bg-canvas/30 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <h4 class="font-semibold text-xs text-text">{{ $item['label'] }}</h4>
                                <p class="text-[11px] text-text-muted mt-0.5">{{ $item['desc'] }}</p>
                            </div>
                            <div class="shrink-0">
                                <button 
                                    type="button" 
                                    @click="triggerToast('Pengaturan preferensi diperbarui.')"
                                    class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none {{ $item['enabled'] ? 'bg-[#2563EB]' : 'bg-slate-300' }}"
                                >
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition duration-200 ease-in-out mt-0.5 shadow-xs {{ $item['enabled'] ? 'translate-x-4' : 'translate-x-0.5' }}"></span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>
        @endif

    </div>

    <!-- ==========================================
         3. TOAST NOTIFIKASI
         ========================================== -->
    <div 
        x-show="showToast" 
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed bottom-6 right-6 z-50 flex items-center gap-2.5 px-4 py-2.5 rounded-[8px] bg-navy-950 text-white text-xs font-medium shadow-lg border border-white/10"
    >
        <x-icon name="check-circle" :size="15" class="text-emerald-400 shrink-0" />
        <span x-text="toastMessage"></span>
    </div>

</div>
@endsection
