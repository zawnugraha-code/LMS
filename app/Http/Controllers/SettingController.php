<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use InvalidArgumentException;

class SettingController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    /**
     * Helper to resolve authenticated user.
     */
    protected function resolveUser(): ?User
    {
        return Auth::user();
    }

    /**
     * Display the settings (Pengaturan) main page.
     */
    public function index(Request $request): View
    {
        $user = $this->resolveUser();
        $data = $this->settingService->getSettingData($user);

        return view('pages.app.settings', $data);
    }

    /**
     * Update user profile information and photo.
     */
    public function updateProfile(Request $request): RedirectResponse|JsonResponse
    {
        $user = $this->resolveUser();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
        ]);

        $avatarFile = $request->file('avatar');
        $updatedUser = $this->settingService->updateProfile($user, $validated, $avatarFile);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Informasi profil berhasil diperbarui.',
                'user' => [
                    'name' => $updatedUser->name,
                    'email' => $updatedUser->email,
                    'avatar_path' => $updatedUser->avatar_path,
                ],
            ]);
        }

        return redirect()->route('app.settings')->with('success', 'Informasi profil berhasil disimpan.');
    }

    /**
     * Update user password with current password verification.
     */
    public function updatePassword(Request $request): RedirectResponse|JsonResponse
    {
        $user = $this->resolveUser();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $this->settingService->updatePassword(
                $user,
                $validated['current_password'],
                $validated['password']
            );
        } catch (InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['current_password' => $e->getMessage()]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diperbarui!',
            ]);
        }

        return redirect()->route('app.settings')->with('success', 'Password berhasil diperbarui.');
    }

    /**
     * Update theme, text size, notifications, and localization preferences.
     */
    public function updatePreferences(Request $request): RedirectResponse|JsonResponse
    {
        $user = $this->resolveUser();
        $payload = $request->all();

        $preference = $this->settingService->updatePreferences($user, $payload);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Preferensi berhasil disimpan.',
                'preferences' => [
                    'theme' => $preference->theme,
                    'font_size' => $preference->font_size,
                    'locale' => $preference->locale,
                    'timezone' => $preference->timezone,
                    'date_format' => $preference->date_format,
                    'time_format' => $preference->time_format,
                    'two_factor_enabled' => $preference->two_factor_enabled,
                    'google_connected' => ! empty($preference->google_id),
                    'notifications' => $preference->notifications,
                ],
            ]);
        }

        return redirect()->route('app.settings')->with('success', 'Preferensi berhasil diperbarui.');
    }

    /**
     * Submit a support ticket from settings or help section.
     */
    public function submitSupportTicket(Request $request): RedirectResponse|JsonResponse
    {
        $user = $this->resolveUser();

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:50'],
            'message' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,zip,jpg,jpeg,png', 'max:10240'],
        ]);

        $ticket = $this->settingService->createSupportTicket(
            $user,
            $validated,
            $request->file('attachment')
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tiket bantuan #'.substr($ticket->ulid, -6).' berhasil diajukan.',
                'ticket' => $ticket,
            ]);
        }

        return redirect()->back()->with('success', 'Tiket bantuan #'.substr($ticket->ulid, -6).' berhasil dikirim ke tim support.');
    }

    /**
     * Display a secondary settings sub-page (Item 14 of docs/pages-without-design.md).
     */
    public function showSection(Request $request, string $section): View
    {
        $user = $this->resolveUser();
        $data = $this->settingService->getSectionData($user, $section);

        return view('pages.app.settings-section', $data);
    }
}
