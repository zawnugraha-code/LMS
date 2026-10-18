@extends('layouts.dosen')

@section('title', 'Pengaturan Akun & Preferensi — Portal Dosen Arunika')
@section('page_breadcrumb', 'Pengaturan')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'profil', panduanModal: false, tiketModal: false }">
    
    <!-- 1. Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-[8px] bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 border border-blue-100">
                <x-icon name="settings" :size="20" />
            </div>
            <div>
                <h1 class="text-2xl font-bold text-text tracking-tight">
                    Pengaturan Akun & Preferensi
                </h1>
                <p class="text-xs sm:text-[13px] text-text-muted mt-0.5 leading-relaxed">
                    Kelola data profil pengajar, preferensi notifikasi, keamanan akun, dan integrasi sistem.
                </p>
            </div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs px-4 py-3 rounded-[6px] flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <x-icon name="circle-check" :size="16" class="text-emerald-600 shrink-0" />
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 cursor-pointer">
                <x-icon name="x" :size="14" />
            </button>
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 text-xs px-4 py-3 rounded-[6px] space-y-1 shadow-xs">
            <div class="flex items-center gap-2 font-semibold">
                <x-icon name="circle-alert" :size="16" class="text-red-600 shrink-0" />
                <span>Terjadi kendala pada isian formulir:</span>
            </div>
            <ul class="list-disc list-inside pl-6 text-[11px] space-y-0.5 text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 2. Tabs Navigasi Pengaturan -->
    <div class="border-b border-line flex items-center gap-2 overflow-x-auto text-xs font-medium">
        <button 
            type="button" 
            @click="activeTab = 'profil'"
            class="flex items-center gap-2 px-4 py-2.5 border-b-2 transition-colors cursor-pointer"
            :class="activeTab === 'profil' ? 'border-[#2563EB] text-[#2563EB] font-bold' : 'border-transparent text-text-muted hover:text-text hover:border-slate-300'"
        >
            <x-icon name="user" :size="15" />
            <span>Profil Pengajar</span>
        </button>

        <button 
            type="button" 
            @click="activeTab = 'notifikasi'"
            class="flex items-center gap-2 px-4 py-2.5 border-b-2 transition-colors cursor-pointer"
            :class="activeTab === 'notifikasi' ? 'border-[#2563EB] text-[#2563EB] font-bold' : 'border-transparent text-text-muted hover:text-text hover:border-slate-300'"
        >
            <x-icon name="bell" :size="15" />
            <span>Preferensi Notifikasi</span>
        </button>

        <button 
            type="button" 
            @click="activeTab = 'keamanan'"
            class="flex items-center gap-2 px-4 py-2.5 border-b-2 transition-colors cursor-pointer"
            :class="activeTab === 'keamanan' ? 'border-[#2563EB] text-[#2563EB] font-bold' : 'border-transparent text-text-muted hover:text-text hover:border-slate-300'"
        >
            <x-icon name="shield" :size="15" />
            <span>Keamanan & Akun</span>
        </button>

        <button 
            type="button" 
            @click="activeTab = 'sistem'"
            class="flex items-center gap-2 px-4 py-2.5 border-b-2 transition-colors cursor-pointer"
            :class="activeTab === 'sistem' ? 'border-[#2563EB] text-[#2563EB] font-bold' : 'border-transparent text-text-muted hover:text-text hover:border-slate-300'"
        >
            <x-icon name="server" :size="15" />
            <span>Status Sistem & Bantuan</span>
        </button>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 1: PROFIL PENGAJAR                                          -->
    <!-- ============================================================== -->
    <div x-show="activeTab === 'profil'" class="space-y-6">
        <form action="{{ route('dosen.pengaturan.profile') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="bg-surface border border-line rounded-[8px] p-6 shadow-xs">
                <div class="flex items-center justify-between pb-4 border-b border-line mb-6">
                    <div>
                        <h2 class="text-sm font-bold text-text tracking-tight">Biodata & Informasi Akademik</h2>
                        <p class="text-xs text-text-muted mt-0.5">Kelola informasi identitas dosen yang tampil pada silabus dan kelas.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-[4px] bg-blue-50 text-[#2563EB] text-[11px] font-semibold border border-blue-200">
                        Dosen Tetap
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                    
                    <!-- Left: Foto Profil Dosen -->
                    <div class="md:col-span-4 flex flex-col items-center text-center p-4 bg-slate-50/60 rounded-[8px] border border-line/70">
                        <div class="w-28 h-28 rounded-full overflow-hidden border-2 border-white shadow-md relative group">
                            <img 
                                src="{{ $lecturer->avatar_path ?? '/images/avatar-budi.jpg' }}" 
                                alt="{{ $lecturer->name }}" 
                                class="w-full h-full object-cover"
                                onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=Budi+Rahardjo&background=2563EB&color=fff';"
                            >
                        </div>

                        <p class="text-xs font-bold text-text mt-3">{{ $lecturer->name }}</p>
                        <p class="text-[11px] text-text-muted">{{ $lecturer->jabatan ?? 'Lektor Kepala' }}</p>

                        <div class="mt-4 w-full">
                            <label for="avatar_input" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-white hover:bg-slate-100 text-text text-xs font-medium rounded-[6px] border border-line transition-colors shadow-2xs cursor-pointer">
                                <x-icon name="upload" :size="13" />
                                <span>Ganti Foto Profil</span>
                            </label>
                            <input type="file" id="avatar_input" name="avatar" accept="image/*" class="hidden">
                            <p class="text-[10px] text-text-subtle mt-1.5">Format: JPG, PNG maks. 2MB</p>
                        </div>

                        <!-- Ringkasan Beban Mengajar -->
                        <div class="w-full mt-5 pt-4 border-t border-line/80 text-left space-y-2 text-xs">
                            <span class="text-[11px] font-semibold text-text-muted uppercase tracking-wider block">Beban Mengajar Aktif</span>
                            <div class="flex items-center justify-between text-text">
                                <span>Kelas Diampu</span>
                                <span class="font-bold text-[#2563EB]">{{ $classesCount }} Kelas</span>
                            </div>
                            <div class="flex items-center justify-between text-text">
                                <span>Total Mahasiswa</span>
                                <span class="font-bold text-[#2563EB]">{{ $studentsCount }} Mahasiswa</span>
                            </div>
                            <div class="flex items-center justify-between text-text">
                                <span>Tahun Akademik</span>
                                <span class="font-semibold text-text-muted">{{ $activeTerm?->name ?? 'Ganjil 2026/2027' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Form Fields -->
                    <div class="md:col-span-8 space-y-4 text-xs">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Nama Lengkap -->
                            <div class="space-y-1">
                                <label for="name" class="font-semibold text-text">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    value="{{ old('name', $lecturer->name) }}" 
                                    required 
                                    class="w-full px-3 py-2 bg-surface border border-line rounded-[6px] text-xs text-text focus:outline-hidden focus:border-[#2563EB]"
                                >
                            </div>

                            <!-- Gelar Lengkap -->
                            <div class="space-y-1">
                                <label for="gelar" class="font-semibold text-text">
                                    Gelar Akademik Lengkap
                                </label>
                                <input 
                                    type="text" 
                                    id="gelar" 
                                    name="gelar" 
                                    value="{{ old('gelar', $lecturer->gelar ?? 'Dr. Budi Rahardjo, S.T., M.Kom.') }}" 
                                    placeholder="Contoh: Dr. Budi Rahardjo, S.T., M.Kom." 
                                    class="w-full px-3 py-2 bg-surface border border-line rounded-[6px] text-xs text-text focus:outline-hidden focus:border-[#2563EB]"
                                >
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- NIDN / NIP -->
                            <div class="space-y-1">
                                <label for="identifier" class="font-semibold text-text">
                                    NIDN / NIP
                                </label>
                                <input 
                                    type="text" 
                                    id="identifier" 
                                    name="identifier" 
                                    value="{{ old('identifier', $lecturer->identifier ?? '0021037502') }}" 
                                    class="w-full px-3 py-2 bg-surface border border-line rounded-[6px] text-xs text-text focus:outline-hidden focus:border-[#2563EB]"
                                >
                            </div>

                            <!-- Jabatan Fungsional -->
                            <div class="space-y-1">
                                <label for="jabatan" class="font-semibold text-text">
                                    Jabatan Fungsional
                                </label>
                                <input 
                                    type="text" 
                                    id="jabatan" 
                                    name="jabatan" 
                                    value="{{ old('jabatan', $lecturer->jabatan ?? 'Lektor Kepala') }}" 
                                    placeholder="Contoh: Lektor Kepala" 
                                    class="w-full px-3 py-2 bg-surface border border-line rounded-[6px] text-xs text-text focus:outline-hidden focus:border-[#2563EB]"
                                >
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Email Institusi -->
                            <div class="space-y-1">
                                <label for="email" class="font-semibold text-text">
                                    Email Institusi <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    value="{{ old('email', $lecturer->email) }}" 
                                    required 
                                    class="w-full px-3 py-2 bg-surface border border-line rounded-[6px] text-xs text-text focus:outline-hidden focus:border-[#2563EB]"
                                >
                            </div>

                            <!-- Nomor Telepon / WhatsApp -->
                            <div class="space-y-1">
                                <label for="phone" class="font-semibold text-text">
                                    Nomor Telepon / WhatsApp
                                </label>
                                <input 
                                    type="text" 
                                    id="phone" 
                                    name="phone" 
                                    value="{{ old('phone', $lecturer->phone ?? '081298765432') }}" 
                                    placeholder="Contoh: 081298765432" 
                                    class="w-full px-3 py-2 bg-surface border border-line rounded-[6px] text-xs text-text focus:outline-hidden focus:border-[#2563EB]"
                                >
                            </div>
                        </div>

                        <!-- Program Studi & Fakultas -->
                        <div class="space-y-1">
                            <label for="prodi" class="font-semibold text-text">
                                Program Studi & Homebase Fakultas
                            </label>
                            <input 
                                type="text" 
                                id="prodi" 
                                name="prodi" 
                                value="{{ old('prodi', $lecturer->prodi ?? 'S1 Teknik Informatika - Fakultas Ilmu Komputer') }}" 
                                class="w-full px-3 py-2 bg-surface border border-line rounded-[6px] text-xs text-text focus:outline-hidden focus:border-[#2563EB]"
                            >
                        </div>

                    </div>

                </div>

                <!-- Footer Action Buttons -->
                <div class="mt-6 pt-4 border-t border-line flex items-center justify-end gap-3">
                    <button 
                        type="submit" 
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#1A2E5E] hover:bg-[#152449] text-white text-xs font-medium rounded-[6px] transition-colors shadow-xs cursor-pointer"
                    >
                        <x-icon name="save" :size="14" />
                        <span>Simpan Perubahan Profil</span>
                    </button>
                </div>
            </div>

        </form>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 2: PREFERENSI NOTIFIKASI                                   -->
    <!-- ============================================================== -->
    <div x-show="activeTab === 'notifikasi'" class="space-y-6">
        <form action="{{ route('dosen.pengaturan.notifications') }}" method="POST" class="space-y-6">
            @csrf

            <div class="bg-surface border border-line rounded-[8px] p-6 shadow-xs">
                <div class="pb-4 border-b border-line mb-6">
                    <h2 class="text-sm font-bold text-text tracking-tight">Preferensi Saluran Pemberitahuan</h2>
                    <p class="text-xs text-text-muted mt-0.5">
                        Tentukan jenis aktivitas perkuliahan yang ingin Anda terima notifikasinya. Opsi di bawah ini terhubung langsung ke mesin evaluasi notifikasi internal sistem (<code class="font-mono text-[11px] text-[#2563EB]">NotificationHelper::shouldNotify()</code>).
                    </p>
                </div>

                <div class="space-y-4">
                    
                    <!-- 1. Pengumpulan Tugas Mahasiswa -->
                    <div class="flex items-start justify-between gap-4 p-3.5 rounded-[6px] border border-line/80 hover:bg-slate-50/50 transition-colors">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-[6px] bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 border border-blue-100 mt-0.5">
                                <x-icon name="clipboard-check" :size="16" />
                            </div>
                            <div>
                                <label for="notif_tugas" class="font-semibold text-xs text-text block cursor-pointer">
                                    Pengumpulan Tugas Mahasiswa
                                </label>
                                <p class="text-[11px] text-text-muted mt-0.5 leading-relaxed">
                                    Kirim notifikasi setiap kali mahasiswa mengirimkan lembar jawaban tugas atau pembaharuan submission.
                                </p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                            <input type="checkbox" id="notif_tugas" name="tugas" value="1" {{ $notifikasi['tugas'] ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#2563EB]"></div>
                        </label>
                    </div>

                    <!-- 2. Ujian & Kuis -->
                    <div class="flex items-start justify-between gap-4 p-3.5 rounded-[6px] border border-line/80 hover:bg-slate-50/50 transition-colors">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-[6px] bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100 mt-0.5">
                                <x-icon name="file-text" :size="16" />
                            </div>
                            <div>
                                <label for="notif_ujian" class="font-semibold text-xs text-text block cursor-pointer">
                                    Aktivitas Ujian & Kuis
                                </label>
                                <p class="text-[11px] text-text-muted mt-0.5 leading-relaxed">
                                    Pemberitahuan pengerjaan kuis terjadwal, tenggat waktu ujian, dan rekapitulasi ujian tengah/akhir semester.
                                </p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                            <input type="checkbox" id="notif_ujian" name="ujian" value="1" {{ $notifikasi['ujian'] ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#2563EB]"></div>
                        </label>
                    </div>

                    <!-- 3. Presensi Perkuliahan -->
                    <div class="flex items-start justify-between gap-4 p-3.5 rounded-[6px] border border-line/80 hover:bg-slate-50/50 transition-colors">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-[6px] bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100 mt-0.5">
                                <x-icon name="calendar-check" :size="16" />
                            </div>
                            <div>
                                <label for="notif_presensi" class="font-semibold text-xs text-text block cursor-pointer">
                                    Presensi & Permohonan Izin Mahasiswa
                                </label>
                                <p class="text-[11px] text-text-muted mt-0.5 leading-relaxed">
                                    Pemberitahuan saat mahasiswa mengajukan permohonan izin/sakit disertai bukti surat keterangan dokter.
                                </p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                            <input type="checkbox" id="notif_presensi" name="presensi" value="1" {{ $notifikasi['presensi'] ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#2563EB]"></div>
                        </label>
                    </div>

                    <!-- 4. Forum Diskusi -->
                    <div class="flex items-start justify-between gap-4 p-3.5 rounded-[6px] border border-line/80 hover:bg-slate-50/50 transition-colors">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-[6px] bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 border border-purple-100 mt-0.5">
                                <x-icon name="message-circle" :size="16" />
                            </div>
                            <div>
                                <label for="notif_diskusi" class="font-semibold text-xs text-text block cursor-pointer">
                                    Forum Diskusi & Tanya Jawab
                                </label>
                                <p class="text-[11px] text-text-muted mt-0.5 leading-relaxed">
                                    Pemberitahuan pertanyaan baru di kelas pengampu dan respons balasan dari mahasiswa pada topik diskusi.
                                </p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                            <input type="checkbox" id="notif_diskusi" name="diskusi" value="1" {{ $notifikasi['diskusi'] ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#2563EB]"></div>
                        </label>
                    </div>

                    <!-- 5. Ringkasan Harian (Backlog Feature) -->
                    <div class="flex items-start justify-between gap-4 p-3.5 rounded-[6px] border border-line/80 bg-slate-50/40 opacity-75">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-[6px] bg-slate-100 text-slate-500 flex items-center justify-center shrink-0 border border-slate-200 mt-0.5">
                                <x-icon name="mail" :size="16" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-xs text-text">Ringkasan Harian Aktivitas (Daily Digest)</span>
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-semibold bg-slate-200 text-slate-700">Segera Hadir</span>
                                </div>
                                <p class="text-[11px] text-text-muted mt-0.5 leading-relaxed">
                                    Pengiriman ringkasan email harian yang mencakup jadwal mengajar esok hari dan daftar tugas yang perlu dinilai.
                                </p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-not-allowed shrink-0 mt-1">
                            <input type="checkbox" disabled class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-200 rounded-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4"></div>
                        </label>
                    </div>

                </div>

                <div class="mt-6 pt-4 border-t border-line flex items-center justify-end">
                    <button 
                        type="submit" 
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#1A2E5E] hover:bg-[#152449] text-white text-xs font-medium rounded-[6px] transition-colors shadow-xs cursor-pointer"
                    >
                        <x-icon name="save" :size="14" />
                        <span>Simpan Preferensi Notifikasi</span>
                    </button>
                </div>
            </div>

        </form>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 3: KEAMANAN & AKUN                                         -->
    <!-- ============================================================== -->
    <div x-show="activeTab === 'keamanan'" class="space-y-6">
        
        <!-- Form Ubah Password -->
        <div class="bg-surface border border-line rounded-[8px] p-6 shadow-xs">
            <div class="pb-4 border-b border-line mb-6">
                <h2 class="text-sm font-bold text-text tracking-tight">Perbarui Password Akun</h2>
                <p class="text-xs text-text-muted mt-0.5">
                    Gunakan kombinasi minimal 8 karakter dengan huruf kapital, angka, dan simbol untuk keamanan maksimal.
                </p>
            </div>

            <form action="{{ route('dosen.pengaturan.password') }}" method="POST" class="max-w-lg space-y-4 text-xs">
                @csrf

                <div class="space-y-1">
                    <label for="current_password" class="font-semibold text-text">
                        Password Saat Ini <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="current_password" 
                        name="current_password" 
                        required 
                        placeholder="Masukkan password saat ini" 
                        class="w-full px-3 py-2 bg-surface border border-line rounded-[6px] text-xs text-text focus:outline-hidden focus:border-[#2563EB]"
                    >
                </div>

                <div class="space-y-1">
                    <label for="password" class="font-semibold text-text">
                        Password Baru <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        placeholder="Minimal 8 karakter" 
                        class="w-full px-3 py-2 bg-surface border border-line rounded-[6px] text-xs text-text focus:outline-hidden focus:border-[#2563EB]"
                    >
                </div>

                <div class="space-y-1">
                    <label for="password_confirmation" class="font-semibold text-text">
                        Konfirmasi Password Baru <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        required 
                        placeholder="Ulangi password baru" 
                        class="w-full px-3 py-2 bg-surface border border-line rounded-[6px] text-xs text-text focus:outline-hidden focus:border-[#2563EB]"
                    >
                </div>

                <div class="pt-2">
                    <button 
                        type="submit" 
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#1A2E5E] hover:bg-[#152449] text-white text-xs font-medium rounded-[6px] transition-colors shadow-xs cursor-pointer"
                    >
                        <x-icon name="key" :size="14" />
                        <span>Perbarui Password</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Autentikasi Dua Faktor (2FA) & Sesi Aktif -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- 2FA Card -->
            <div class="bg-surface border border-line rounded-[8px] p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-8 h-8 rounded-[6px] bg-blue-50 text-[#2563EB] flex items-center justify-center border border-blue-100">
                            <x-icon name="shield-check" :size="16" />
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                            Segera Hadir
                        </span>
                    </div>
                    <h3 class="text-xs font-bold text-text">Autentikasi Dua Faktor (2FA)</h3>
                    <p class="text-[11px] text-text-muted mt-1 leading-relaxed">
                        Tambahkan lapisan keamanan ekstra dengan aplikasi authenticator (Google Authenticator / Microsoft Authenticator) saat login ke sistem.
                    </p>
                </div>
                <div class="pt-4 mt-4 border-t border-line">
                    <button type="button" disabled class="px-3 py-1.5 bg-slate-100 text-slate-400 text-xs font-medium rounded-[6px] cursor-not-allowed">
                        Konfigurasi 2FA (Nonaktif)
                    </button>
                </div>
            </div>

            <!-- Sesi Aktif -->
            <div class="bg-surface border border-line rounded-[8px] p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-8 h-8 rounded-[6px] bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                            <x-icon name="laptop" :size="16" />
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Aktif Sekarang
                        </span>
                    </div>
                    <h3 class="text-xs font-bold text-text">Sesi Perangkat Saat Ini</h3>
                    <p class="text-[11px] text-text-muted mt-1 leading-relaxed">
                        Anda sedang terhubung melalui web browser pada perangkat kerja institusi.
                    </p>
                    <div class="mt-3 p-2.5 rounded-[6px] bg-slate-50 border border-line/60 text-[11px] space-y-1">
                        <div class="flex items-center justify-between text-text-muted">
                            <span>Sistem Operasi:</span>
                            <span class="font-medium text-text">Windows 64-bit</span>
                        </div>
                        <div class="flex items-center justify-between text-text-muted">
                            <span>Alamat IP:</span>
                            <span class="font-medium text-text">127.0.0.1 (Lokal)</span>
                        </div>
                    </div>
                </div>
                <div class="pt-4 mt-4 border-t border-line">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs text-red-600 hover:text-red-700 font-medium hover:underline inline-flex items-center gap-1 cursor-pointer">
                            <x-icon name="log-out" :size="13" />
                            <span>Keluar dari Sesi Ini</span>
                        </button>
                    </form>
                </div>
            </div>

        </div>

    </div>

    <!-- ============================================================== -->
    <!-- TAB 4: STATUS SISTEM & BANTUAN                                 -->
    <!-- ============================================================== -->
    <div x-show="activeTab === 'sistem'" class="space-y-6">
        
        <!-- Status Sistem Jujur (Audit Batch 2) -->
        <div class="bg-surface border border-line rounded-[8px] p-6 shadow-xs">
            <div class="pb-4 border-b border-line mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-text tracking-tight">Kondisi & Status Operasional Sistem</h2>
                    <p class="text-xs text-text-muted mt-0.5">Informasi diagnostik server dan integritas koneksi backend LMS.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $systemStatus['db_connected'] ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                    <span class="w-2 h-2 rounded-full {{ $systemStatus['db_connected'] ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                    <span>{{ $systemStatus['db_connected'] ? 'Sistem Normal' : 'Koneksi Terganggu' }}</span>
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                
                <div class="p-3.5 rounded-[6px] bg-slate-50/70 border border-line/70">
                    <span class="text-[10px] text-text-muted block uppercase font-medium">Database Backend</span>
                    <p class="text-xs font-bold text-text mt-1 flex items-center gap-1.5">
                        <x-icon name="database" :size="14" class="text-[#2563EB]" />
                        <span>MySQL (arunika)</span>
                    </p>
                    <span class="text-[10px] text-emerald-600 font-medium mt-1 block">✓ Pool Terhubung</span>
                </div>

                <div class="p-3.5 rounded-[6px] bg-slate-50/70 border border-line/70">
                    <span class="text-[10px] text-text-muted block uppercase font-medium">Versi Aplikasi</span>
                    <p class="text-xs font-bold text-text mt-1 flex items-center gap-1.5">
                        <x-icon name="box" :size="14" class="text-[#2563EB]" />
                        <span>{{ $systemStatus['app_version'] }}</span>
                    </p>
                    <span class="text-[10px] text-text-muted mt-1 block">Arunika LMS Engine</span>
                </div>

                <div class="p-3.5 rounded-[6px] bg-slate-50/70 border border-line/70">
                    <span class="text-[10px] text-text-muted block uppercase font-medium">Waktu Server (WIB)</span>
                    <p class="text-xs font-bold text-text mt-1 flex items-center gap-1.5">
                        <x-icon name="clock" :size="14" class="text-[#2563EB]" />
                        <span>{{ $systemStatus['server_time'] }}</span>
                    </p>
                    <span class="text-[10px] text-text-muted mt-1 block">Zona Asia/Jakarta</span>
                </div>

            </div>
        </div>

        <!-- Bantuan & Dukungan Teknis -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Card Panduan Cepat Dosen -->
            <div class="bg-surface border border-line rounded-[8px] p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="w-8 h-8 rounded-[6px] bg-blue-50 text-[#2563EB] flex items-center justify-center border border-blue-100 mb-3">
                        <x-icon name="book-open" :size="16" />
                    </div>
                    <h3 class="text-xs font-bold text-text">Panduan Cepat Pengajar (Dosen)</h3>
                    <p class="text-[11px] text-text-muted mt-1 leading-relaxed">
                        Petunjuk operasional lengkap untuk mengelola silabus kelas, komponen penilaian OBE, presensi barcode/manual, serta forum diskusi.
                    </p>
                </div>
                <div class="pt-4 mt-4 border-t border-line">
                    <button 
                        type="button" 
                        @click="panduanModal = true"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-surface hover:bg-slate-100 text-text text-xs font-medium rounded-[6px] border border-line transition-colors shadow-2xs cursor-pointer"
                    >
                        <x-icon name="file-text" :size="13" />
                        <span>Buka Panduan Cepat</span>
                    </button>
                </div>
            </div>

            <!-- Card Form Bantuan & Tiket BAAK/IT -->
            <div class="bg-surface border border-line rounded-[8px] p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="w-8 h-8 rounded-[6px] bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100 mb-3">
                        <x-icon name="headphones" :size="16" />
                    </div>
                    <h3 class="text-xs font-bold text-text">Bantuan Teknis & Tiket Dukungan</h3>
                    <p class="text-[11px] text-text-muted mt-1 leading-relaxed">
                        Mengalami kendala sinkronisasi nilai SIAKAD atau masalah teknis portal? Kirimkan permohonan bantuan ke tim administrator kampus.
                    </p>
                </div>
                <div class="pt-4 mt-4 border-t border-line">
                    <button 
                        type="button" 
                        @click="tiketModal = true"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#1A2E5E] hover:bg-[#152449] text-white text-xs font-medium rounded-[6px] transition-colors shadow-xs cursor-pointer"
                    >
                        <x-icon name="send" :size="13" />
                        <span>Ajukan Tiket Bantuan</span>
                    </button>
                </div>
            </div>

        </div>

    </div>

    <!-- ============================================================== -->
    <!-- MODAL 1: PANDUAN CEPAT DOSEN                                   -->
    <!-- ============================================================== -->
    <div 
        x-show="panduanModal" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4"
    >
        <div 
            @click.away="panduanModal = false"
            class="bg-surface border border-line rounded-[8px] max-w-2xl w-full shadow-lg overflow-hidden animate-in fade-in zoom-in-95 duration-150"
        >
            <div class="px-5 py-4 border-b border-line flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-[6px] bg-blue-50 text-[#2563EB] flex items-center justify-center border border-blue-100">
                        <x-icon name="book-open" :size="16" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-text tracking-tight">Panduan Cepat Operasional Dosen</h3>
                        <p class="text-[11px] text-text-muted">Tata kelola perkuliahan berbasis Outcome-Based Education (OBE)</p>
                    </div>
                </div>
                <button type="button" @click="panduanModal = false" class="text-text-muted hover:text-text cursor-pointer">
                    <x-icon name="x" :size="18" />
                </button>
            </div>

            <div class="p-5 space-y-4 text-xs max-h-[70vh] overflow-y-auto">
                <div class="p-3 bg-blue-50/50 border border-blue-200/60 rounded-[6px]">
                    <h4 class="font-bold text-text flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-[#2563EB] text-white flex items-center justify-center text-[10px]">1</span>
                        <span>Silabus & Bahan Ajar</span>
                    </h4>
                    <p class="text-[11px] text-text-muted mt-1 leading-relaxed pl-7">
                        Unggah silabus dan materi perkuliahan pada menu <strong>Materi Pembelajaran</strong>. Anda dapat mengunci materi sebagai Draft atau langsung mempublikasikannya untuk 16 pertemuan.
                    </p>
                </div>

                <div class="p-3 bg-slate-50 border border-line rounded-[6px]">
                    <h4 class="font-bold text-text flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-[#1A2E5E] text-white flex items-center justify-center text-[10px]">2</span>
                        <span>Tugas & Penilaian Rubrik</span>
                    </h4>
                    <p class="text-[11px] text-text-muted mt-1 leading-relaxed pl-7">
                        Gunakan menu <strong>Tugas & Ujian</strong> untuk membuat assessment baru. Saat mahasiswa mengumpulkan, Anda dapat langsung menilai di <strong>Workspace Penilaian</strong> dengan feedback detail.
                    </p>
                </div>

                <div class="p-3 bg-slate-50 border border-line rounded-[6px]">
                    <h4 class="font-bold text-text flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-[#1A2E5E] text-white flex items-center justify-center text-[10px]">3</span>
                        <span>Presensi Perkuliahan Cepat</span>
                    </h4>
                    <p class="text-[11px] text-text-muted mt-1 leading-relaxed pl-7">
                        Gunakan fitur <strong>Hadir Semua</strong> pada formulir input presensi untuk pencatatan instan, kemudian ubah status mahasiswa yang berhalangan atau izin.
                    </p>
                </div>

                <div class="p-3 bg-slate-50 border border-line rounded-[6px]">
                    <h4 class="font-bold text-text flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-[#1A2E5E] text-white flex items-center justify-center text-[10px]">4</span>
                        <span>Buku Nilai & Standarisasi Bobot 100%</span>
                    </h4>
                    <p class="text-[11px] text-text-muted mt-1 leading-relaxed pl-7">
                        Buku Nilai secara otomatis menghitung nilai akhir berdasarkan bobot komponen OBE. Pastikan total bobot bernilai tepat 100% saat mengubah pengaturan komponen.
                    </p>
                </div>

                <div class="p-3 bg-slate-50 border border-line rounded-[6px]">
                    <h4 class="font-bold text-text flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-[#1A2E5E] text-white flex items-center justify-center text-[10px]">5</span>
                        <span>Forum Diskusi Interaktif</span>
                    </h4>
                    <p class="text-[11px] text-text-muted mt-1 leading-relaxed pl-7">
                        Jawab pertanyaan mahasiswa di forum kelas split-pane. Anda dapat menyematkan (pin) pengumuman penting atau mengunci (lock) topik diskusi yang telah selesai.
                    </p>
                </div>
            </div>

            <div class="px-5 py-3 border-t border-line bg-slate-50/50 flex justify-end">
                <button type="button" @click="panduanModal = false" class="px-3.5 py-1.5 bg-[#1A2E5E] text-white text-xs font-medium rounded-[6px] hover:bg-[#152449]">
                    Mengerti & Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 2: AJUKAN TIKET BANTUAN                                  -->
    <!-- ============================================================== -->
    <div 
        x-show="tiketModal" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4"
    >
        <div 
            @click.away="tiketModal = false"
            class="bg-surface border border-line rounded-[8px] max-w-lg w-full shadow-lg overflow-hidden animate-in fade-in zoom-in-95 duration-150"
        >
            <div class="px-5 py-4 border-b border-line flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-[6px] bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100">
                        <x-icon name="headphones" :size="16" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-text tracking-tight">Ajukan Tiket Dukungan Teknis</h3>
                        <p class="text-[11px] text-text-muted">Bantuan layanan dari tim BAAK dan IT Kampus</p>
                    </div>
                </div>
                <button type="button" @click="tiketModal = false" class="text-text-muted hover:text-text cursor-pointer">
                    <x-icon name="x" :size="18" />
                </button>
            </div>

            <form action="{{ route('dosen.pengaturan.support_ticket') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="p-5 space-y-4 text-xs">
                    
                    <div class="space-y-1">
                        <label for="ticket_subject" class="font-medium text-text">
                            Subjek / Pokok Masalah <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="ticket_subject" 
                            name="subject" 
                            required 
                            placeholder="Contoh: Kendala sinkronisasi komponen penilaian ke SIAKAD" 
                            class="w-full px-3 py-2 bg-surface border border-line rounded-[6px] text-xs text-text focus:outline-hidden focus:border-[#2563EB]"
                        >
                    </div>

                    <div class="space-y-1">
                        <label for="ticket_category" class="font-medium text-text">
                            Kategori Permasalahan <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="ticket_category" 
                            name="category" 
                            required 
                            class="w-full px-3 py-2 bg-surface border border-line rounded-[6px] text-xs text-text focus:outline-hidden focus:border-[#2563EB]"
                        >
                            <option value="teknis">Kendala Teknis LMS / Bug Sistem</option>
                            <option value="akademik">Administrasi Akademik / Data Kelas SIAKAD</option>
                            <option value="fasilitas">Fasilitas Laboratorium / Ruang Kuliah</option>
                            <option value="lainnya">Pertanyaan Umum Lainnya</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label for="ticket_message" class="font-medium text-text">
                            Deskripsi Rinci Kendala <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            id="ticket_message" 
                            name="message" 
                            rows="4" 
                            required 
                            placeholder="Jelaskan detail kendala yang dialami, mata kuliah terkait, dan pesan error jika ada..." 
                            class="w-full px-3 py-2 bg-surface border border-line rounded-[6px] text-xs text-text focus:outline-hidden focus:border-[#2563EB]"
                        ></textarea>
                    </div>

                    <div class="space-y-1">
                        <label for="ticket_attachment" class="font-medium text-text">
                            Lampiran Tangkapan Layar (Opsional)
                        </label>
                        <input 
                            type="file" 
                            id="ticket_attachment" 
                            name="attachment" 
                            class="w-full text-xs text-text-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-[4px] file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-[#2563EB] hover:file:bg-blue-100"
                        >
                    </div>

                </div>

                <div class="px-5 py-3 border-t border-line bg-slate-50/50 flex items-center justify-end gap-2">
                    <button type="button" @click="tiketModal = false" class="px-3 py-1.5 text-xs text-text-muted hover:text-text cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-1.5 bg-[#1A2E5E] text-white text-xs font-medium rounded-[6px] hover:bg-[#152449] cursor-pointer">
                        Kirim Tiket Bantuan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
