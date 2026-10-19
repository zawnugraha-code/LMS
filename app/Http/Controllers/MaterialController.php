<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Campus;
use App\Models\Material;
use App\Models\User;
use App\Services\AcademicTermService;
use App\Services\MaterialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MaterialController extends Controller
{
    /**
     * Display the learning materials (Materi Pembelajaran) page.
     */
    public function index(
        Request $request,
        AcademicTermService $termService,
        MaterialService $materialService
    ): View {
        $user = Auth::user();
        $campus = $user?->campus ?? Campus::first();
        $activeTerm = $termService->getActiveTerm();

        // 1. Resolve enrolled classes for term
        $enrolledClasses = $materialService->getEnrolledClasses($user, $activeTerm);

        // 2. Resolve active class
        $activeClass = $materialService->resolveActiveClass(
            $user,
            $activeTerm,
            $request->query('course'),
            $request->query('class')
        );

        if (! $activeClass && $enrolledClasses->isNotEmpty()) {
            $activeClass = $enrolledClasses->first();
        }

        // 3. Extract filters
        $filters = [
            'type' => $request->query('type', 'all'),
            'status' => $request->query('status', 'all'),
            'query' => $request->query('q', ''),
        ];

        // 4. Calculate summary & chapters
        $summary = $activeClass ? $materialService->getSummary($user, $activeClass) : [
            'total_materials' => 0,
            'completed_materials' => 0,
            'in_progress_materials' => 0,
            'locked_materials' => 0,
            'progress_percentage' => 0.0,
        ];

        $chapters = $activeClass ? $materialService->getChapters($user, $activeClass, $filters) : [];
        $recentActivities = $materialService->getRecentActivities($user, $activeClass);

        return view('pages.app.materials', [
            'user' => $user,
            'campus' => $campus,
            'currentRole' => $user->role,
            'activeTerm' => $activeTerm,
            'enrolledClasses' => $enrolledClasses,
            'selectedClass' => $activeClass,
            'summary' => $summary,
            'chapters' => $chapters,
            'recentActivities' => $recentActivities,
            'filters' => $filters,
        ]);
    }

    /**
     * Display the full material viewer page (Sub-halaman sesuai docs/pages-without-design.md).
     */
    public function view(Material $material): View
    {
        $user = Auth::user();

        $meeting = $material->meeting;
        $class = $meeting?->academicClass;

        Gate::authorize('view', $material);

        if ($class && $class->isLocked()) {
            abort(403, 'Mata kuliah ini belum dimulai atau masih berstatus Akan Datang.');
        }

        $progress = $material->progress()->where('user_id', $user->id)->first();

        // Prev and Next materials in the same class
        $allMaterials = Material::whereHas('meeting', fn ($q) => $q->where('class_id', $class?->id))
            ->orderBy('id')
            ->get();

        $currentIndex = $allMaterials->search(fn ($m) => $m->id === $material->id);
        $prevMaterial = ($currentIndex !== false && $currentIndex > 0) ? $allMaterials->get($currentIndex - 1) : null;
        $nextMaterial = ($currentIndex !== false && $currentIndex < $allMaterials->count() - 1) ? $allMaterials->get($currentIndex + 1) : null;

        return view('pages.app.material-viewer', [
            'user' => $user,
            'campus' => $user?->campus ?? Campus::first(),
            'currentRole' => $user->role,
            'material' => $material,
            'meeting' => $meeting,
            'class' => $class,
            'course' => $class?->course,
            'lecturer' => $class?->lecturer,
            'progress' => $progress,
            'prevMaterial' => $prevMaterial,
            'nextMaterial' => $nextMaterial,
        ]);
    }

    /**
     * Update progress of a material via AJAX / form POST.
     */
    public function updateProgress(
        Request $request,
        Material $material,
        MaterialService $materialService
    ): JsonResponse {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        Gate::authorize('view', $material);

        $class = $material->meeting?->academicClass ?? $material->academicClass;
        if ($class && ($class->isLocked() || $class->isFinished())) {
            return response()->json(['error' => 'Kelas terkunci atau telah selesai'], 403);
        }

        if ($user->role === UserRole::Student) {
            $isEnrolledActive = $class?->enrollments()->where('user_id', $user->id)->aktif()->exists();
            if (! $isEnrolledActive) {
                return response()->json(['error' => 'Aksi ini hanya dapat dilakukan oleh mahasiswa aktif'], 403);
            }
        }

        $watchPercentage = (int) $request->input('watch_percentage', 100);
        $result = $materialService->updateProgress($user, $material, $watchPercentage);

        return response()->json(array_merge(['success' => true], $result));
    }
}
