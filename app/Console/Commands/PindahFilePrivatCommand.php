<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use App\Models\AttendanceLog;
use App\Models\DiscussionThread;
use App\Models\Material;
use App\Models\Submission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PindahFilePrivatCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'arunika:pindah-file-privat 
                            {--dry-run : Jalankan simulasi tanpa menyalin atau menghapus berkas (default)}
                            {--jalankan : Jalankan penyalinan berkas dari disk public ke disk private dengan verifikasi integritas}
                            {--hapus-sumber : Hapus berkas sumber di storage/app/public setelah berhasil diverifikasi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasikan berkas privat dari storage/app/public ke storage/app/private dengan verifikasi ukuran dan hash SHA-256';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isJalankan = (bool) $this->option('jalankan');
        $isHapusSumber = (bool) $this->option('hapus-sumber');
        $isDryRun = (bool) $this->option('dry-run') || (! $isJalankan && ! $isHapusSumber);

        $this->newLine();
        $this->info('================================================================');
        $this->info('      ARUNIKA: MIGRASI PENYIMPANAN BERKAS PRIVAT                ');
        $this->info('================================================================');

        if ($isDryRun) {
            $this->warn('MODE: DRY-RUN (Simulasi aktif. Tidak ada berkas yang disalin/dihapus)');
        } elseif ($isJalankan && $isHapusSumber) {
            $this->alert('MODE: EKSEKUSI PENUH (Menyalin, Verifikasi Hash, & Hapus Berkas Sumber)');
        } elseif ($isJalankan) {
            $this->info('MODE: SALIN & VERIFIKASI (Menyalin dan verifikasi saja; sumber TIDAK dihapus)');
        } elseif ($isHapusSumber) {
            $this->warn('MODE: PENGHAPUSAN SUMBER (Hanya menghapus berkas sumber yang sudah terverifikasi di private)');
        }

        $this->newLine();

        $categories = [
            'materials' => [
                'label' => 'Materi Kuliah (materials)',
                'paths' => Material::whereNotNull('file_path')->pluck('file_path')->all(),
                'dir' => 'materials',
            ],
            'assessments' => [
                'label' => 'Lampiran Tugas (assessments)',
                'paths' => Assignment::whereNotNull('lampiran_path')->pluck('lampiran_path')->all(),
                'dir' => 'assessments',
            ],
            'submissions' => [
                'label' => 'Pengumpulan Mahasiswa (submissions)',
                'paths' => Submission::whereNotNull('file_path')->pluck('file_path')->all(),
                'dir' => 'submissions',
            ],
            'discussions' => [
                'label' => 'Lampiran Diskusi (discussions)',
                'paths' => DiscussionThread::whereNotNull('attachment_path')->pluck('attachment_path')->all(),
                'dir' => 'discussions',
            ],
            'permits' => [
                'label' => 'Surat Izin Kehadiran (permits)',
                'paths' => $this->extractPermitPaths(),
                'dir' => 'permits',
            ],
            'support' => [
                'label' => 'Lampiran Dukungan Teknis (support)',
                'paths' => [],
                'dir' => 'support',
            ],
        ];

        // Temukan juga berkas fisik orphan di folder publik yang tidak terdata di DB
        foreach (['materials', 'assessments', 'submissions', 'discussions', 'permits', 'support'] as $dir) {
            if (Storage::disk('public')->exists($dir)) {
                $physicalFiles = Storage::disk('public')->allFiles($dir);
                $categories[$dir]['paths'] = array_values(array_unique(array_merge($categories[$dir]['paths'], $physicalFiles)));
            }
        }

        $tableRows = [];
        $totalRecords = 0;
        $totalRealFiles = 0;
        $totalMockFiles = 0;
        $totalCopiedVerified = 0;
        $totalDeletedSource = 0;
        $errors = [];

        foreach ($categories as $catKey => $catData) {
            $paths = array_values(array_unique(array_filter($catData['paths'])));
            $recCount = count($paths);
            $realCount = 0;
            $mockCount = 0;
            $copiedCount = 0;
            $deletedCount = 0;

            foreach ($paths as $rawPath) {
                $cleanPath = ltrim($rawPath, '/\\');
                $publicFullPath = storage_path('app/public/' . $cleanPath);
                $privateFullPath = storage_path('app/private/' . $cleanPath);

                $existsInPublic = file_exists($publicFullPath) && is_file($publicFullPath);

                if ($existsInPublic) {
                    $realCount++;

                    // Operasi salin & verifikasi
                    if ($isJalankan) {
                        try {
                            File::ensureDirectoryExists(dirname($privateFullPath));
                            File::copy($publicFullPath, $privateFullPath);

                            // Verifikasi ukuran berkas
                            $srcSize = filesize($publicFullPath);
                            $dstSize = filesize($privateFullPath);

                            // Verifikasi SHA-256 hash
                            $srcHash = hash_file('sha256', $publicFullPath);
                            $dstHash = hash_file('sha256', $privateFullPath);

                            if ($srcSize === $dstSize && $srcHash === $dstHash) {
                                $copiedCount++;

                                if ($isHapusSumber) {
                                    File::delete($publicFullPath);
                                    $deletedCount++;
                                }
                            } else {
                                $errors[] = "Integritas berkas tidak cocok: {$cleanPath} (Ukuran: {$srcSize} vs {$dstSize}, Hash: {$srcHash} vs {$dstHash})";
                            }
                        } catch (\Throwable $e) {
                            $errors[] = "Gagal memproses berkas {$cleanPath}: " . $e->getMessage();
                        }
                    } elseif ($isHapusSumber && ! $isJalankan) {
                        // Hanya menghapus sumber jika private sudah ada dan terverifikasi identik
                        if (file_exists($privateFullPath) && is_file($privateFullPath)) {
                            $srcSize = filesize($publicFullPath);
                            $dstSize = filesize($privateFullPath);
                            $srcHash = hash_file('sha256', $publicFullPath);
                            $dstHash = hash_file('sha256', $privateFullPath);

                            if ($srcSize === $dstSize && $srcHash === $dstHash) {
                                File::delete($publicFullPath);
                                $deletedCount++;
                            } else {
                                $errors[] = "Sumber {$cleanPath} tidak dihapus: target di private tidak identik (hash/ukuran berbeda).";
                            }
                        } else {
                            $errors[] = "Sumber {$cleanPath} tidak dihapus: target di private belum ada.";
                        }
                    }
                } else {
                    $mockCount++;
                }
            }

            $totalRecords += $recCount;
            $totalRealFiles += $realCount;
            $totalMockFiles += $mockCount;
            $totalCopiedVerified += $copiedCount;
            $totalDeletedSource += $deletedCount;

            $tableRows[] = [
                $catData['label'],
                $recCount,
                $realCount,
                $mockCount,
                $isDryRun ? '-' : "{$copiedCount} diverifikasi",
                $isHapusSumber ? "{$deletedCount} dihapus" : '-',
            ];
        }

        $this->table(
            ['Kategori Berkas', 'Total Path/Record', 'Berkas Nyata di Disk', 'Mock Seed (Tidak Ada di Disk)', 'Tersalin & Terverifikasi', 'Sumber Dihapus'],
            $tableRows
        );

        $this->newLine();
        $this->info("RINGKASAN AUDIT PENYIMPANAN:");
        $this->line("  - Total entri/path berkas      : {$totalRecords}");
        $this->line("  - Berkas nyata ditemukan di disk : {$totalRealFiles}");
        $this->line("  - Berkas mock seed (tanpa fisik) : {$totalMockFiles}");

        if (! $isDryRun) {
            $this->info("  - Berkas disalin & diverifikasi : {$totalCopiedVerified}");
            if ($isHapusSumber) {
                $this->warn("  - Berkas sumber dihapus         : {$totalDeletedSource}");
            }
        }

        if (! empty($errors)) {
            $this->newLine();
            $this->error("Ditemukan " . count($errors) . " kendala saat memproses berkas:");
            foreach ($errors as $err) {
                $this->error("  * " . $err);
            }
            return Command::FAILURE;
        }

        $this->newLine();
        if ($isDryRun) {
            $this->comment("Petunjuk: Jalankan `php artisan arunika:pindah-file-privat --jalankan` untuk menyalin dan memverifikasi berkas nyata.");
        } elseif ($isJalankan && ! $isHapusSumber) {
            $this->comment("Penyalinan dan verifikasi integritas berhasil! Berkas sumber di storage/app/public tetap utuh.");
            $this->comment("Setelah verifikasi manual, jalankan `php artisan arunika:pindah-file-privat --hapus-sumber` untuk menghapus sumber.");
        }

        return Command::SUCCESS;
    }

    /**
     * Ekstrak path berkas izin dari kolom notes di attendance_logs.
     *
     * @return array<string>
     */
    protected function extractPermitPaths(): array
    {
        $logs = AttendanceLog::where('notes', 'like', '%(Berkas: permits/%')->pluck('notes');
        $paths = [];

        foreach ($logs as $note) {
            if (preg_match('/\(Berkas:\s*([^)]+)\)/', $note, $matches)) {
                $paths[] = trim($matches[1]);
            }
        }

        return $paths;
    }
}
