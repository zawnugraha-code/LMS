<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\User;
use App\Services\Admin\EnrollmentAdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminEnrollmentController extends Controller
{
    public function __construct(
        protected EnrollmentAdminService $enrollmentService
    ) {}

    /**
     * Tampilkan halaman pengelolaan pendaftaran mahasiswa (Screenshot 5).
     */
    public function index(Request $request): View
    {
        $classId = $request->integer('class_id') ?: null;
        $tab = $request->query('tab', 'terdaftar');
        $search = $request->query('q');

        $data = $this->enrollmentService->getEnrollmentData($classId, $tab, $search);

        return view('pages.admin.pendaftaran.index', array_merge($data, [
            'searchQuery' => $search,
        ]));
    }

    /**
     * Daftarkan mahasiswa ke kelas.
     */
    public function store(Request $request, AcademicClass $class): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $enrollment = $this->enrollmentService->enrollStudent($class, (int) $validated['user_id']);
            return redirect()->back()
                ->with('success', "Mahasiswa {$enrollment->user->name} berhasil didaftarkan ke kelas {$class->course->code} {$class->section}.");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Daftarkan sejumlah mahasiswa sekaligus (bulk enroll).
     */
    public function bulkStore(Request $request, AcademicClass $class): RedirectResponse
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        $res = $this->enrollmentService->bulkEnrollStudents($class, $validated['user_ids']);

        if ($res['enrolled_count'] > 0) {
            $msg = "{$res['enrolled_count']} mahasiswa berhasil didaftarkan ke kelas.";
            if (! empty($res['errors'])) {
                $msg .= " (" . count($res['errors']) . " mahasiswa dilewati karena kapasitas penuh atau sudah terdaftar).";
            }
            return redirect()->back()->with('success', $msg);
        }

        return redirect()->back()->with('error', $res['errors'][0] ?? 'Tidak ada mahasiswa yang berhasil didaftarkan.');
    }

    /**
     * Keluarkan mahasiswa dari kelas (soft remove).
     */
    public function destroy(Request $request, AcademicClass $class, User $user): RedirectResponse
    {
        try {
            $this->enrollmentService->removeStudent($class, $user->id, (int) auth()->id());
            return redirect()->back()
                ->with('success', "Mahasiswa {$user->name} telah dikeluarkan dari kelas. Seluruh riwayat nilai dan presensi tetap tersimpan aman.");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Ekspor daftar mahasiswa terdaftar kelas ini ke CSV.
     */
    public function export(AcademicClass $class): StreamedResponse
    {
        return $this->enrollmentService->exportCsv($class);
    }

    /**
     * Unduh template file CSV pendaftaran mahasiswa.
     */
    public function downloadTemplate(): StreamedResponse
    {
        return $this->enrollmentService->getTemplateCsv();
    }

    /**
     * Impor file CSV mahasiswa ke kelas.
     */
    public function import(Request $request, AcademicClass $class): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|max:2048',
        ], [
            'file.required' => 'Silakan pilih berkas CSV yang akan diunggah.',
            'file.max' => 'Ukuran berkas CSV maksimal 2 MB.',
        ]);

        try {
            $res = $this->enrollmentService->importCsv($class, $request->file('file'));

            if ($res['success_count'] > 0) {
                return redirect()->back()->with(
                    'success',
                    "Impor CSV berhasil: {$res['success_count']} mahasiswa didaftarkan. " .
                    ($res['failed_count'] > 0 ? "({$res['failed_count']} baris dilewati/gagal)." : '')
                );
            }

            return redirect()->back()->with('error', 'Gagal mengimpor mahasiswa. ' . ($res['errors'][0] ?? 'Periksa format file CSV Anda.'));
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
