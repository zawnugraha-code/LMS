@extends('layouts.app')

@section('title', 'Dashboard — ' . Auth::user()?->role?->label())
@section('page_title', 'Dashboard Pembelajaran ' . Auth::user()?->campus?->name)

@section('content')

    <!-- ==========================================
         VIEW UNTUK MAHASISWA
         ========================================== -->
    @if(Auth::user()->isStudent())

        <!-- Student Banner -->
        <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-indigo-900/60 via-slate-900 to-amber-950/40 border border-indigo-500/30 shadow-2xl mb-8 relative overflow-hidden">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 font-mono text-xs font-bold border border-emerald-500/30">Mahasiswa Aktif</span>
                        <span class="text-xs text-slate-400 font-mono">NIM: {{ Auth::user()->identifier ?? '2201010045' }}</span>
                    </div>
                    <h1 class="font-display font-bold text-2xl sm:text-3xl text-white">
                        Halo, {{ Auth::user()->name }}! 👋
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-300">
                        S1 Teknik Informatika · Semester 3 (Ganjil 2026/2027) · Dosen PA: Dra. Endang Lestari, M.Si.
                    </p>
                </div>

                <!-- Quick Metrics Grid -->
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800">
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider block font-bold">IPK Sementara</span>
                        <span class="text-lg font-bold font-mono text-emerald-400">3.88</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800">
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider block font-bold">Total SKS</span>
                        <span class="text-lg font-bold font-mono text-indigo-300">21 SKS</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800">
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider block font-bold">Presensi</span>
                        <span class="text-lg font-bold font-mono text-amber-400">100%</span>
                    </div>
                </div>
            </div>

            <!-- Gamification XP Streak Bar -->
            <div class="mt-6 pt-6 border-t border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex-1">
                    <div class="flex justify-between text-xs mb-1.5 font-bold">
                        <span class="text-amber-300 flex items-center gap-1.5">
                            <span>⭐ Level {{ Auth::user()->currentLevel() }}: Code Artisan</span>
                        </span>
                        <span class="text-slate-400 font-mono">{{ Auth::user()->totalXp() }} XP / {{ Auth::user()->currentLevel() * 200 }} XP</span>
                    </div>
                    <div class="w-full bg-slate-800 h-2.5 rounded-full overflow-hidden">
                        <div class="bg-gradient-to-r from-amber-400 to-indigo-500 h-full rounded-full transition-all duration-500" style="width: 75%;"></div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-bold flex items-center gap-1.5">
                        🔥 Streak 7 Hari
                    </span>
                    <span class="px-3 py-1 rounded-xl bg-indigo-500/10 border border-indigo-500/30 text-indigo-300 text-xs font-bold">
                        🎖️ {{ Auth::user()->badges()->count() }} Lencana
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left 2 Cols: Enrolled Classes & Upcoming Tasks -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Enrolled Course Section -->
                <section id="mata-kuliah">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-display font-bold text-lg text-white flex items-center gap-2">
                            <span>📚</span>
                            <span>Mata Kuliah Aktif Semester Ini</span>
                        </h3>
                        <span class="text-xs text-slate-400">Kurikulum OBE 2024</span>
                    </div>

                    @foreach($enrolledClasses as $enrolledClass)
                        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition space-y-4 mb-4">
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 rounded bg-indigo-600/20 text-indigo-300 font-mono text-xs font-bold">
                                            {{ $enrolledClass->course->code }} · {{ $enrolledClass->course->credits }} SKS
                                        </span>
                                        <span class="text-xs text-slate-400">{{ $enrolledClass->section }} · {{ $enrolledClass->room }}</span>
                                    </div>
                                    <h4 class="font-display font-bold text-lg text-white">{{ $enrolledClass->course->name }}</h4>
                                    <p class="text-xs text-slate-400">Dosen Pengampu: {{ $enrolledClass->lecturer->name }} · Jadwal: {{ $enrolledClass->schedule_day }}, {{ $enrolledClass->start_time }} - {{ $enrolledClass->end_time }}</p>
                                </div>
                                <span class="px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold font-mono">
                                    Nilai: A (91.50)
                                </span>
                            </div>

                            <!-- Weekly Meetings Accordion Preview -->
                            <div class="pt-4 border-t border-slate-800">
                                <h5 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Silabus & Materi Pertemuan</h5>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($enrolledClass->meetings->take(4) as $meeting)
                                        <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800/80 flex flex-col justify-between">
                                            <div class="mb-2">
                                                <div class="flex items-center justify-between text-[11px] mb-1">
                                                    <span class="font-bold text-amber-400 font-mono">Minggu {{ $meeting->week }}</span>
                                                    <span class="text-slate-400">{{ $meeting->mode }}</span>
                                                </div>
                                                <p class="text-xs font-bold text-white leading-snug">{{ $meeting->title }}</p>
                                                <p class="text-[11px] text-slate-400 mt-1 line-clamp-1">{{ $meeting->description }}</p>
                                            </div>
                                            <div class="flex items-center justify-between pt-2 border-t border-slate-800/60 text-[11px]">
                                                <span class="text-indigo-400 flex items-center gap-1">
                                                    📄 {{ $meeting->materials->count() }} Modul
                                                </span>
                                                <!-- Action: Check in attendance -->
                                                <form action="{{ route('meetings.attendance', $meeting->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="px-2 py-1 rounded bg-indigo-600/30 text-indigo-300 font-bold hover:bg-indigo-600 hover:text-white transition">
                                                        Presensi &rarr;
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </section>

                <!-- Assignments & Submissions Section -->
                <section id="tugas-asesmen" x-data="{ submitModalOpen: false, selectedAssignment: null }">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-display font-bold text-lg text-white flex items-center gap-2">
                            <span>📝</span>
                            <span>Tugas & Asesmen Terjadwal</span>
                        </h3>
                        <span class="text-xs text-slate-400">Penilaian Berbasis Rubrik</span>
                    </div>

                    <div class="space-y-4">
                        @foreach($assignments as $assignment)
                            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded bg-amber-500/20 text-amber-300 font-mono text-[11px] font-bold">
                                            Max {{ $assignment->max_score }} Poin
                                        </span>
                                        <span class="text-xs text-slate-400">{{ $assignment->academicClass->course->code }}</span>
                                    </div>
                                    <h4 class="font-bold text-white text-sm">{{ $assignment->title }}</h4>
                                    <p class="text-xs text-slate-400 line-clamp-1">{{ $assignment->description }}</p>
                                    <p class="text-[11px] text-slate-400 font-mono">Tenggat Waktu: {{ $assignment->due_at->format('d M Y, H:i') }} WIB</p>
                                </div>

                                <div class="flex items-center gap-3">
                                    <button @click="selectedAssignment = {{ json_encode($assignment) }}; submitModalOpen = true" class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 text-white font-bold text-xs shadow transition whitespace-nowrap">
                                        Kumpulkan Tugas &rarr;
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Submission Modal -->
                    <div x-show="submitModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
                        <div class="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4" @click.away="submitModalOpen = false">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                                <div>
                                    <h4 class="font-bold text-white text-base">Pengumpulan Tugas</h4>
                                    <p class="text-xs text-indigo-400" x-text="selectedAssignment ? selectedAssignment.title : ''"></p>
                                </div>
                                <button @click="submitModalOpen = false" class="text-slate-400 hover:text-white text-lg font-bold">&times;</button>
                            </div>

                            <form :action="'/app/assignments/' + (selectedAssignment ? selectedAssignment.id : 1) + '/submit'" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1">Catatan Pengumpulan / Tautan Repository *</label>
                                    <textarea name="content" rows="3" required placeholder="Jelaskan ringkasan solusi kode, tautan GitHub/GitLab, atau jawaban Anda..." class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white focus:outline-none focus:border-indigo-500 transition"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1">URL Berkas / Lampiran ZIP (Opsional)</label>
                                    <input type="text" name="file_url" placeholder="https://drive.google.com/... atau path file" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white focus:outline-none focus:border-indigo-500 transition">
                                </div>
                                <div class="p-3 rounded-xl bg-indigo-950/30 border border-indigo-500/20 text-[11px] text-slate-300 flex items-center gap-2">
                                    <span>💡</span>
                                    <span>Pengumpulan tepat waktu akan memberikan <strong>+10 XP</strong> ke profil gamifikasi Anda.</span>
                                </div>
                                <div class="flex justify-end gap-3 pt-2">
                                    <button type="button" @click="submitModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold hover:bg-slate-700 transition">
                                        Batal
                                    </button>
                                    <button type="submit" class="px-5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-amber-500 text-white text-xs font-bold shadow transition">
                                        Kirimkan Sekarang &rarr;
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Right Column: OBE Radar, Gamification, Badges -->
            <div class="space-y-8">
                <!-- OBE Radar Card -->
                <div id="obe-capaian" class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="font-display font-bold text-sm text-white flex items-center gap-2">
                            <span>🎯</span>
                            <span>Radar Capaian CPL (OBE)</span>
                        </h4>
                        <span class="text-[11px] text-emerald-400 font-mono font-bold">Lulus Unggul</span>
                    </div>
                    <p class="text-xs text-slate-400">Ketercapaian kompetensi lulusan berdasarkan agregat seluruh penilaian semester.</p>

                    <div class="space-y-3 pt-2">
                        @foreach($cplScores as $score)
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="font-bold text-slate-200">{{ $score->cpl->code }}</span>
                                    <span class="font-mono text-emerald-400 font-bold">{{ $score->score }}%</span>
                                </div>
                                <p class="text-[11px] text-slate-400 mb-1.5 line-clamp-1">{{ $score->cpl->description }}</p>
                                <div class="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                                    <div class="bg-gradient-to-r from-indigo-500 to-emerald-400 h-full rounded-full" style="width: {{ $score->score }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Badges Showcase Card -->
                <div id="gamifikasi" class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="font-display font-bold text-sm text-white flex items-center gap-2">
                            <span>🎖️</span>
                            <span>Koleksi Lencana Kompetensi</span>
                        </h4>
                        <span class="text-xs text-amber-400 font-mono font-bold">{{ $badges->count() }} Diraih</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        @foreach($allBadges as $badge)
                            @php
                                $isEarned = $badges->contains('id', $badge->id);
                            @endphp
                            <div class="p-3 rounded-2xl border text-center transition {{ $isEarned ? 'bg-amber-950/20 border-amber-500/40' : 'bg-slate-950/60 border-slate-800/80 opacity-50' }}">
                                <div class="w-10 h-10 mx-auto rounded-full flex items-center justify-center text-lg mb-2 {{ $isEarned ? 'bg-amber-500/20 text-amber-400' : 'bg-slate-800 text-slate-500' }}">
                                    {{ $isEarned ? '🏆' : '🔒' }}
                                </div>
                                <p class="font-bold text-xs text-white leading-tight mb-1">{{ $badge->name }}</p>
                                <p class="text-[10px] text-slate-400 line-clamp-1">{{ $badge->description }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Recent XP Activity -->
                <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-3">
                    <h4 class="font-display font-bold text-sm text-white flex items-center gap-2">
                        <span>⚡</span>
                        <span>Riwayat XP Ledger</span>
                    </h4>
                    <div class="space-y-2">
                        @foreach($recentXp as $xp)
                            <div class="p-2.5 rounded-xl bg-slate-950 border border-slate-800/60 flex items-center justify-between text-xs">
                                <div>
                                    <p class="text-white font-medium text-[11px] line-clamp-1">{{ $xp->description }}</p>
                                    <span class="text-[10px] text-slate-500 font-mono">{{ $xp->created_at->diffForHumans() }}</span>
                                </div>
                                <span class="font-mono font-bold text-amber-400 text-xs shrink-0">+{{ $xp->amount }} XP</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    <!-- ==========================================
         VIEW UNTUK DOSEN PENGAMPU
         ========================================== -->
    @elseif(Auth::user()->isLecturer())

        <!-- Lecturer Header -->
        <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-indigo-950/80 via-slate-900 to-indigo-900/40 border border-indigo-500/30 mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="px-2.5 py-0.5 rounded-full bg-blue-500/20 text-blue-300 font-mono text-xs font-bold border border-blue-500/30">Dosen Pengampu</span>
                    <h1 class="font-display font-bold text-2xl sm:text-3xl text-white mt-1">
                        Selamat Datang, {{ Auth::user()->name }}
                    </h1>
                    <p class="text-xs text-slate-300 mt-1">NIDN: {{ Auth::user()->identifier }} · Program Studi S1 Teknik Informatika · Periode Ganjil 2026/2027</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="aiDrawerOpen = true; aiPrompt = 'Tolong buatkan draf 3 soal kuis pilihan ganda berbasis taksonomi Bloom C4 untuk materi SOLID Principles.'; askAi()" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-amber-500 text-white font-bold text-xs shadow-lg transition">
                        ✨ AI Draf Soal Bloom &rarr;
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left: Classes Taught & Submissions to Grade -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Submissions to Grade -->
                <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800" x-data="{ gradeModalOpen: false, activeSubmission: null }">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-display font-bold text-base text-white flex items-center gap-2">
                            <span>📥</span>
                            <span>Pengumpulan Tugas yang Perlu Dinilai</span>
                        </h3>
                        <span class="text-xs font-mono text-amber-400 font-bold">{{ $pendingSubmissions->count() }} Menunggu</span>
                    </div>

                    <div class="space-y-3">
                        @forelse($pendingSubmissions as $sub)
                            <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-bold text-white text-xs">{{ $sub->user->name }}</span>
                                        <span class="text-[11px] font-mono text-slate-400">({{ $sub->user->identifier }})</span>
                                    </div>
                                    <p class="text-xs text-indigo-300 font-semibold">{{ $sub->assignment->title }}</p>
                                    <p class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">{{ $sub->content }}</p>
                                </div>
                                <button @click="activeSubmission = {{ json_encode($sub) }}; gradeModalOpen = true" class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs whitespace-nowrap transition">
                                    Beri Nilai & Feedback &rarr;
                                </button>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500 italic p-4 text-center">Semua pengumpulan tugas telah selesai dinilai.</p>
                        @endforelse
                    </div>

                    <!-- Grading Modal -->
                    <div x-show="gradeModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
                        <div class="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4" @click.away="gradeModalOpen = false">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                                <div>
                                    <h4 class="font-bold text-white text-base">Penilaian Rubrik Tugas</h4>
                                    <p class="text-xs text-slate-400" x-text="activeSubmission ? activeSubmission.user.name : ''"></p>
                                </div>
                                <button @click="gradeModalOpen = false" class="text-slate-400 hover:text-white text-lg font-bold">&times;</button>
                            </div>

                            <form :action="'/app/submissions/' + (activeSubmission ? activeSubmission.id : 1) + '/grade'" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nilai Akhir (Skala 0 - 100) *</label>
                                    <input type="number" step="0.1" name="score" required min="0" max="100" placeholder="Contoh: 92.5" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1">Umpan Balik Dosen (Pedagogical Feedback) *</label>
                                    <textarea name="feedback" rows="3" required placeholder="Tuliskan catatan evaluasi terhadap kepatuhan prinsip arsitektur, clean code, dan saran perbaikan..." class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white focus:outline-none focus:border-indigo-500 transition"></textarea>
                                </div>
                                <div class="flex justify-end gap-3 pt-2">
                                    <button type="button" @click="gradeModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold hover:bg-slate-700 transition">
                                        Batal
                                    </button>
                                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition">
                                        Simpan Nilai & Konversi CPL &rarr;
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Classes Taught -->
                <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
                    <h3 class="font-display font-bold text-base text-white flex items-center gap-2">
                        <span>👥</span>
                        <span>Kelas Paralel yang Diampu</span>
                    </h3>
                    <div class="space-y-3">
                        @foreach($classesTaught as $class)
                            <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                                <div>
                                    <span class="text-xs font-mono px-2 py-0.5 rounded bg-indigo-900/50 text-indigo-300 font-bold">{{ $class->course->code }} · {{ $class->section }}</span>
                                    <h4 class="font-bold text-white text-sm mt-1">{{ $class->course->name }}</h4>
                                    <p class="text-xs text-slate-400">{{ $class->enrollments->count() }} Mahasiswa Terdaftar · {{ $class->schedule_day }}, {{ $class->start_time }}</p>
                                </div>
                                <span class="px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 font-mono text-xs font-bold">
                                    CPL Avg: 91.8%
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right Column: Quick Sesi Presensi QR Generator -->
            <div class="space-y-6">
                <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
                    <h4 class="font-display font-bold text-sm text-white flex items-center gap-2">
                        <span>📱</span>
                        <span>Sesi Presensi QR Dinamis</span>
                    </h4>
                    <p class="text-xs text-slate-400">Kode QR otomatis diperbarui setiap 15 detik pada proyektor kelas dengan batasan radius 100m.</p>
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 text-center space-y-3">
                        <div class="w-32 h-32 mx-auto bg-white p-2 rounded-xl shadow flex items-center justify-center">
                            <!-- Simulated QR Matrix -->
                            <div class="w-full h-full border-4 border-slate-950 p-2 flex flex-col justify-between font-mono text-[8px] text-slate-950 font-bold">
                                <div class="flex justify-between"><span>■■■</span><span>■■■</span></div>
                                <span class="text-center">ARUNIKA-QR</span>
                                <div class="flex justify-between"><span>■■■</span><span>■■■</span></div>
                            </div>
                        </div>
                        <span class="text-[11px] text-amber-400 font-mono block">QR Berputar: Aktif (12 detik tersisa)</span>
                    </div>
                </div>
            </div>
        </div>

    <!-- ==========================================
         VIEW UNTUK ADMIN KAMPUS & SUPER ADMIN
         ========================================== -->
    @elseif(Auth::user()->isCampusAdmin() || Auth::user()->isSuperAdmin())

        <div class="p-6 sm:p-8 rounded-3xl bg-slate-900 border border-slate-800 mb-8">
            <span class="px-2.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 font-mono text-xs font-bold border border-indigo-500/30">Pengelola Institusi / BAAK</span>
            <h1 class="font-display font-bold text-2xl sm:text-3xl text-white mt-1">
                Dashboard Administrasi Akademik & Mutu
            </h1>
            <p class="text-xs text-slate-400 mt-1">Pantau seluruh fakultas, kurikulum OBE, ketercapaian akreditasi LAM-PT, dan sinkronisasi SIAKAD.</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-center">
                <span class="text-xs text-slate-400 uppercase tracking-wider block">Fakultas</span>
                <span class="text-2xl font-bold font-mono text-white">{{ $stats['faculties'] }}</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-center">
                <span class="text-xs text-slate-400 uppercase tracking-wider block">Prodi</span>
                <span class="text-2xl font-bold font-mono text-white">{{ $stats['programs'] }}</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-center">
                <span class="text-xs text-slate-400 uppercase tracking-wider block">Mata Kuliah</span>
                <span class="text-2xl font-bold font-mono text-white">{{ $stats['courses'] }}</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-center">
                <span class="text-xs text-slate-400 uppercase tracking-wider block">Kelas Aktif</span>
                <span class="text-2xl font-bold font-mono text-white">{{ $stats['classes'] }}</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-center">
                <span class="text-xs text-slate-400 uppercase tracking-wider block">Dosen</span>
                <span class="text-2xl font-bold font-mono text-indigo-400">{{ $stats['lecturers'] }}</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-center">
                <span class="text-xs text-slate-400 uppercase tracking-wider block">Mahasiswa</span>
                <span class="text-2xl font-bold font-mono text-emerald-400">{{ $stats['students'] }}</span>
            </div>
        </div>

        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800">
            <h3 class="font-display font-bold text-base text-white mb-4">Kesiapan Akreditasi Program Studi (OBE Compliance)</h3>
            <div class="space-y-4">
                @foreach($programs as $prog)
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <span class="text-xs font-mono px-2 py-0.5 rounded bg-emerald-900 text-emerald-300 font-bold">{{ $prog->accreditation }}</span>
                            <h4 class="font-bold text-white text-sm mt-1">{{ $prog->name }}</h4>
                            <p class="text-xs text-slate-400">{{ $prog->cpls->count() }} Capaian Pembelajaran Lulusan (CPL) Terdefinisi</p>
                        </div>
                        <span class="px-3 py-1 rounded-xl bg-emerald-500/10 text-emerald-400 text-xs font-mono font-bold">
                            Kesiapan Dokumen: 100%
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

    <!-- ==========================================
         VIEW UNTUK AUDITOR / REVIEWER AKREDITASI
         ========================================== -->
    @elseif(Auth::user()->isAuditor())

        <div class="p-6 sm:p-8 rounded-3xl bg-slate-900 border border-slate-800 mb-8">
            <span class="px-2.5 py-0.5 rounded-full bg-rose-500/20 text-rose-300 font-mono text-xs font-bold border border-rose-500/30">Auditor / Reviewer Akreditasi</span>
            <h1 class="font-display font-bold text-2xl sm:text-3xl text-white mt-1">
                Portal Evaluasi & Audit Ketercapaian CPL Lulusan
            </h1>
            <p class="text-xs text-slate-400 mt-1">Audit kesesuaian instrumen 9 kriteria LAM-INFOKOM, bukti rubrik asesmen, dan siklus Continuous Quality Improvement (CQI).</p>
        </div>

        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
            <h3 class="font-display font-bold text-base text-white">Matriks Capaian Pembelajaran Lulusan (CPL)</h3>
            <div class="space-y-3">
                @foreach($cpls as $cpl)
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-mono font-bold text-indigo-400">{{ $cpl->code }} · Target {{ $cpl->target_score }}%</span>
                            <p class="text-xs text-white mt-1">{{ $cpl->description }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 font-bold font-mono text-xs shrink-0">
                            Capaian Aktual: 92.5%
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

    <!-- ==========================================
         VIEW UNTUK MENTOR INDUSTRI (MBKM)
         ========================================== -->
    @elseif(Auth::user()->isIndustryMentor())

        <div class="p-6 sm:p-8 rounded-3xl bg-slate-900 border border-slate-800 mb-8">
            <span class="px-2.5 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 font-mono text-xs font-bold border border-cyan-500/30">Mentor Industri / Mitra MBKM</span>
            <h1 class="font-display font-bold text-2xl sm:text-3xl text-white mt-1">
                Portal Verifikasi Magang & Proyek MBKM
            </h1>
            <p class="text-xs text-slate-400 mt-1">Validasi aktivitas logbook mingguan mahasiswa magang bersertifikat dan konversi SKS mata kuliah.</p>
        </div>

        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
            <h3 class="font-display font-bold text-base text-white">Mahasiswa Bimbingan Magang Industri</h3>
            <div class="space-y-3">
                @foreach($internStudents as $student)
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-white text-xs">{{ $student->name }} ({{ $student->identifier }})</h4>
                            <p class="text-xs text-slate-400">Posisi: Software Engineering Intern @ Gojek · Minggu ke-8</p>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-cyan-500/20 text-cyan-300 font-mono text-xs font-bold">
                            Logbook Terverifikasi
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

    @endif

@endsection
