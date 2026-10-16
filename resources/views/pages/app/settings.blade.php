@extends('layouts.app')

@section('title', 'Pengaturan — Arunika')
@section('page_breadcrumb', 'Pengaturan')

@section('content')
<div 
    x-data="{
        activeSection: 'profil',
        toastMessage: '',
        showToast: false,
        passwordModalOpen: false,
        editProfileModalOpen: false,
        profileName: '{{ addslashes($user->name) }}',
        profileEmail: '{{ addslashes($user->email) }}',
        profilePhone: '{{ addslashes($user->phone ?? '') }}',
        avatarPath: '{{ $user->avatar_path ?: '/images/avatar-arya.jpg' }}',
        theme: '{{ $preference->theme === "dark" ? "gelap" : ($preference->theme === "auto" ? "otomatis" : "terang") }}',
        textSize: '{{ $preference->font_size === "small" ? "kecil" : ($preference->font_size === "large" ? "besar" : "normal") }}',
        notifPengumuman: {{ ($preference->notifications['class_announcements'] ?? true) ? 'true' : 'false' }},
        notifTugas: {{ ($preference->notifications['assignments_deadlines'] ?? true) ? 'true' : 'false' }},
        notifPesan: {{ ($preference->notifications['private_messages'] ?? true) ? 'true' : 'false' }},
        notifDiskusi: {{ ($preference->notifications['discussion_activities'] ?? false) ? 'true' : 'false' }},
        notifSistem: {{ ($preference->notifications['system_notifications'] ?? true) ? 'true' : 'false' }},
        twoFactorEnabled: {{ $preference->two_factor_enabled ? 'true' : 'false' }},
        googleConnected: {{ !empty($preference->google_id) ? 'true' : 'false' }},
        language: '{{ $preference->locale ?? 'id' }}',
        timezone: '{{ $preference->timezone ?? 'Asia/Jakarta' }}',
        dateFormat: '{{ $preference->date_format ?? 'DD MMM YYYY' }}',
        timeFormat: '{{ $preference->time_format ?? 'HH:mm' }}',
        triggerToast(msg) {
            this.toastMessage = msg;
            this.showToast = true;
            setTimeout(() => { this.showToast = false; }, 3000);
        },
        async savePreferences(payload) {
            try {
                const res = await fetch('{{ route('app.settings.preferences') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (!res.ok) {
                    console.error('Save preferences failed', data);
                }
            } catch(e) {
                console.error(e);
            }
        },
        async submitPasswordForm(e) {
            const form = e.target;
            const formData = new FormData(form);
            try {
                const res = await fetch('{{ route('app.settings.password') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.passwordModalOpen = false;
                    this.triggerToast(data.message || 'Password berhasil diperbarui!');
                    form.reset();
                } else {
                    this.triggerToast(data.message || 'Gagal mengubah password');
                }
            } catch (err) {
                this.triggerToast('Terjadi kesalahan server saat mengubah password');
            }
        },
        async submitProfileForm(e) {
            const form = e.target;
            const formData = new FormData(form);
            try {
                const res = await fetch('{{ route('app.settings.profile') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.editProfileModalOpen = false;
                    if (data.user) {
                        this.profileName = data.user.name;
                        this.profileEmail = data.user.email;
                    }
                    this.triggerToast(data.message || 'Perubahan profil berhasil disimpan!');
                } else {
                    this.triggerToast(data.message || 'Gagal menyimpan profil');
                }
            } catch (err) {
                this.triggerToast('Terjadi kesalahan server saat memperbarui profil');
            }
        },
        async uploadAvatar(e) {
            const file = e.target.files[0];
            if (!file) return;
            const formData = new FormData();
            formData.append('avatar', file);
            formData.append('name', this.profileName);
            formData.append('email', this.profileEmail);
            try {
                const res = await fetch('{{ route('app.settings.profile') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    if (data.user && data.user.avatar_path) {
                        this.avatarPath = data.user.avatar_path;
                    }
                    this.triggerToast('Foto profil berhasil diunggah!');
                } else {
                    this.triggerToast(data.message || 'Gagal mengunggah foto profil');
                }
            } catch(err) {
                this.triggerToast('Terjadi kesalahan saat mengunggah foto');
            }
        }
    }"
    class="space-y-5"
