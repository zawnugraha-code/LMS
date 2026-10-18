<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Services\Dosen\DosenPresensiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DosenPresensiController extends Controller
{
    public function __construct(
        protected DosenPresensiService $presensiService
    ) {}

    /**
     * Display the lecturer attendance dashboard overview.
     */
    public function index(Request $request): View
    {
        $dosen = Auth::user();
        $tab = $request->query('tab', 'semua');
        $search = $request->query('q', '');
        $termId = $request->query('term_id');

        $data = $this->presensiService->getPresensiIndexData($dosen, $tab, $search, $termId);
        $data['user'] = $dosen;

        return view('pages.dosen.presensi.index', $data);
    }

    /**
     * Display the input presensi form for a class & meeting.
     */
    public function input(Request $request): View
    {
        $dosen = Auth::user();
        $classId = $request->query('class_id') ? (int) $request->query('class_id') : null;
        $meetingId = $request->query('meeting_id') ? (int) $request->query('meeting_id') : null;

        $data = $this->presensiService->getInputFormData($dosen, $classId, $meetingId);
        $data['user'] = $dosen;

        return view('pages.dosen.presensi.input', $data);
    }

    /**
     * Store submitted presensi records.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'meeting_id' => 'required|integer|exists:meetings,id',
            'mahasiswa' => 'required|array',
            'mahasiswa.*.status' => 'required|string|in:hadir,izin,sakit,tidak_hadir',
            'mahasiswa.*.catatan' => 'nullable|string|max:500',
        ]);

        $dosen = Auth::user();
        $result = $this->presensiService->savePresensi($dosen, (int) $validated['meeting_id'], $validated['mahasiswa']);

        return redirect()->route('dosen.presensi.show', $result['class_id'])
            ->with('success', $result['message']);
    }

    /**
     * Display class attendance detail & matrix.
     */
    public function show(AcademicClass $class): View
    {
        $dosen = Auth::user();
        $data = $this->presensiService->getClassDetailData($dosen, $class);
        $data['user'] = $dosen;

        return view('pages.dosen.presensi.show', $data);
    }

    /**
     * Export attendance matrix as pure native RFC 4180 CSV.
     */
    public function export(AcademicClass $class): StreamedResponse
    {
        $dosen = Auth::user();

        return $this->presensiService->exportCsv($dosen, $class);
    }
}
