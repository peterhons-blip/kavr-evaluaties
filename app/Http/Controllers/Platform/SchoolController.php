<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\View\View;

class SchoolController extends Controller
{
    /**
     * Overzicht van alle aangesloten scholen.
     */
    public function index(): View
    {
        // Haal alle scholen alfabetisch op.
        // Laad ook het aantal vestigingsplaatsen.
        $schools = School::query()
            ->withCount('locations')
            ->orderBy('name')
            ->paginate(15);

        return view('platform.schools.index', compact('schools'));
    }
}