>
    
    <!-- Hidden Avatar File Input -->
    <input type="file" id="avatarFileInput" accept="image/*" class="hidden" @change="uploadAvatar($event)">

    <!-- ==========================================
         1. BREADCRUMB & HEADER SECTION
         ========================================== -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <!-- Left: Page Icon, Title & Subtitle -->
        <div class="flex items-start gap-3.5">
            <div class="w-11 h-11 rounded-full bg-[#1A2E5E] flex items-center justify-center text-white shrink-0 shadow-2xs mt-0.5">
                <x-icon name="settings" :size="20" />
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-navy-950 tracking-tight">
                    Pengaturan
                </h1>
                <p class="text-xs text-text-muted mt-0.5">
                    Kelola profil, akun, notifikasi, tampilan, dan preferensi lainnya.
                </p>
            </div>
        </div>

        <!-- Right: Inspirational Quote Banner (Persis Gambar Acuan) -->
        <div class="relative overflow-hidden rounded-[10px] border border-blue-100 bg-gradient-to-r from-[#EFF6FF] via-[#EEF2FF] to-[#DBEAFE] p-3 sm:px-4 sm:py-2.5 flex items-center justify-between min-w-[280px] sm:min-w-[340px] shadow-2xs">
            <div class="relative z-10 max-w-[220px]">
                <p class="text-xs italic text-blue-950 font-medium leading-relaxed">
                    &ldquo;Pengaturan yang tepat,<br>
                    pengalaman belajar yang lebih baik.&rdquo;
                </p>
                <span class="text-[10px] text-blue-600 font-medium block mt-0.5">&mdash; Arunika</span>
            </div>
            
            <!-- Mountain & Sunrise Vector Illustration -->
            <div class="absolute right-0 inset-y-0 w-36 pointer-events-none flex items-end justify-end overflow-hidden">
                <img src="/images/settings-sunrise-art.png" alt="Arunika Sunrise" class="h-full w-auto object-cover object-left opacity-95">
            </div>
        </div>

    </div>

    <!-- ==========================================
         2. MAIN LAYOUT: SUB-NAV KIRI + GRID PANEL KANAN
         ========================================== -->
    <div class="flex flex-col lg:flex-row items-start gap-5">
        
        <!-- ==========================================
             KOLOM KIRI: SUB-NAVIGASI PENGATURAN (Persis Gambar Acuan)
             ========================================== -->
        <aside class="w-full lg:w-56 shrink-0 bg-surface border border-line rounded-[10px] p-3 shadow-2xs divide-y divide-line/70">
            
            <!-- Grup 1: Akun Saya -->
            <div class="pb-3 space-y-1">
                <span class="px-2.5 text-[11px] font-bold text-text-muted uppercase tracking-wider block mb-1.5">
                    Akun Saya
                </span>
                
                <button 
                    type="button" 
                    @click="activeSection = 'profil'"
                    :class="activeSection === 'profil' ? 'bg-blue-50/80 text-[#2563EB] font-semibold' : 'text-text-muted hover:text-text hover:bg-canvas/60 font-medium'"
                    class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[6px] text-xs transition-colors cursor-pointer text-left"
                >
                    <x-icon name="user" :size="14" />
                    <span>Profil &amp; Informasi</span>
                </button>

                <button 
                    type="button" 
                    @click="activeSection = 'keamanan'"
                    :class="activeSection === 'keamanan' ? 'bg-blue-50/80 text-[#2563EB] font-semibold' : 'text-text-muted hover:text-text hover:bg-canvas/60 font-medium'"
                    class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[6px] text-xs transition-colors cursor-pointer text-left"
                >
                    <x-icon name="shield-check" :size="14" />
                    <span>Keamanan Akun</span>
                </button>

                <a 
                    href="{{ route('app.settings.section', 'preferensi') }}"
                    class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[6px] text-xs text-text-muted hover:text-[#2563EB] hover:bg-canvas/60 font-medium transition-colors cursor-pointer text-left"
                >
                    <x-icon name="sparkles" :size="14" />
                    <span>Preferensi Pembelajaran</span>
                </a>
            </div>

            <!-- Grup 2: Notifikasi -->
            <div class="py-3 space-y-1">
                <span class="px-2.5 text-[11px] font-bold text-text-muted uppercase tracking-wider block mb-1.5">
                    Notifikasi
                </span>

                <button 
                    type="button" 
                    @click="activeSection = 'notifikasi'"
                    :class="activeSection === 'notifikasi' ? 'bg-blue-50/80 text-[#2563EB] font-semibold' : 'text-text-muted hover:text-text hover:bg-canvas/60 font-medium'"
                    class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[6px] text-xs transition-colors cursor-pointer text-left"
                >
                    <x-icon name="bell" :size="14" />
                    <span>Notifikasi</span>
                </button>

                <a 
                    href="{{ route('app.settings.section', 'jadwal') }}"
                    class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[6px] text-xs text-text-muted hover:text-[#2563EB] hover:bg-canvas/60 font-medium transition-colors cursor-pointer text-left"
                >
                    <x-icon name="calendar" :size="14" />
                    <span>Jadwal &amp; Pengingat</span>
                </a>
            </div>

            <!-- Grup 3: Tampilan -->
            <div class="py-3 space-y-1">
                <span class="px-2.5 text-[11px] font-bold text-text-muted uppercase tracking-wider block mb-1.5">
                    Tampilan
                </span>

                <button 
                    type="button" 
                    @click="activeSection = 'tema'"
                    :class="activeSection === 'tema' ? 'bg-blue-50/80 text-[#2563EB] font-semibold' : 'text-text-muted hover:text-text hover:bg-canvas/60 font-medium'"
                    class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[6px] text-xs transition-colors cursor-pointer text-left"
                >
                    <x-icon name="sun" :size="14" />
                    <span>Tema &amp; Mode Gelap</span>
                </button>

                <button 
                    type="button" 
                    @click="activeSection = 'bahasa'"
                    :class="activeSection === 'bahasa' ? 'bg-blue-50/80 text-[#2563EB] font-semibold' : 'text-text-muted hover:text-text hover:bg-canvas/60 font-medium'"
                    class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[6px] text-xs transition-colors cursor-pointer text-left"
                >
                    <x-icon name="globe" :size="14" />
                    <span>Bahasa &amp; Lokalisasi</span>
                </button>
            </div>

            <!-- Grup 4: Integrasi -->
            <div class="py-3 space-y-1">
                <span class="px-2.5 text-[11px] font-bold text-text-muted uppercase tracking-wider block mb-1.5">
                    Integrasi
                </span>

                <a 
                    href="{{ route('app.settings.section', 'aplikasi-terhubung') }}"
                    class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[6px] text-xs text-text-muted hover:text-[#2563EB] hover:bg-canvas/60 font-medium transition-colors cursor-pointer text-left"
                >
                    <x-icon name="share-2" :size="14" />
                    <span>Aplikasi Terhubung</span>
                </a>

                <a 
                    href="{{ route('app.settings.section', 'webhook-api') }}"
                    class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[6px] text-xs text-text-muted hover:text-[#2563EB] hover:bg-canvas/60 font-medium transition-colors cursor-pointer text-left"
                >
                    <x-icon name="link" :size="14" />
                    <span>Webhook &amp; API</span>
                </a>
            </div>

            <!-- Grup 5: Lainnya -->
            <div class="pt-3 space-y-1">
                <span class="px-2.5 text-[11px] font-bold text-text-muted uppercase tracking-wider block mb-1.5">
                    Lainnya
                </span>

                <a 
                    href="{{ route('app.settings.section', 'bantuan-dukungan') }}"
                    class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[6px] text-xs text-text-muted hover:text-[#2563EB] hover:bg-canvas/60 font-medium transition-colors cursor-pointer text-left"
                >
                    <x-icon name="help-circle" :size="14" class="text-text-muted" />
                    <span>Bantuan &amp; Dukungan</span>
                </a>

                <button 
                    type="button" 
                    @click="triggerToast('Arunika LMS v2.4 (Navy Academic Edition)')"
                    class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[6px] text-xs text-text-muted hover:text-text hover:bg-canvas/60 font-medium transition-colors cursor-pointer text-left"
                >
                    <x-icon name="info" :size="14" class="text-text-muted" />
                    <span>Tentang Arunika</span>
                </button>
            </div>

        </aside>

        <!-- ==========================================
             KOLOM KANAN: PANEL KONTEN PENGATURAN (Persis Gambar Acuan)
             ========================================== -->
        <main class="flex-1 w-full space-y-4 min-w-0">
            
            <!-- GRID 2-KOLOM (KIRI & KANAN) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                
                <!-- ==========================================
                     1. KARTU INFORMASI PROFIL (KIRI ATAS)
                     ========================================== -->
                <x-ui.card class="p-4 sm:p-5">
                    <!-- Card Header -->
                    <div class="flex items-start gap-3 mb-4">
                        <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <x-icon name="file-text" :size="16" />
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-text">
                                Informasi Profil
                            </h3>
                            <p class="text-xs text-text-muted mt-0.5">
                                Kelola informasi pribadi dan foto profil Anda.
                            </p>
                        </div>
                    </div>

                    <!-- Profile Avatar & Details -->
                    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">
                        
                        <!-- Avatar Block -->
                        <div class="flex flex-col items-center shrink-0">
                            <div class="relative">
                                <div class="w-20 h-20 rounded-full overflow-hidden shadow-2xs border-2 border-white ring-1 ring-line">
                                    <img :src="avatarPath" src="{{ $user->avatar_path ?: '/images/avatar-arya.jpg' }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                </div>
                                <button 
                                    type="button" 
                                    @click="document.getElementById('avatarFileInput').click()"
                                    class="absolute bottom-0 right-0 w-6 h-6 rounded-full bg-[#1A2E5E] text-white flex items-center justify-center shadow-xs hover:bg-blue-700 transition-colors cursor-pointer"
                                    title="Ubah Foto"
                                >
                                    <x-icon name="camera" :size="12" />
                                </button>
                            </div>
                            
                            <button 
                                type="button" 
                                @click="document.getElementById('avatarFileInput').click()"
                                class="mt-3 px-3 py-1.5 rounded-[6px] border border-line bg-surface hover:bg-canvas text-xs font-medium text-text transition-colors cursor-pointer shadow-2xs"
                            >
                                Ubah Foto Profil
                            </button>
                        </div>

                        <!-- User Info Field List -->
                        <div class="flex-1 min-w-0 w-full space-y-2.5">
                            
                            <!-- Nama Lengkap -->
                            <div class="flex items-center justify-between pb-1.5 border-b border-line/60">
                                <div>
                                    <span class="text-[10px] text-text-muted block">Nama Lengkap</span>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-semibold text-text" x-text="profileName">{{ $user->name }}</span>
                                        <x-icon name="external-link" :size="11" class="text-text-muted" />
                                    </div>
                                </div>
                                <button type="button" @click="editProfileModalOpen = true" class="text-text-muted hover:text-[#2563EB] p-1 cursor-pointer">
                                    <x-icon name="pencil" :size="12" />
                                </button>
                            </div>

                            <!-- Email -->
                            <div class="flex items-center justify-between pb-1.5 border-b border-line/60">
                                <div>
                                    <span class="text-[10px] text-text-muted block">Email</span>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-medium text-text truncate max-w-[170px]" x-text="profileEmail">{{ $user->email }}</span>
                                        <x-icon name="external-link" :size="11" class="text-text-muted" />
                                    </div>
                                </div>
                                <button type="button" @click="editProfileModalOpen = true" class="text-text-muted hover:text-[#2563EB] p-1 cursor-pointer">
                                    <x-icon name="pencil" :size="12" />
                                </button>
                            </div>

                            <!-- NIM -->
                            <div class="flex items-center justify-between pb-1.5 border-b border-line/60">
                                <div>
                                    <span class="text-[10px] text-text-muted block">NIM</span>
                                    <span class="text-xs font-medium text-text tabular-nums">{{ $user->identifier ?? '23060101024' }}</span>
                                </div>
                                <button type="button" @click="editProfileModalOpen = true" class="text-text-muted hover:text-[#2563EB] p-1 cursor-pointer">
                                    <x-icon name="pencil" :size="12" />
                                </button>
                            </div>

                            <!-- Program Studi -->
                            <div class="flex items-center justify-between pb-1.5 border-b border-line/60">
                                <div>
                                    <span class="text-[10px] text-text-muted block">Program Studi</span>
                                    <span class="text-xs font-medium text-text">{{ $program->name ?? 'S1 Teknik Informatika' }}</span>
                                </div>
                                <button type="button" @click="editProfileModalOpen = true" class="text-text-muted hover:text-[#2563EB] p-1 cursor-pointer">
                                    <x-icon name="pencil" :size="12" />
                                </button>
                            </div>

                            <!-- Fakultas -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] text-text-muted block">Fakultas</span>
                                    <span class="text-xs font-medium text-text">{{ $faculty->name ?? 'Fakultas Ilmu Komputer' }}</span>
                                </div>
                                <button type="button" @click="editProfileModalOpen = true" class="text-text-muted hover:text-[#2563EB] p-1 cursor-pointer">
                                    <x-icon name="pencil" :size="12" />
                                </button>
                            </div>

                        </div>

                    </div>
                </x-ui.card>

                <!-- ==========================================
                     2. KARTU STATUS AKUN (KANAN ATAS)
                     ========================================== -->
                <x-ui.card class="p-4 sm:p-5">
                    <!-- Card Header -->
                    <div class="flex items-start gap-3 mb-4">
                        <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <x-icon name="shield-check" :size="16" />
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-text">
                                Status Akun
                            </h3>
                            <p class="text-xs text-text-muted mt-0.5">
                                Informasi status akun dan peran Anda di sistem.
                            </p>
                        </div>
                    </div>

                    <!-- Green Active Status Box -->
                    <div class="bg-emerald-50/70 border border-emerald-200/80 rounded-[8px] p-3 flex items-center gap-3 mb-4">
                        <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0">
                            <x-icon name="check" :size="14" strokeWidth="2.5" />
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-bold text-xs text-emerald-950">
                                {{ $user->is_active ? 'Akun Aktif' : 'Status Akun' }}
                            </h4>
                            <p class="text-[11px] text-emerald-800 mt-0.5">
                                Anda dapat mengakses semua fitur yang tersedia.
                            </p>
                        </div>
                    </div>

                    <!-- Account Metadata List -->
                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between pb-2 border-b border-line/60">
                            <span class="text-text-muted">Role</span>
                            <span class="font-semibold text-text">{{ $user->role->label() ?? 'Mahasiswa' }}</span>
                        </div>
                        <div class="flex items-center justify-between pb-2 border-b border-line/60">
                            <span class="text-text-muted">Kampus</span>
                            <span class="font-semibold text-text">{{ $campus->name ?? 'Universitas Nusantara Indonesia' }}</span>
                        </div>
                        <div class="flex items-center justify-between pb-2 border-b border-line/60">
                            <span class="text-text-muted">Prodi</span>
                            <span class="font-semibold text-text">{{ $program->name ?? 'S1 Teknik Informatika' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-text-muted">Terdaftar Sejak</span>
                            <span class="font-semibold text-text tabular-nums">
                                {{ $user->created_at ? $user->created_at->isoFormat('D MMMM Y') : '12 Agustus 2025' }}
                            </span>
                        </div>
                    </div>
                </x-ui.card>

                <!-- ==========================================
                     3. KARTU KEAMANAN AKUN (KIRI TENGAH)
                     ========================================== -->
                <x-ui.card class="p-4 sm:p-5">
                    <!-- Card Header -->
                    <div class="flex items-start gap-3 mb-4">
                        <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <x-icon name="shield-check" :size="16" />
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-text">
                                Keamanan Akun
                            </h3>
                            <p class="text-xs text-text-muted mt-0.5">
                                Kelola kata sandi dan autentikasi dua faktor untuk keamanan akun Anda.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2.5">
                        
                        <!-- Row 1: Password -->
                        <div class="p-3 rounded-[8px] border border-line bg-canvas/30 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                    <x-icon name="lock" :size="14" />
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-semibold text-xs text-text">Password</h4>
                                    <p class="text-[11px] text-text-muted mt-0.5 truncate">Terakhir diubah 3 hari yang lalu</p>
                                </div>
                            </div>
                            <button 
                                type="button" 
                                @click="passwordModalOpen = true"
                                class="px-3 py-1.5 rounded-[6px] border border-line bg-surface hover:bg-canvas text-xs font-semibold text-[#2563EB] shrink-0 transition-colors cursor-pointer shadow-2xs"
                            >
                                Ubah Password
                            </button>
                        </div>

                        <!-- Row 2: 2FA -->
                        <div class="p-3 rounded-[8px] border border-line bg-canvas/30 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                    <x-icon name="shield-check" :size="14" />
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-semibold text-xs text-text">Two-Factor Authentication (2FA)</h4>
                                    <p class="text-[11px] text-text-muted mt-0.5 truncate">Tambahkan lapisan keamanan ekstra untuk akun Anda.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <button 
                                    type="button" 
                                    @click="twoFactorEnabled = !twoFactorEnabled; savePreferences({ twoFactorEnabled: twoFactorEnabled }); triggerToast(twoFactorEnabled ? '2FA diaktifkan' : '2FA dinonaktifkan')"
                                    :class="twoFactorEnabled ? 'bg-[#2563EB]' : 'bg-slate-300'"
                                    class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none"
                                >
                                    <span 
                                        :class="twoFactorEnabled ? 'translate-x-4' : 'translate-x-0.5'"
                                        class="inline-block h-4 w-4 transform rounded-full bg-white transition duration-200 ease-in-out mt-0.5 shadow-xs"
                                    ></span>
                                </button>
                                <span class="text-xs text-text-muted" x-text="twoFactorEnabled ? 'Aktif' : 'Nonaktif'">Nonaktif</span>
                            </div>
                        </div>

                        <!-- Row 3: Login Google -->
                        <div class="p-3 rounded-[8px] border border-line bg-canvas/30 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0 text-xs font-bold">
                                    <!-- Google Colored G -->
                                    <svg viewBox="0 0 24 24" class="w-4 h-4">
                                        <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.66v3.04h3.88c2.27-2.09 3.665-5.17 3.665-9.14z"/>
                                        <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.04c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.26v3.13C3.25 21.37 7.33 24 12 24z"/>
                                        <path fill="#FBBC05" d="M5.28 14.28c-.25-.72-.38-1.49-.38-2.28s.13-1.56.38-2.28V6.59H1.26C.46 8.19 0 9.99 0 12s.46 3.81 1.26 5.41l4.02-3.13z"/>
                                        <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.25 2.63 1.26 6.59l4.02 3.13c.95-2.83 3.6-4.97 6.72-4.97z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-semibold text-xs text-text">Login dengan Google</h4>
                                    <p class="text-[11px] text-text-muted mt-0.5 truncate">Hubungkan akun Google untuk akses lebih cepat.</p>
                                </div>
                            </div>
                            <button 
                                type="button" 
                                @click="googleConnected = !googleConnected; savePreferences({ googleConnected: googleConnected }); triggerToast(googleConnected ? 'Akun Google berhasil dihubungkan' : 'Koneksi akun Google diputus')"
                                class="px-3 py-1.5 rounded-[6px] border border-line bg-surface hover:bg-canvas text-xs font-semibold text-[#2563EB] shrink-0 inline-flex items-center gap-1.5 transition-colors cursor-pointer shadow-2xs"
                            >
                                <svg viewBox="0 0 24 24" class="w-3.5 h-3.5">
                                    <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.66v3.04h3.88c2.27-2.09 3.665-5.17 3.665-9.14z"/>
                                    <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.04c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.26v3.13C3.25 21.37 7.33 24 12 24z"/>
                                    <path fill="#FBBC05" d="M5.28 14.28c-.25-.72-.38-1.49-.38-2.28s.13-1.56.38-2.28V6.59H1.26C.46 8.19 0 9.99 0 12s.46 3.81 1.26 5.41l4.02-3.13z"/>
                                    <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.25 2.63 1.26 6.59l4.02 3.13c.95-2.83 3.6-4.97 6.72-4.97z"/>
                                </svg>
                                <span x-text="googleConnected ? 'Terhubung' : 'Hubungkan'">Hubungkan</span>
                            </button>
                        </div>

                    </div>
                </x-ui.card>

                <!-- ==========================================
                     4. KARTU NOTIFIKASI (KANAN TENGAH)
                     ========================================== -->
                <x-ui.card class="p-4 sm:p-5">
                    <!-- Card Header -->
                    <div class="flex items-start gap-3 mb-4">
                        <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <x-icon name="bell" :size="16" />
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-text">
                                Notifikasi
                            </h3>
                            <p class="text-xs text-text-muted mt-0.5">
                                Atur jenis notifikasi yang ingin Anda terima.
                            </p>
                        </div>
                    </div>

                    <!-- 5 Notification Toggles List -->
                    <div class="space-y-3">
                        
                        <!-- 1. Pengumuman Kelas -->
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-7 h-7 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                    <x-icon name="megaphone" :size="13" />
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-semibold text-xs text-text">Pengumuman Kelas</h4>
                                    <p class="text-[11px] text-text-muted truncate">Notifikasi pengumuman dari dosen</p>
                                </div>
                            </div>
                            <button 
                                type="button" 
                                @click="notifPengumuman = !notifPengumuman; savePreferences({ notifPengumuman: notifPengumuman }); triggerToast(notifPengumuman ? 'Notifikasi Pengumuman diaktifkan' : 'Notifikasi Pengumuman dimatikan')"
                                :class="notifPengumuman ? 'bg-[#2563EB]' : 'bg-slate-300'"
                                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none"
                            >
                                <span 
                                    :class="notifPengumuman ? 'translate-x-4' : 'translate-x-0.5'"
                                    class="inline-block h-4 w-4 transform rounded-full bg-white transition duration-200 ease-in-out mt-0.5 shadow-xs"
                                ></span>
                            </button>
                        </div>

                        <!-- 2. Tugas & Deadline -->
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-7 h-7 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                    <x-icon name="calendar" :size="13" />
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-semibold text-xs text-text">Tugas &amp; Deadline</h4>
                                    <p class="text-[11px] text-text-muted truncate">Pengingat tugas dan tenggat waktu</p>
                                </div>
                            </div>
                            <button 
                                type="button" 
                                @click="notifTugas = !notifTugas; savePreferences({ notifTugas: notifTugas }); triggerToast(notifTugas ? 'Notifikasi Tugas diaktifkan' : 'Notifikasi Tugas dimatikan')"
                                :class="notifTugas ? 'bg-[#2563EB]' : 'bg-slate-300'"
                                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none"
                            >
                                <span 
                                    :class="notifTugas ? 'translate-x-4' : 'translate-x-0.5'"
                                    class="inline-block h-4 w-4 transform rounded-full bg-white transition duration-200 ease-in-out mt-0.5 shadow-xs"
                                ></span>
                            </button>
                        </div>

                        <!-- 3. Pesan Pribadi -->
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-7 h-7 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                    <x-icon name="message-square" :size="13" />
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-semibold text-xs text-text">Pesan Pribadi</h4>
                                    <p class="text-[11px] text-text-muted truncate">Pesan dari dosen dan teman</p>
                                </div>
                            </div>
                            <button 
                                type="button" 
                                @click="notifPesan = !notifPesan; savePreferences({ notifPesan: notifPesan }); triggerToast(notifPesan ? 'Notifikasi Pesan Pribadi diaktifkan' : 'Notifikasi Pesan Pribadi dimatikan')"
                                :class="notifPesan ? 'bg-[#2563EB]' : 'bg-slate-300'"
                                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none"
                            >
                                <span 
                                    :class="notifPesan ? 'translate-x-4' : 'translate-x-0.5'"
                                    class="inline-block h-4 w-4 transform rounded-full bg-white transition duration-200 ease-in-out mt-0.5 shadow-xs"
                                ></span>
                            </button>
                        </div>

                        <!-- 4. Aktivitas Diskusi -->
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-7 h-7 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                    <x-icon name="message-circle" :size="13" />
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-semibold text-xs text-text">Aktivitas Diskusi</h4>
                                    <p class="text-[11px] text-text-muted truncate">Balasan dan mention di forum</p>
                                </div>
                            </div>
                            <button 
                                type="button" 
                                @click="notifDiskusi = !notifDiskusi; savePreferences({ notifDiskusi: notifDiskusi }); triggerToast(notifDiskusi ? 'Notifikasi Diskusi diaktifkan' : 'Notifikasi Diskusi dimatikan')"
                                :class="notifDiskusi ? 'bg-[#2563EB]' : 'bg-slate-300'"
                                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none"
                            >
                                <span 
                                    :class="notifDiskusi ? 'translate-x-4' : 'translate-x-0.5'"
                                    class="inline-block h-4 w-4 transform rounded-full bg-white transition duration-200 ease-in-out mt-0.5 shadow-xs"
                                ></span>
                            </button>
                        </div>

                        <!-- 5. Notifikasi Sistem -->
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-7 h-7 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                    <x-icon name="bell" :size="13" />
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-semibold text-xs text-text">Notifikasi Sistem</h4>
                                    <p class="text-[11px] text-text-muted truncate">Update sistem dan informasi penting</p>
                                </div>
                            </div>
                            <button 
                                type="button" 
                                @click="notifSistem = !notifSistem; savePreferences({ notifSistem: notifSistem }); triggerToast(notifSistem ? 'Notifikasi Sistem diaktifkan' : 'Notifikasi Sistem dimatikan')"
                                :class="notifSistem ? 'bg-[#2563EB]' : 'bg-slate-300'"
                                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none"
                            >
                                <span 
                                    :class="notifSistem ? 'translate-x-4' : 'translate-x-0.5'"
                                    class="inline-block h-4 w-4 transform rounded-full bg-white transition duration-200 ease-in-out mt-0.5 shadow-xs"
                                ></span>
                            </button>
                        </div>

                    </div>
                </x-ui.card>

                <!-- ==========================================
                     5. KARTU TAMPILAN (KIRI BAWAH)
                     ========================================== -->
                <x-ui.card class="p-4 sm:p-5">
                    <!-- Card Header -->
                    <div class="flex items-start gap-3 mb-4">
                        <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <x-icon name="monitor" :size="16" />
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-text">
                                Tampilan
                            </h3>
                            <p class="text-xs text-text-muted mt-0.5">
                                Sesuaikan tampilan aplikasi sesuai preferensi Anda.
                            </p>
                        </div>
                    </div>

                    <!-- Layout: Tema (Kiri) & Ukuran Teks (Kanan) -->
                    <div class="flex flex-col sm:flex-row gap-5">
                        
                        <!-- Bagian Kiri: 3 Kartu Tema -->
                        <div class="flex-1 min-w-0">
                            <span class="text-xs font-semibold text-text block mb-2">Tema</span>
                            <div class="grid grid-cols-3 gap-2">
                                
                                <!-- Card 1: Terang (Active Default) -->
                                <button 
                                    type="button" 
                                    @click="theme = 'terang'; savePreferences({ theme: 'terang' }); triggerToast('Tema Terang aktif (Default)')"
                                    :class="theme === 'terang' ? 'border-[#2563EB] bg-blue-50/40 ring-1 ring-[#2563EB]/40' : 'border-line bg-surface hover:bg-canvas/50'"
                                    class="relative p-2.5 rounded-[8px] border text-left transition-all cursor-pointer shadow-2xs"
                                >
                                    <div class="flex items-center justify-between mb-1">
                                        <x-icon name="sun" :size="15" />
                                        <span x-show="theme === 'terang'" class="w-1.5 h-1.5 rounded-full bg-[#2563EB]"></span>
                                    </div>
                                    <span class="font-bold text-xs text-text block">Terang</span>
                                    <span class="text-[10px] text-text-muted block mt-0.5 leading-tight">Mode default</span>
                                </button>

                                <!-- Card 2: Gelap -->
                                <button 
                                    type="button" 
                                    @click="theme = 'gelap'; savePreferences({ theme: 'gelap' }); triggerToast('Mode gelap diaktifkan')"
                                    :class="theme === 'gelap' ? 'border-[#2563EB] bg-blue-50/40 ring-1 ring-[#2563EB]/40' : 'border-line bg-surface hover:bg-canvas/50'"
                                    class="relative p-2.5 rounded-[8px] border text-left transition-all cursor-pointer shadow-2xs"
                                >
                                    <div class="flex items-center justify-between mb-1">
                                        <x-icon name="moon" :size="15" />
                                        <span x-show="theme === 'gelap'" class="w-1.5 h-1.5 rounded-full bg-[#2563EB]"></span>
                                    </div>
                                    <span class="font-bold text-xs text-text block">Gelap</span>
                                    <span class="text-[10px] text-text-muted block mt-0.5 leading-tight">Lebih nyaman di malam hari</span>
                                </button>

                                <!-- Card 3: Otomatis -->
                                <button 
                                    type="button" 
                                    @click="theme = 'otomatis'; savePreferences({ theme: 'otomatis' }); triggerToast('Tema Otomatis mengikuti preferensi sistem')"
                                    :class="theme === 'otomatis' ? 'border-[#2563EB] bg-blue-50/40 ring-1 ring-[#2563EB]/40' : 'border-line bg-surface hover:bg-canvas/50'"
                                    class="relative p-2.5 rounded-[8px] border text-left transition-all cursor-pointer shadow-2xs"
                                >
                                    <div class="flex items-center justify-between mb-1">
                                        <x-icon name="monitor" :size="15" />
                                        <span x-show="theme === 'otomatis'" class="w-1.5 h-1.5 rounded-full bg-[#2563EB]"></span>
                                    </div>
                                    <span class="font-bold text-xs text-text block">Otomatis</span>
                                    <span class="text-[10px] text-text-muted block mt-0.5 leading-tight">Mengikuti sistem</span>
                                </button>

                            </div>
                        </div>

                        <!-- Bagian Kanan: Ukuran Teks (Radio List) -->
                        <div class="w-full sm:w-28 shrink-0">
                            <span class="text-xs font-semibold text-text block mb-2">Ukuran Teks</span>
                            <div class="space-y-2 text-xs">
                                
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input 
                                        type="radio" 
                                        name="textSize" 
                                        value="kecil" 
                                        x-model="textSize" 
                                        @change="savePreferences({ textSize: 'kecil' }); triggerToast('Ukuran teks disetel ke Kecil')"
                                        class="w-3.5 h-3.5 text-[#2563EB] border-line focus:ring-[#2563EB]"
                                    >
                                    <span class="text-text">Kecil</span>
                                </label>

                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input 
                                        type="radio" 
                                        name="textSize" 
                                        value="normal" 
                                        x-model="textSize" 
                                        @change="savePreferences({ textSize: 'normal' }); triggerToast('Ukuran teks disetel ke Normal (Default)')"
                                        class="w-3.5 h-3.5 text-[#2563EB] border-line focus:ring-[#2563EB]"
                                    >
                                    <span class="font-semibold text-text">Normal</span>
                                </label>

                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input 
                                        type="radio" 
                                        name="textSize" 
                                        value="besar" 
                                        x-model="textSize" 
                                        @change="savePreferences({ textSize: 'besar' }); triggerToast('Ukuran teks disetel ke Besar')"
                                        class="w-3.5 h-3.5 text-[#2563EB] border-line focus:ring-[#2563EB]"
                                    >
                                    <span class="text-text">Besar</span>
                                </label>

                            </div>
                        </div>

                    </div>
                </x-ui.card>

                <!-- ==========================================
                     6. KARTU BAHASA & LOKALISASI (KANAN BAWAH)
                     ========================================== -->
                <x-ui.card class="p-4 sm:p-5">
                    <!-- Card Header -->
                    <div class="flex items-start gap-3 mb-4">
                        <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <x-icon name="globe" :size="16" />
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-text">
                                Bahasa &amp; Lokalisasi
                            </h3>
                            <p class="text-xs text-text-muted mt-0.5">
                                Atur bahasa dan format regional Anda.
                            </p>
                        </div>
                    </div>

                    <!-- 4 Select Inputs (Grid 2x2) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        
                        <!-- 1. Bahasa -->
                        <div>
                            <label class="block text-[11px] text-text-muted mb-1">Bahasa</label>
                            <div class="relative">
                                <select 
                                    x-model="language" 
                                    @change="savePreferences({ language: language }); triggerToast('Bahasa sistem diperbarui')"
                                    class="w-full appearance-none px-3 py-1.5 rounded-[6px] border border-line bg-surface text-text shadow-2xs font-medium focus:outline-none focus:border-[#2563EB] cursor-pointer"
                                >
                                    <option value="id">Bahasa Indonesia</option>
                                    <option value="en">English (US)</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-text-muted">
                                    <x-icon name="chevron-down" :size="13" />
                                </div>
                            </div>
                        </div>

                        <!-- 2. Zona Waktu -->
                        <div>
                            <label class="block text-[11px] text-text-muted mb-1">Zona Waktu</label>
                            <div class="relative">
                                <select 
                                    x-model="timezone" 
                                    @change="savePreferences({ timezone: timezone }); triggerToast('Zona waktu diperbarui')"
                                    class="w-full appearance-none px-3 py-1.5 rounded-[6px] border border-line bg-surface text-text shadow-2xs font-medium focus:outline-none focus:border-[#2563EB] cursor-pointer"
                                >
                                    <option value="Asia/Jakarta">Asia/Jakarta (WIB)</option>
                                    <option value="Asia/Makassar">Asia/Makassar (WITA)</option>
                                    <option value="Asia/Jayapura">Asia/Jayapura (WIT)</option>
                                    <option value="UTC">UTC (Universal)</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-text-muted">
                                    <x-icon name="chevron-down" :size="13" />
                                </div>
                            </div>
                        </div>

                        <!-- 3. Format Tanggal -->
                        <div>
                            <label class="block text-[11px] text-text-muted mb-1">Format Tanggal</label>
                            <div class="relative">
                                <select 
                                    x-model="dateFormat" 
                                    @change="savePreferences({ dateFormat: dateFormat }); triggerToast('Format tanggal diperbarui')"
                                    class="w-full appearance-none px-3 py-1.5 rounded-[6px] border border-line bg-surface text-text shadow-2xs font-medium focus:outline-none focus:border-[#2563EB] cursor-pointer"
                                >
                                    <option value="DD MMM YYYY">DD MMM YYYY (24 Sep 2026)</option>
                                    <option value="YYYY-MM-DD">YYYY-MM-DD (2026-09-24)</option>
                                    <option value="DD/MM/YYYY">DD/MM/YYYY (24/09/2026)</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-text-muted">
                                    <x-icon name="chevron-down" :size="13" />
                                </div>
                            </div>
                        </div>

                        <!-- 4. Format Waktu -->
                        <div>
                            <label class="block text-[11px] text-text-muted mb-1">Format Waktu</label>
                            <div class="relative">
                                <select 
                                    x-model="timeFormat" 
                                    @change="savePreferences({ timeFormat: timeFormat }); triggerToast('Format waktu diperbarui')"
                                    class="w-full appearance-none px-3 py-1.5 rounded-[6px] border border-line bg-surface text-text shadow-2xs font-medium focus:outline-none focus:border-[#2563EB] cursor-pointer"
                                >
                                    <option value="HH:mm">HH:mm (24 Jam: 14:30)</option>
                                    <option value="hh:mm A">hh:mm A (12 Jam: 02:30 PM)</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-text-muted">
                                    <x-icon name="chevron-down" :size="13" />
                                </div>
                            </div>
                        </div>

                    </div>
                </x-ui.card>

            </div>

            <!-- ==========================================
                 7. KARTU BANNER BANTUAN (BOTTOM FULL-WIDTH)
                 ========================================== -->
            <div class="bg-blue-50/50 border border-blue-100 rounded-[10px] p-3.5 sm:px-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-100/80 text-blue-600 flex items-center justify-center shrink-0">
                        <x-icon name="info" :size="16" />
                    </div>
                    <div>
                        <h4 class="font-bold text-xs text-navy-950">
                            Butuh bantuan?
                        </h4>
                        <p class="text-xs text-text-muted mt-0.5">
                            Jika Anda mengalami kendala atau memiliki pertanyaan, silakan hubungi tim dukungan kami.
                        </p>
                    </div>
                </div>

                <a 
                    href="{{ route('app.settings.section', 'bantuan-dukungan') }}"
                    class="px-3.5 py-1.5 rounded-[6px] border border-blue-200 bg-surface hover:bg-canvas text-xs font-semibold text-[#2563EB] inline-flex items-center gap-2 transition-colors cursor-pointer shrink-0 shadow-2xs self-end sm:self-auto"
                >
                    <x-icon name="headphones" :size="14" class="text-[#2563EB]" />
                    <span>Hubungi Support</span>
                </a>
            </div>

        </main>

    </div>

    <!-- ==========================================
         8. MODAL UBAH PASSWORD
         ========================================== -->
    <div 
        x-show="passwordModalOpen" 
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog" 
        aria-modal="true"
    >
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <!-- Backdrop -->
            <div 
                x-show="passwordModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-navy-950/40 backdrop-blur-xs transition-opacity" 
                @click="passwordModalOpen = false"
            ></div>

            <!-- Modal Panel -->
            <div 
                x-show="passwordModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-[10px] bg-surface border border-line text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md p-5"
            >
                <div class="flex items-center justify-between pb-3 border-b border-line mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                            <x-icon name="lock" :size="15" />
                        </div>
                        <h3 class="font-bold text-sm text-text">
                            Ubah Password Akun
                        </h3>
                    </div>
                    <button type="button" @click="passwordModalOpen = false" class="text-text-muted hover:text-text cursor-pointer p-1">
                        <x-icon name="x" :size="16" />
                    </button>
                </div>

                <form @submit.prevent="submitPasswordForm($event)" class="space-y-3.5 text-xs">
                    @csrf
                    <div>
                        <label class="block font-medium text-text mb-1">Password Saat Ini</label>
                        <input type="password" name="current_password" required placeholder="Masukkan password lama" class="w-full px-3 py-1.5 rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]">
                    </div>

                    <div>
                        <label class="block font-medium text-text mb-1">Password Baru</label>
                        <input type="password" name="password" required minlength="8" placeholder="Minimal 8 karakter kombinasi huruf & angka" class="w-full px-3 py-1.5 rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]">
                    </div>

                    <div>
                        <label class="block font-medium text-text mb-1">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" required minlength="8" placeholder="Ulangi password baru" class="w-full px-3 py-1.5 rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]">
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-2 border-t border-line">
                        <button 
                            type="button" 
                            @click="passwordModalOpen = false"
                            class="px-3.5 py-1.5 rounded-[6px] border border-line bg-surface hover:bg-canvas text-xs font-medium text-text transition-colors cursor-pointer"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-1.5 rounded-[6px] bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold shadow-2xs transition-colors cursor-pointer"
                        >
                            Simpan Password Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ==========================================
         9. MODAL EDIT INFORMASI PROFIL
         ========================================== -->
    <div 
        x-show="editProfileModalOpen" 
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog" 
        aria-modal="true"
    >
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div 
                x-show="editProfileModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-navy-950/40 backdrop-blur-xs transition-opacity" 
                @click="editProfileModalOpen = false"
            ></div>

            <div 
                x-show="editProfileModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-[10px] bg-surface border border-line text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg p-5"
            >
                <div class="flex items-center justify-between pb-3 border-b border-line mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                            <x-icon name="pencil" :size="15" />
                        </div>
                        <h3 class="font-bold text-sm text-text">
                            Perbarui Informasi Profil
                        </h3>
                    </div>
                    <button type="button" @click="editProfileModalOpen = false" class="text-text-muted hover:text-text cursor-pointer p-1">
                        <x-icon name="x" :size="16" />
                    </button>
                </div>

                <form @submit.prevent="submitProfileForm($event)" class="space-y-3.5 text-xs">
                    @csrf
                    <div>
                        <label class="block font-medium text-text mb-1">Nama Lengkap</label>
                        <input type="text" name="name" x-model="profileName" required class="w-full px-3 py-1.5 rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]">
                    </div>

                    <div>
                        <label class="block font-medium text-text mb-1">Email Kampus</label>
                        <input type="email" name="email" x-model="profileEmail" required class="w-full px-3 py-1.5 rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]">
                    </div>

                    <div>
                        <label class="block font-medium text-text mb-1">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="phone" x-model="profilePhone" placeholder="08xxxxxxxxxx" class="w-full px-3 py-1.5 rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium text-text mb-1">NIM</label>
                            <input type="text" value="{{ $user->identifier ?? '23060101024' }}" readonly class="w-full px-3 py-1.5 rounded-[6px] border border-line bg-canvas text-text-muted cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block font-medium text-text mb-1">Program Studi</label>
                            <input type="text" value="{{ $program->name ?? 'S1 Teknik Informatika' }}" readonly class="w-full px-3 py-1.5 rounded-[6px] border border-line bg-canvas text-text-muted cursor-not-allowed">
                        </div>
                    </div>

                    <div>
                        <label class="block font-medium text-text mb-1">Fakultas</label>
                        <input type="text" value="{{ $faculty->name ?? 'Fakultas Ilmu Komputer' }}" readonly class="w-full px-3 py-1.5 rounded-[6px] border border-line bg-canvas text-text-muted cursor-not-allowed">
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-2 border-t border-line">
                        <button 
                            type="button" 
                            @click="editProfileModalOpen = false"
                            class="px-3.5 py-1.5 rounded-[6px] border border-line bg-surface hover:bg-canvas text-xs font-medium text-text transition-colors cursor-pointer"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-1.5 rounded-[6px] bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold shadow-2xs transition-colors cursor-pointer"
                        >
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ==========================================
         10. TOAST NOTIFIKASI
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
