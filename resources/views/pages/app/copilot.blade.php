@extends('layouts.app')

@section('title', 'AI Copilot — Arunika')
@section('page_breadcrumb', 'AI Copilot')

@section('content')
<div 
    x-data="{
        prompt: '',
        context: '{{ addslashes($defaultContext) }}',
        mode: 'chat',
        socratic: true,
        loading: false,
        toastMessage: '',
        showToast: false,
        messages: [
            {
                role: 'ai',
                text: 'Halo Arya! Saya Arunika AI Copilot, pendamping belajar akademik Anda. Anda sedang berada di workspace cerdas untuk mata kuliah {{ $defaultContext }}. Apa materi atau tugas yang ingin kita bedah bersama?',
                time: 'Baru saja',
                mode: 'chat'
            }
        ],
        suggestions: {{ json_encode($suggestions) }},
        triggerToast(msg) {
            this.toastMessage = msg;
            this.showToast = true;
            setTimeout(() => { this.showToast = false; }, 3000);
        },
        async sendMessage(promptText, overrideMode = null) {
            const text = promptText || this.prompt;
            if (!text || !text.trim() || this.loading) return;

            const activeMode = overrideMode || this.mode;

            // Push user message
            this.messages.push({
                role: 'user',
                text: text,
                time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                mode: activeMode
            });

            this.prompt = '';
            this.loading = true;

            this.$nextTick(() => {
                const el = document.getElementById('copilotChatConsole');
                if (el) el.scrollTop = el.scrollHeight;
            });

            try {
                const res = await fetch('{{ route('copilot.ask') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        prompt: text,
                        socratic: this.socratic,
                        context: this.context,
                        mode: activeMode
                    })
                });

                const data = await res.json();
                this.loading = false;

                if (data.success) {
                    this.messages.push({
                        role: 'ai',
                        text: data.response,
                        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                        mode: data.mode,
                        provider: data.provider,
                        xp: data.xp_awarded
                    });

                    if (data.xp_awarded > 0) {
                        this.triggerToast('🎉 +10 XP diperoleh dari diskusi cerdas bersama AI!');
                    }

                    if (data.suggested_prompts && data.suggested_prompts.length) {
                        this.suggestions = data.suggested_prompts;
                    }
                } else {
                    this.messages.push({
                        role: 'ai',
                        text: 'Mohon maaf, AI Copilot sedang memproses permintaan lain. Silakan coba kembali.',
                        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                        mode: activeMode
                    });
                }
            } catch (err) {
                this.loading = false;
                this.messages.push({
                    role: 'ai',
                    text: 'Terjadi kendala jaringan saat menghubungi layanan AI Copilot.',
                    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                    mode: activeMode
                });
            }

            this.$nextTick(() => {
                const el = document.getElementById('copilotChatConsole');
                if (el) el.scrollTop = el.scrollHeight;
            });
        },
        clearChat() {
            this.messages = [
                {
                    role: 'ai',
                    text: 'Percakapan telah direset. Silakan ajukan pertanyaan atau pilih mode belajar baru di atas.',
                    time: 'Baru saja',
                    mode: 'chat'
                }
            ];
            this.triggerToast('Riwayat diskusi dibersihkan.');
        },
        copyText(text) {
            navigator.clipboard.writeText(text);
            this.triggerToast('Teks berhasil disalin ke clipboard!');
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
                <x-icon name="bot" :size="20" />
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-[4px] bg-blue-50 text-[#2563EB] text-[10px] font-bold border border-blue-200">
                        Arunika Cognitive Engine
                    </span>
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-[11px] text-emerald-700 font-medium">Asisten Aktif</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-navy-950 tracking-tight mt-1">
                    Arunika AI Copilot Workspace
                </h1>
                <p class="text-xs text-text-muted mt-0.5">
                    Tutor Sokrates cerdas berbasis Outcome-Based Education (OBE) untuk pendalaman materi dan persiapan ujian.
                </p>
            </div>
        </div>

        <!-- Right: Inspirational / Quick Stat Card -->
        <div class="flex items-center gap-3 self-start md:self-auto">
            <div class="px-3.5 py-2 rounded-[8px] border border-line bg-surface shadow-2xs flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center">
                    <x-icon name="sparkles" :size="16" />
                </div>
                <div>
                    <span class="text-[10px] text-text-muted block">Bonus Harian</span>
                    <span class="font-bold text-xs text-text">+10 XP / Diskusi</span>
                </div>
            </div>

            <button 
                type="button" 
                @click="clearChat()"
                class="px-3 py-2 rounded-[8px] border border-line bg-surface hover:bg-canvas text-xs font-medium text-text shadow-2xs transition-colors cursor-pointer inline-flex items-center gap-1.5"
            >
                <x-icon name="rotate-ccw" :size="13" class="text-text-muted" />
                <span>Reset Sesi</span>
            </button>
        </div>

    </div>

    <!-- ==========================================
         2. LEARNING CONTEXT & MODES TOOLBAR
         ========================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 items-center">
        
        <!-- Left: Course Context Selector -->
        <div class="lg:col-span-2">
            <label class="block text-[11px] font-bold text-text-muted mb-1.5 uppercase tracking-wider">
                Mata Kuliah Konteks Pembelajaran
            </label>
            <div class="relative">
                <select 
                    x-model="context" 
                    @change="triggerToast('Konteks dialihkan ke ' + context)"
                    class="w-full px-3.5 py-2 rounded-[8px] border border-line bg-surface text-xs font-semibold text-text shadow-2xs focus:outline-none focus:border-[#2563EB] cursor-pointer"
                >
                    @foreach ($enrolledClasses as $class)
                        <option value="{{ $class->course->name }}">
                            {{ $class->course->code }} — {{ $class->course->name }} ({{ $class->lecturer->name }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Right: 4 Interactive Learning Modes -->
        <div class="lg:col-span-2">
            <label class="block text-[11px] font-bold text-text-muted mb-1.5 uppercase tracking-wider">
                Mode Pembelajaran AI
            </label>
            <div class="grid grid-cols-4 gap-2 text-xs">
                
                <button 
                    type="button" 
                    @click="mode = 'chat'; triggerToast('Mode Tutor Sokrates aktif')"
                    :class="mode === 'chat' ? 'bg-[#1A2E5E] text-white shadow-2xs' : 'bg-surface hover:bg-canvas text-text border border-line'"
                    class="py-1.5 px-2 rounded-[6px] font-medium text-center transition-all cursor-pointer flex flex-col items-center gap-1"
                >
                    <x-icon name="message-square" :size="13" />
                    <span class="text-[11px] font-bold leading-tight">Tutor Konsep</span>
                </button>

                <button 
                    type="button" 
                    @click="mode = 'flashcard'; triggerToast('Mode Generator Flashcard aktif')"
                    :class="mode === 'flashcard' ? 'bg-[#1A2E5E] text-white shadow-2xs' : 'bg-surface hover:bg-canvas text-text border border-line'"
                    class="py-1.5 px-2 rounded-[6px] font-medium text-center transition-all cursor-pointer flex flex-col items-center gap-1"
                >
                    <x-icon name="layers" :size="13" />
                    <span class="text-[11px] font-bold leading-tight">Flashcard</span>
                </button>

                <button 
                    type="button" 
                    @click="mode = 'summarize'; triggerToast('Mode Rangkuman Modul aktif')"
                    :class="mode === 'summarize' ? 'bg-[#1A2E5E] text-white shadow-2xs' : 'bg-surface hover:bg-canvas text-text border border-line'"
                    class="py-1.5 px-2 rounded-[6px] font-medium text-center transition-all cursor-pointer flex flex-col items-center gap-1"
                >
                    <x-icon name="file-text" :size="13" />
                    <span class="text-[11px] font-bold leading-tight">Rangkuman</span>
                </button>

                <button 
                    type="button" 
                    @click="mode = 'quiz'; triggerToast('Mode Latihan Kuis Mandiri aktif')"
                    :class="mode === 'quiz' ? 'bg-[#1A2E5E] text-white shadow-2xs' : 'bg-surface hover:bg-canvas text-text border border-line'"
                    class="py-1.5 px-2 rounded-[6px] font-medium text-center transition-all cursor-pointer flex flex-col items-center gap-1"
                >
                    <x-icon name="help-circle" :size="13" />
                    <span class="text-[11px] font-bold leading-tight">Latihan Kuis</span>
                </button>

            </div>
        </div>

    </div>

    <!-- ==========================================
         3. MAIN CONSOLE GRID (CHAT + SIDEBAR INFO)
         ========================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
        
        <!-- Left 2 Cols: Chat & Study Console -->
        <div class="lg:col-span-2 space-y-3">
            <x-ui.card class="p-0 overflow-hidden flex flex-col h-[650px] shadow-2xs">
                
                <!-- Chat Window Topbar -->
                <div class="px-4 py-3 border-b border-line bg-surface flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-blue-50 text-[#2563EB] flex items-center justify-center">
                            <x-icon name="bot" :size="14" />
                        </div>
                        <div>
                            <span class="font-bold text-xs text-text block">Sesi Diskusi Aktif</span>
                            <span class="text-[10px] text-text-muted" x-text="'Mata Kuliah: ' + context"></span>
                        </div>
                    </div>

                    <!-- Socratic Mode Toggle -->
                    <div class="flex items-center gap-2 bg-canvas px-3 py-1 rounded-[6px] border border-line text-xs">
                        <span class="text-[11px] font-medium text-text">Metode Sokrates</span>
                        <button 
                            type="button" 
                            @click="socratic = !socratic; triggerToast(socratic ? 'Metode Sokrates aktif' : 'Jawaban langsung aktif')" 
                            :class="socratic ? 'bg-[#2563EB]' : 'bg-slate-300'"
                            class="relative inline-flex h-4 w-7 shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none"
                        >
                            <span 
                                :class="socratic ? 'translate-x-3.5' : 'translate-x-0.5'"
                                class="inline-block h-3 w-3 transform rounded-full bg-white transition duration-200 ease-in-out mt-0.5 shadow-xs"
                            ></span>
                        </button>
                    </div>
                </div>

                <!-- Chat Stream Console (Scrollable) -->
                <div id="copilotChatConsole" class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-4 text-xs bg-canvas/30">
                    <template x-for="(msg, index) in messages" :key="index">
                        <div>
                            <!-- User Message -->
                            <template x-if="msg.role === 'user'">
                                <div class="flex justify-end mb-3">
                                    <div class="max-w-[80%] rounded-[10px] rounded-tr-xs bg-[#1A2E5E] text-white p-3.5 shadow-2xs">
                                        <p class="leading-relaxed whitespace-pre-wrap text-xs" x-text="msg.text"></p>
                                        <span class="text-[9px] text-white/60 block text-right mt-1.5" x-text="msg.time"></span>
                                    </div>
                                </div>
                            </template>

                            <!-- AI Message -->
                            <template x-if="msg.role === 'ai'">
                                <div class="flex items-start gap-3 mb-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-[#2563EB] flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                                        <x-icon name="bot" :size="16" />
                                    </div>
                                    <div class="max-w-[85%] rounded-[10px] rounded-tl-xs bg-surface border border-line text-text p-4 shadow-2xs space-y-2">
                                        <div class="prose prose-xs leading-relaxed whitespace-pre-wrap font-sans text-xs text-text" x-text="msg.text"></div>
                                        
                                        <div class="flex items-center justify-between pt-2 border-t border-line/60 text-[10px] text-text-muted">
                                            <div class="flex items-center gap-2">
                                                <span x-show="msg.xp" class="text-amber-600 font-bold bg-amber-50 px-2 py-0.5 rounded-[4px] border border-amber-200">
                                                    +10 XP
                                                </span>
                                                <span class="italic text-[9px]" x-text="msg.provider ? 'Engine: ' + msg.provider : ''"></span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button 
                                                    type="button" 
                                                    @click="copyText(msg.text)" 
                                                    class="hover:text-[#2563EB] cursor-pointer inline-flex items-center gap-1"
                                                >
                                                    <x-icon name="copy" :size="11" />
                                                    <span>Salin</span>
                                                </button>
                                                <span>&bull;</span>
                                                <span x-text="msg.time"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Loading Indicator -->
                    <div x-show="loading" class="flex items-start gap-3 mb-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-[#2563EB] flex items-center justify-center shrink-0 mt-0.5">
                            <x-icon name="bot" :size="16" />
                        </div>
                        <div class="rounded-[10px] rounded-tl-xs bg-surface border border-line p-4 shadow-2xs flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#2563EB] animate-bounce"></span>
                            <span class="w-2 h-2 rounded-full bg-[#2563EB] animate-bounce [animation-delay:0.2s]"></span>
                            <span class="w-2 h-2 rounded-full bg-[#2563EB] animate-bounce [animation-delay:0.4s]"></span>
                            <span class="text-xs text-text-muted ml-2">Arunika AI Copilot sedang menelaah silabus dan konsep materi...</span>
                        </div>
                    </div>
                </div>

                <!-- Suggested Chips Toolbar -->
                <div class="px-4 py-2 border-t border-line/70 bg-surface">
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                        <span class="text-[10px] font-bold text-text-muted uppercase shrink-0">Saran:</span>
                        <template x-for="chip in suggestions" :key="chip">
                            <button 
                                type="button" 
                                @click="sendMessage(chip)" 
                                class="px-2.5 py-1 rounded-[6px] bg-canvas hover:bg-blue-50 text-[11px] text-text hover:text-[#2563EB] border border-line hover:border-blue-200 transition-colors cursor-pointer shrink-0"
                                x-text="chip"
                            ></button>
                        </template>
                    </div>
                </div>

                <!-- Input Footer Bar -->
                <div class="p-3 sm:p-4 border-t border-line bg-surface">
                    <form @submit.prevent="sendMessage(prompt)" class="flex items-center gap-2">
                        <input 
                            type="text" 
                            x-model="prompt" 
                            :disabled="loading"
                            placeholder="Ketik pertanyaan konsep, minta contoh kode, atau buat flashcard..." 
                            class="flex-1 px-3.5 py-2.5 rounded-[8px] border border-line bg-canvas text-xs text-text placeholder:text-text-muted focus:outline-none focus:border-[#2563EB] focus:bg-surface transition-colors"
                        >
                        <button 
                            type="submit" 
                            :disabled="loading || !prompt.trim()"
                            :class="(loading || !prompt.trim()) ? 'opacity-50 cursor-not-allowed bg-slate-300' : 'bg-[#2563EB] hover:bg-blue-700 cursor-pointer'"
                            class="px-4 py-2.5 rounded-[8px] text-white text-xs font-semibold shrink-0 shadow-2xs transition-colors inline-flex items-center gap-1.5"
                        >
                            <x-icon name="send" :size="14" />
                            <span class="hidden sm:inline">Kirim</span>
                        </button>
                    </form>
                </div>

            </x-ui.card>
        </div>

        <!-- Right 1 Col: Pedagogical & OBE Guidance Cards -->
        <div class="space-y-4">
            
            <!-- Card 1: Metode Sokrates & Integritas Akademik -->
            <x-ui.card class="p-4 sm:p-5">
                <div class="flex items-start gap-3 mb-3">
                    <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <x-icon name="shield-check" :size="16" />
                    </div>
                    <div>
                        <h3 class="font-bold text-xs sm:text-sm text-text">Pedagogi Sokrates</h3>
                        <p class="text-[11px] text-text-muted mt-0.5">Komitmen integritas akademik kampus.</p>
                    </div>
                </div>
                <p class="text-xs text-text-muted leading-relaxed">
                    Arunika AI Copilot dirancang agar <strong>tidak langsung membocorkan jawaban</strong> pengerjaan tugas atau ujian. Sebaliknya, AI membimbing Anda membedah masalah melalui pertanyaan pemantik konseptual agar logika berpikir terasah optimal.
                </p>
            </x-ui.card>

            <!-- Card 2: Capaian Pembelajaran (OBE Matrix) -->
            <x-ui.card class="p-4 sm:p-5">
                <div class="flex items-start gap-3 mb-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <x-icon name="award" :size="16" />
                    </div>
                    <div>
                        <h3 class="font-bold text-xs sm:text-sm text-text">Capaian CPMK Terhubung</h3>
                        <p class="text-[11px] text-text-muted mt-0.5">Pemetaan kompetensi semester aktif.</p>
                    </div>
                </div>
                <div class="space-y-2.5 text-xs">
                    <div class="p-2.5 rounded-[6px] border border-line bg-canvas/30 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-text">CPMK-01</span>
                            <span class="text-emerald-600 font-semibold text-[11px]">88%</span>
                        </div>
                        <p class="text-[11px] text-text-muted">Menerapkan konsep enkapsulasi, pewarisan, dan antarmuka.</p>
                    </div>

                    <div class="p-2.5 rounded-[6px] border border-line bg-canvas/30 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-text">CPMK-02</span>
                            <span class="text-emerald-600 font-semibold text-[11px]">92%</span>
                        </div>
                        <p class="text-[11px] text-text-muted">Merancang arsitektur sistem berbasis SOLID Principles.</p>
                    </div>
                </div>
            </x-ui.card>

            <!-- Card 3: Shortcut Belajar Cepat -->
            <x-ui.card class="p-4 sm:p-5">
                <h4 class="font-bold text-xs text-text mb-2.5">Fitur Unggulan Copilot</h4>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center gap-2 text-text">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#2563EB]"></span>
                        <span>Auto-flashcard generator untuk memori aktif</span>
                    </div>
                    <div class="flex items-center gap-2 text-text">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#2563EB]"></span>
                        <span>Simulasi soal ujian pilihan ganda &amp; pembahasan</span>
                    </div>
                    <div class="flex items-center gap-2 text-text">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#2563EB]"></span>
                        <span>Rangkuman intisari RPS pertemuan perkuliahan</span>
                    </div>
                </div>
            </x-ui.card>

        </div>

    </div>

    <!-- ==========================================
         4. TOAST NOTIFIKASI
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
