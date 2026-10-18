<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\Course;
use App\Models\Term;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSearchController extends Controller
{
    /**
     * Cari entitas sistem secara global dari topbar (Ctrl + K).
     */
    public function search(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        if (strlen($q) < 2) {
            return view('pages.admin.cari', [
                'query' => $q,
                'totalResults' => 0,
                'courses' => collect(),
                'classes' => collect(),
                'terms' => collect(),
                'lecturers' => collect(),
                'students' => collect(),
            ]);
        }

        $courses = Course::query()
            ->with(['curriculum.program'])
            ->where('name', 'like', "%{$q}%")
            ->orWhere('code', 'like', "%{$q}%")
            ->limit(10)
            ->get();

        $classes = AcademicClass::query()
            ->with(['course', 'term', 'lecturer'])
            ->where('section', 'like', "%{$q}%")
            ->orWhere('room', 'like', "%{$q}%")
            ->orWhereHas('course', function ($cq) use ($q) {
                $cq->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            })
            ->orWhereHas('lecturer', function ($lq) use ($q) {
                $lq->where('name', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get();

        $terms = Term::query()
            ->where('name', 'like', "%{$q}%")
            ->orWhere('academic_year', 'like', "%{$q}%")
            ->limit(5)
            ->get();

        $lecturers = User::query()
            ->where('role', UserRole::Lecturer)
            ->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('identifier', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get();

        $students = User::query()
            ->where('role', UserRole::Student)
            ->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('identifier', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get();

        $totalResults = $courses->count() + $classes->count() + $terms->count() + $lecturers->count() + $students->count();

        return view('pages.admin.cari', [
            'query' => $q,
            'totalResults' => $totalResults,
            'courses' => $courses,
            'classes' => $classes,
            'terms' => $terms,
            'lecturers' => $lecturers,
            'students' => $students,
        ]);
    }
}
