<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('marketing.home');
        }

        if (! in_array($user->role, [UserRole::Admin, UserRole::SuperAdmin, UserRole::CampusAdmin], true)) {
            abort(403, 'Akses terbatas: Anda tidak memiliki wewenang untuk membuka portal Administrator.');
        }

        if (isset($user->is_active) && ! $user->is_active) {
            auth()->logout();
            return redirect()->route('marketing.home')->with('error', 'Akun administrator Anda sedang dinonaktifkan.');
        }

        return $next($request);
    }
}
