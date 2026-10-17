<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\DemoRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoAuthController extends Controller
{
    /**
     * Switch demo user role instantly for pair-programming and evaluation.
     */
    public function switchRole(string $role): RedirectResponse
    {
        $roleEnum = UserRole::tryFrom($role);

        if (! $roleEnum) {
            return redirect()->back()->with('error', 'Role tidak valid.');
        }

        $user = ($roleEnum === UserRole::Lecturer)
            ? (User::where('email', 'dosen@arunika.test')->first() ?? User::where('role', $roleEnum)->first())
            : User::where('role', $roleEnum)->first();

        if (! $user) {
            return redirect()->back()->with('error', "User demo dengan role {$roleEnum->label()} tidak ditemukan.");
        }

        Auth::login($user);

        if ($user->campus_id) {
            session(['active_campus_id' => $user->campus_id]);
        } else {
            session()->forget('active_campus_id');
        }

        $targetRoute = $user->role === UserRole::Lecturer ? 'dosen.dashboard' : 'app.dashboard';

        return redirect()->route($targetRoute)->with('success', "Beralih peran sebagai {$user->name} ({$roleEnum->label()})");
    }

    /**
     * Store a demo request from the marketing website.
     */
    public function storeDemoRequest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'institution' => 'required|string|max:150',
            'phone' => 'nullable|string|max:30',
            'role' => 'nullable|string|max:60',
            'students_count' => 'nullable|string|max:50',
            'message' => 'nullable|string|max:1000',
        ]);

        DemoRequest::create($validated);

        return redirect()->back()->with('demo_submitted', true)->with('success', 'Terima kasih! Permintaan demo institusi Anda telah kami terima. Tim konsultan Arunika akan segera menghubungi Anda.');
    }

    /**
     * Log out the authenticated user.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('marketing.home');
    }
}
