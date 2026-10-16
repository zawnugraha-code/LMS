@extends('layouts.app')

@section('title', 'Ujian Online — ' . $quiz->title . ' — Arunika')
@section('page_breadcrumb', 'Tugas & Ujian / ' . $quiz->title)

@section('content')
<div 
    x-data="{
        answers: {},
        currentQuestion: 1,
        totalQuestions: {{ $quiz->questions->count() }},
        secondsLeft: {{ ($quiz->duration_minutes ?: 120) * 60 }},
        timerInterval: null,
        formattedTime() {
            const m = Math.floor(this.secondsLeft / 60);
            const s = this.secondsLeft % 60;
            return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        },
        init() {
            this.timerInterval = setInterval(() => {
                if (this.secondsLeft > 0) {
                    this.secondsLeft--;
                } else {
                    clearInterval(this.timerInterval);
                    alert('Waktu ujian telah berakhir! Jawaban Anda akan dikirimkan otomatis.');
                    document.getElementById('examForm').submit();
                }
            }, 1000);
        },
        get answeredCount() {
            return Object.keys(this.answers).length;
        }
    }"
    class="space-y-6"
>

    <!-- Top Sticky Bar: Ujian Info & Countdown Timer & Proctoring Status -->
    <div class="bg-surface border border-line rounded-[10px] p-4 px-6 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-purple-100 text-purple-700">
                    CBT ONLINE
                </span>
                <span class="text-xs text-text-muted">
                    {{ $quiz->academicClass?->course?->code ?? 'IF2103' }} • {{ $quiz->academicClass?->course?->name ?? 'Mata Kuliah' }}
                </span>
            </div>
            <h1 class="text-xl font-bold text-text mt-1">
                {{ $quiz->title }}
            </h1>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <!-- AI Proctoring Badge -->
            <div class="px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>AI Proctoring Aktif</span>
            </div>

            <!-- Countdown Timer -->
            <div class="px-4 py-2 rounded-[8px] bg-red-50 border border-red-200 text-danger text-sm font-bold flex items-center gap-2">
                <x-icon name="clock" :size="16" />
                <span x-text="formattedTime()">--:--</span>
            </div>
        </div>
    </div>

    <!-- Main Exam Layout (70% Questions, 30% Navigator) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column: Lembar Soal -->
        <div class="lg:col-span-8 space-y-5">
            <form 
                id="examForm" 
                action="{{ route('quizzes.submit_attempt', $quiz) }}" 
                method="POST" 
                class="space-y-5"
            >
                @csrf

                @forelse($quiz->questions as $index => $q)
                    <x-ui.card id="soal-{{ $index + 1 }}" class="p-6 transition-colors" x-bind:class="answers['{{ $q->id }}'] ? 'border-blue-300' : ''">
                        <div class="flex items-center justify-between pb-3 mb-4 border-b border-line text-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-blue-100 text-[#2563EB] font-bold flex items-center justify-center text-xs">
                                    {{ $index + 1 }}
                                </span>
                                <span class="font-bold text-text">Soal Nomor {{ $index + 1 }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-text-muted">
                                    Bobot: {{ number_format($q->points, 0) }} Poin
                                </span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700">
                                    {{ $q->bloom_level ?? 'C4' }}
                                </span>
                            </div>
                        </div>

                        <!-- Prompt Soal -->
                        <div class="text-sm text-text font-medium leading-relaxed mb-4">
                            {{ $q->prompt }}
                        </div>

                        <!-- Pilihan Jawaban (Radio Choices) -->
                        <div class="space-y-2.5">
                            @if(is_array($q->options))
                                @foreach($q->options as $opt)
                                    <label class="flex items-start gap-3 p-3 rounded-[8px] border border-line bg-surface hover:bg-canvas/60 hover:border-blue-300 transition-colors cursor-pointer text-xs">
                                        <input 
                                            type="radio" 
                                            name="answers[{{ $q->id }}]" 
                                            value="{{ $opt['key'] }}" 
                                            x-model="answers['{{ $q->id }}']"
                                            class="mt-0.5 text-[#2563EB] focus:ring-accent"
                                        />
                                        <div class="flex-1">
                                            <span class="font-bold text-text mr-1.5">{{ $opt['key'] }}.</span>
                                            <span class="text-text leading-relaxed">{{ $opt['text'] }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            @endif
                        </div>
                    </x-ui.card>
                @empty
                    <x-ui.card class="p-8 text-center text-text-muted">
                        <p class="text-sm">Soal ujian sedang disiapkan oleh dosen pengampu.</p>
                    </x-ui.card>
                @endforelse

                <!-- Bottom Submit Bar -->
                <div class="bg-surface border border-line rounded-[10px] p-5 shadow-2xs flex items-center justify-between">
                    <div class="flex items-center gap-2 text-xs text-text-muted">
                        <x-icon name="check-circle" :size="15" class="text-emerald-500" />
                        <span>Jawaban tersimpan otomatis ke server</span>
                    </div>

                    <button 
                        type="submit" 
                        onclick="return confirm('Apakah Anda yakin ingin menyelesaikan dan mengumpulkan ujian ini sekarang? Pastikan seluruh soal telah terjawab.');"
                        class="px-6 py-2.5 rounded-[6px] bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold transition-colors inline-flex items-center gap-2 shadow-2xs cursor-pointer"
                    >
                        <span>Selesaikan & Kumpulkan Ujian</span>
                        <x-icon name="arrow-right" :size="14" />
                    </button>
                </div>
            </form>
        </div>

        <!-- Right Column: Question Navigator & Ringkasan -->
        <div class="lg:col-span-4 space-y-5">
            
            <!-- 1. Navigator Soal Grid -->
            <x-ui.card class="p-5">
                <div class="flex items-center justify-between mb-3 text-xs">
                    <h3 class="font-bold text-text flex items-center gap-2">
                        <x-icon name="grid" :size="15" class="text-[#2563EB]" />
                        <span>Navigasi Soal</span>
                    </h3>
                    <span class="font-bold text-[#2563EB]" x-text="answeredCount + '/' + totalQuestions + ' Terjawab'">
                        0/{{ $quiz->questions->count() }} Terjawab
                    </span>
                </div>

                <!-- Number Grid Pills -->
                <div class="grid grid-cols-5 gap-2 text-xs">
                    @foreach($quiz->questions as $index => $q)
                        <a 
                            href="#soal-{{ $index + 1 }}" 
                            :class="answers['{{ $q->id }}'] ? 'bg-[#2563EB] text-white border-[#2563EB]' : 'bg-surface border-line text-text hover:bg-canvas'"
                            class="h-9 rounded-[6px] border font-bold flex items-center justify-center transition-colors text-xs"
                        >
                            {{ $index + 1 }}
                        </a>
                    @endforeach
                </div>

                <div class="mt-4 pt-3 border-t border-line text-[11px] text-text-muted flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded bg-[#2563EB]"></span>
                        <span>Sudah Terjawab</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded border border-line bg-surface"></span>
                        <span>Belum Dijawab</span>
                    </div>
                </div>
            </x-ui.card>

            <!-- 2. Informasi & Peraturan Ujian -->
            <x-ui.card class="p-5 text-xs space-y-3">
                <h3 class="font-bold text-text flex items-center gap-2">
                    <x-icon name="shield" :size="15" class="text-purple-600" />
                    <span>Ketentuan Ujian Terawasi</span>
                </h3>

                <ul class="space-y-2 text-text-muted leading-relaxed">
                    <li class="flex items-start gap-2">
                        <x-icon name="check" :size="13" class="text-emerald-600 shrink-0 mt-0.5" />
                        <span>Dilarang berpindah tab browser lebih dari 3 kali.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <x-icon name="check" :size="13" class="text-emerald-600 shrink-0 mt-0.5" />
                        <span>Standar kelulusan minimum: {{ number_format($quiz->pass_score ?: 70, 0) }} Poin.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <x-icon name="check" :size="13" class="text-emerald-600 shrink-0 mt-0.5" />
                        <span>Penyelesaian ujian tepat waktu memberikan reward +50 XP.</span>
                    </li>
                </ul>
            </x-ui.card>

        </div>

    </div>

</div>
@endsection
