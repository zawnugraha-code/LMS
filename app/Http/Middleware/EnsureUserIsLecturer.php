<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsLecturer
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

        if (! in_array($user->role, [UserRole::Lecturer, UserRole::SuperAdmin, UserRole::CampusAdmin])) {
            abort(403, 'Akses terbatas: Anda tidak memiliki wewenang untuk membuka portal Dosen.');
        }

        return $next($request);
    }
}
