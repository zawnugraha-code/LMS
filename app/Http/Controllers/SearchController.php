<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function __construct(
        protected SearchService $searchService
    ) {}

    /**
     * Search across courses, materials, assignments, and discussions.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->query('q', '');
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $results = $this->searchService->search($query, $user);

        return response()->json($results);
    }
}
