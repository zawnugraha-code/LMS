<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(
        protected AdminDashboardService $dashboardService
    ) {}

    /**
     * Tampilkan halaman Beranda Administrator LMS Arunika.
     */
    public function index(Request $request): View
    {
        $termId = $request->integer('term_id') ?: null;
        $data = $this->dashboardService->getDashboardData($termId);

        return view('pages.admin.dashboard', $data);
    }
}
