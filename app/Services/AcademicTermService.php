<?php

namespace App\Services;

use App\Models\Term;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AcademicTermService
{
    /**
     * Get the active or selected term.
     */
    public function getActiveTerm(): ?Term
    {
        $termId = Session::get('active_term_id');

        if (! $termId && Auth::check()) {
            $pref = Auth::user()->preference;
            $termId = $pref?->active_term_id;
        }

        if ($termId) {
            $term = Term::find($termId);
            if ($term) {
                return $term;
            }
        }

        // Default to active term in database (or latest)
        $defaultTerm = Term::where('is_active', true)->first() ?? Term::latest()->first();

        if ($defaultTerm) {
            Session::put('active_term_id', $defaultTerm->id);
        }

        return $defaultTerm;
    }

    /**
     * Set the active term in session and user preference.
     */
    public function setActiveTerm(int $termId): ?Term
    {
        $term = Term::find($termId);

        if ($term) {
            Session::put('active_term_id', $term->id);

            if (Auth::check()) {
                $pref = Auth::user()->preference()->firstOrCreate(['user_id' => Auth::id()]);
                $pref->update(['active_term_id' => $term->id]);
            }
        }

        return $term;
    }

    /**
     * Get all available academic terms.
     */
    public function getAllTerms(): Collection
    {
        return Term::orderBy('start_date', 'desc')->get();
    }
}
