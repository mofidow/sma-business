<?php

namespace App\Http\Controllers\Sma\Report;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Activity;
use Illuminate\Http\Request;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __invoke(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        return Inertia::render('Sma/Report/Activity', [
            'users' => User::employee()->get(['id as value', 'name as label']),

            'pagination' => new Collection(
                Activity::with(['subject', 'causer:id,name'])
                    ->filter($filters)->latest()->paginate()->withQueryString()
            ),
        ]);
    }
}
