<?php

namespace App\Http\Controllers;

use App\Services\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiCopilotController extends Controller
{
    public function ask(Request $request, AiService $aiService): JsonResponse
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'socratic' => 'nullable|boolean',
            'context' => 'nullable|string|max:500',
        ]);

        $role = Auth::user()?->role?->value ?? 'student';
        $socratic = $request->boolean('socratic', true);
        $context = $request->input('context');

        $result = $aiService->ask($request->input('prompt'), $role, $socratic, $context);

        return response()->json($result);
    }
}
