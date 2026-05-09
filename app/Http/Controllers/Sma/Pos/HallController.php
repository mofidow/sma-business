<?php

namespace App\Http\Controllers\Sma\Pos;

use Inertia\Inertia;
use App\Models\Sma\Pos\Hall;
use Illuminate\Http\Request;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sma\Pos\HallRequest;

class HallController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters');

        if (! ($filters['store_id'] ?? null) && session('selected_store_id', null)) {
            $filters['store_id'] = session('selected_store_id');
        }

        return Inertia::render('Sma/Pos/Hall/Index', [
            'pagination' => new Collection(
                Hall::withCount('tables')
                    ->filter($filters)
                    ->latest()->orderBy('sort_order')
                    ->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HallRequest $request)
    {
        Hall::create($request->validated());

        return redirect()->route('pos.halls.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Hall'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Hall $hall)
    {
        return $hall->load('tables');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HallRequest $request, Hall $hall)
    {
        $hall->update($request->validated());

        return redirect()->route('pos.halls.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Hall'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hall $hall)
    {
        if ($hall->{$hall->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Hall'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}. The record is being used for relationships.', [
            'model'  => __('Hall'),
            'action' => __('deleted'),
        ]));
    }
}
