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
        abort_if(app()->isProduction(), 404);

        $allowedRoles = ['admin', 'lecturer', 'student'];
        abort_unless(in_array($role, $allowedRoles, true), 404);

        $roleEnum = UserRole::tryFrom($role);

        if (! $roleEnum) {
            abort(404);
        }

        if ($roleEnum === UserRole::Admin) {
            $user = User::where('email', 'admin@arunika.test')->first() ?? User::where('role', $roleEnum)->first();
        } elseif ($roleEnum === UserRole::Lecturer) {
            $user = User::where('email', 'dosen@arunika.test')->first() ?? User::where('role', $roleEnum)->first();
        } else {
            $user = User::where('role', $roleEnum)->first();
        }

        if (! $user) {
            return redirect()->back()->with('error', "User demo dengan role {$roleEnum->label()} tidak ditemukan.");
        }

        Auth::login($user);
        $user->forceFill(['last_login_at' => now()])->save();

        if ($user->campus_id) {
            session(['active_campus_id' => $user->campus_id]);
        } else {
            session()->forget('active_campus_id');
        }

        if ($user->role === UserRole::Admin || $user->role === UserRole::SuperAdmin) {
            $targetRoute = 'admin.dashboard';
        } elseif ($user->role === UserRole::Lecturer) {
            $targetRoute = 'dosen.dashboard';
        } else {
            $targetRoute = 'app.dashboard';
        }

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
