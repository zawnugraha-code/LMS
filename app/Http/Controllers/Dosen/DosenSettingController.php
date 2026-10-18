<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Services\Dosen\DosenSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class DosenSettingController extends Controller
{
    public function __construct(
        protected DosenSettingService $settingService
    ) {}

    /**
     * Display the lecturer account settings & preferences page.
     */
    public function edit(Request $request): View
    {
        $lecturer = $request->user();
        $data = $this->settingService->getSettingsData($lecturer);

        return view('pages.dosen.pengaturan.index', $data);
    }

    /**
     * Update lecturer personal & academic profile information.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $lecturer = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gelar' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$lecturer->id,
            'phone' => 'nullable|string|max:30',
            'identifier' => 'nullable|string|max:50',
            'jabatan' => 'nullable|string|max:100',
            'prodi' => 'nullable|string|max:150',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $this->settingService->updateProfile($lecturer, $validated, $request->file('avatar'));

        return redirect()->route('dosen.pengaturan.edit')
            ->with('success', 'Informasi profil pengajar berhasil diperbarui.');
    }

    /**
     * Update lecturer password with verification.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $lecturer = $request->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $this->settingService->updatePassword(
                $lecturer,
                $validated['current_password'],
                $validated['password']
            );
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['current_password' => $e->getMessage()]);
        }

        return redirect()->route('dosen.pengaturan.edit')
            ->with('success', 'Password akun pengajar berhasil diperbarui.');
    }

    /**
     * Update lecturer notification preferences.
     */
    public function updateNotifications(Request $request): RedirectResponse
    {
        $lecturer = $request->user();

        $preferences = [
            'tugas' => $request->has('tugas'),
            'ujian' => $request->has('ujian'),
            'presensi' => $request->has('presensi'),
            'diskusi' => $request->has('diskusi'),
            'ringkasan_harian' => $request->has('ringkasan_harian'),
        ];

        $this->settingService->updateNotificationPreferences($lecturer, $preferences);

        return redirect()->route('dosen.pengaturan.edit')
            ->with('success', 'Preferensi notifikasi berhasil diperbarui dan aktif di seluruh pengiriman pesan.');
    }

    /**
     * Submit support ticket from lecturer.
     */
    public function submitTicket(Request $request): RedirectResponse
    {
        $lecturer = $request->user();

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'category' => 'required|string|in:teknis,akademik,fasilitas,lainnya',
            'message' => 'required|string',
            'attachment' => 'nullable|file|max:5120',
        ]);

        $this->settingService->submitSupportTicket($lecturer, $validated, $request->file('attachment'));

        return redirect()->route('dosen.pengaturan.edit')
            ->with('success', 'Tiket bantuan berhasil diajukan ke tim dukungan teknis BAAK/IT kampus.');
    }
}
