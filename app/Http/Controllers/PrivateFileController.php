<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\AttendanceLog;
use App\Models\DiscussionThread;
use App\Models\Material;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PrivateFileController extends Controller
{
    /**
     * Unduh atau tampilkan berkas materi perkuliahan.
     */
    public function downloadMaterial(Request $request, Material $material): Response
    {
        Gate::authorize('download', $material);

        $path = $material->file_path;
        $fallbackName = $material->title . '.' . pathinfo($path ?? '', PATHINFO_EXTENSION);

        return $this->servePrivateFile($path, $fallbackName, $request->boolean('download'));
    }

    /**
     * Unduh berkas lampiran instruksi/panduan tugas.
     */
    public function downloadAssignment(Request $request, Assignment $assignment): Response
    {
        Gate::authorize('download', $assignment);

        $path = $assignment->lampiran_path;
        $fallbackName = $assignment->title . '.' . pathinfo($path ?? '', PATHINFO_EXTENSION);

        return $this->servePrivateFile($path, $fallbackName, $request->boolean('download'));
    }

    /**
     * Unduh berkas pengumpulan tugas mahasiswa.
     */
    public function downloadSubmission(Request $request, Submission $submission): Response
    {
        Gate::authorize('download', $submission);

        $path = $submission->file_path;
        $studentName = $submission->user?->name ?? 'Mahasiswa';
        $fallbackName = 'Pengumpulan_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $studentName) . '.' . pathinfo($path ?? '', PATHINFO_EXTENSION);

        return $this->servePrivateFile($path, $fallbackName, $request->boolean('download'));
    }

    /**
     * Unduh berkas surat izin kehadiran (izin/sakit).
     */
    public function downloadPermit(Request $request, AttendanceLog $log): Response
    {
        $user = $request->user();

        // Admin (sesuai middleware /admin)
        $isAdmin = $user && $user->role === UserRole::Admin;
        $isOwner = $user && $user->id === $log->user_id;
        $lecturerId = $log->attendanceSession?->meeting?->academicClass?->lecturer_id;
        $isClassLecturer = $user && $user->role === UserRole::Lecturer && $lecturerId === $user->id;

        if (! ($isAdmin || $isOwner || $isClassLecturer)) {
            abort(403, 'Anda tidak berhak mengakses berkas surat izin ini.');
        }

        // Mahasiswa yang dikeluarkan tidak boleh mengunduh berkas
        if ($isOwner && ! $isAdmin) {
            $enrollment = $log->attendanceSession?->meeting?->academicClass?->enrollments()
                ->where('user_id', $user->id)
                ->first();

            if ($enrollment && $enrollment->isDikeluarkan()) {
                abort(403, 'Akses ditolak: Anda telah dikeluarkan dari kelas ini.');
            }
        }

        // Ekstrak path berkas dari notes: "... (Berkas: permits/xyz.pdf)"
        $path = null;
        if (! empty($log->notes) && preg_match('/\(Berkas:\s*([^)]+)\)/', $log->notes, $matches)) {
            $path = trim($matches[1]);
        }

        return $this->servePrivateFile($path, 'surat_izin_' . $log->id, $request->boolean('download'));
    }

    /**
     * Unduh berkas lampiran diskusi.
     */
    public function downloadDiscussion(Request $request, DiscussionThread $thread): Response
    {
        Gate::authorize('view', $thread);

        $path = $thread->attachment_path;
        $fallbackName = $thread->title . '.' . pathinfo($path ?? '', PATHINFO_EXTENSION);

        return $this->servePrivateFile($path, $fallbackName, $request->boolean('download'));
    }

    /**
     * Sajikan berkas dari penyimpanan privat dengan allowlist dan pengamanan ketat.
     */
    protected function servePrivateFile(?string $rawPath, string $fallbackName, bool $forceDownload = false): Response
    {
        if (empty($rawPath)) {
            abort(404, 'Berkas fisik tidak ditemukan di server.');
        }

        $cleanPath = ltrim($rawPath, '/\\');

        // Utamakan disk 'private', fallback ke 'public' untuk kompatibilitas data lama
        $disk = null;
        if (Storage::disk('private')->exists($cleanPath)) {
            $disk = 'private';
        } elseif (Storage::disk('public')->exists($cleanPath)) {
            $disk = 'public';
        }

        if ($disk === null) {
            abort(404, 'Berkas fisik tidak ditemukan di server.');
        }

        $filename = basename($cleanPath);
        if (empty($filename) || $filename === '.' || $filename === '..') {
            $filename = $fallbackName;
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Allowlist MIME types
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'zip' => 'application/zip',
        ];

        $contentType = $mimeTypes[$extension] ?? 'application/octet-stream';

        // Hanya pdf, jpg, jpeg, png yang diizinkan inline; sisanya selalu attachment
        $inlineAllowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
        $isInline = ! $forceDownload && in_array($extension, $inlineAllowedExtensions, true);

        // Sanitasi nama berkas di Content-Disposition (cegah injection)
        $cleanFilename = preg_replace('/[^\w\.\-\s]/u', '_', $filename);
        $cleanFilename = trim(str_replace(['"', ';', "\r", "\n"], '', $cleanFilename));
        if ($cleanFilename === '') {
            $cleanFilename = 'berkas_' . time() . ($extension ? '.' . $extension : '');
        }
        $encodedFilename = rawurlencode($cleanFilename);

        $headers = [
            'Content-Type' => $contentType,
            'X-Content-Type-Options' => 'nosniff',
        ];

        if ($isInline) {
            $headers['Content-Disposition'] = "inline; filename=\"{$cleanFilename}\"; filename*=UTF-8''{$encodedFilename}";
            $headers['Content-Security-Policy'] = 'sandbox';
        } else {
            $headers['Content-Disposition'] = "attachment; filename=\"{$cleanFilename}\"; filename*=UTF-8''{$encodedFilename}";
        }

        return response(Storage::disk($disk)->get($cleanPath), 200, $headers);
    }
}
