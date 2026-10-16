<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\Campus;
use App\Models\User;
use App\Services\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AiCopilotController extends Controller
{
    /**
     * Helper to authenticate default student if session is empty (demo mode).
     */
    protected function resolveUser(): User
    {
        if (! Auth::check()) {
            $defaultUser = User::where('role', UserRole::Student)->first() ?? User::first();
            if ($defaultUser) {
                Auth::login($defaultUser);
                if ($defaultUser->campus_id) {
                    session(['active_campus_id' => $defaultUser->campus_id]);
                }
            }
        }

        return Auth::user();
    }

    /**
     * Display the standalone AI Copilot learning workspace.
     */
    public function index(Request $request, AiService $aiService): View
    {
        $user = $this->resolveUser();
        $campus = $user->campus ?? Campus::first();

        // Enrolled classes for subject context dropdown
        $enrolledClasses = AcademicClass::with(['course', 'lecturer'])
            ->whereHas('enrollments', fn ($q) => $q->where('user_id', $user->id))
            ->get();

        $defaultClass = $enrolledClasses->first();
        $defaultContext = $defaultClass ? $defaultClass->course->name : 'Pemrograman Berorientasi Objek & Desain Pola';

        $suggestions = $aiService->getSuggestions($defaultContext);

        return view('pages.app.copilot', [
            'user' => $user,
            'campus' => $campus,
            'currentRole' => $user->role,
            'enrolledClasses' => $enrolledClasses,
            'defaultContext' => $defaultContext,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Ask AI Copilot a question with academic context and Socratic guidance.
     */
    public function ask(Request $request, AiService $aiService): JsonResponse
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'socratic' => 'nullable|boolean',
            'context' => 'nullable|string|max:500',
            'mode' => 'nullable|string|in:chat,flashcard,quiz,summarize',
        ]);

        $user = Auth::user();
        $role = $user?->role?->value ?? 'student';
        $socratic = $request->boolean('socratic', true);
        $context = $request->input('context');
        $mode = $request->input('mode', 'chat');

        $result = $aiService->ask(
            $request->input('prompt'),
            $role,
            $socratic,
            $context,
            $mode,
            $user
        );

        return response()->json($result);
    }

    /**
     * Get prompt chips suggestions for a given learning context.
     */
    public function suggestions(Request $request, AiService $aiService): JsonResponse
    {
        $context = $request->input('context');
        $suggestions = $aiService->getSuggestions($context);

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions,
        ]);
    }
}
