<?php

namespace App\Http\Controllers\Sma\Hr;

use Inertia\Inertia;
use App\Models\Sma\Hr\Claim;
use Illuminate\Http\Request;
use App\Models\Sma\Hr\Employee;
use App\Models\Sma\Setting\Store;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sma\Hr\ClaimRequest;

class ClaimController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        return Inertia::render('Sma/Hr/Claim/Index', [
            'stores'     => Store::get(['id as value', 'name as label']),
            'employees'  => Employee::with('user:id,name')->get(['id', 'user_id', 'store_id']),
            'pagination' => new Collection(
                Claim::filter($filters)->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClaimRequest $request)
    {
        $claim = Claim::create($request->validated());

        return back()
            ->with('data', $claim)
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Claim'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Claim $claim)
    {
        return $claim;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClaimRequest $request, Claim $claim)
    {
        $claim->update($request->validated());

        return back()->with('message', __('{model} has been successfully {action}.', [
            'model'  => __('Claim'),
            'action' => __('updated'),
        ]));
    }

    /**
     * Approve or reject a claim.
     */
    public function approve(Request $request, Claim $claim)
    {
        $request->validate(['status' => 'required|in:approved,rejected', 'notes' => 'nullable|string']);

        $claim->update([
            'status'      => $request->status,
            'notes'       => $request->notes,
            'approved_by' => auth()->id(),
        ]);

        return back()->with('message', __('{model} has been successfully {action}.', [
            'model'  => __('Claim'),
            'action' => __($request->status),
        ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Claim $claim)
    {
        if ($claim->{$claim->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Claim'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Claim'),
            'action' => __('deleted'),
        ]));
    }

    public function destroyMany(Request $request)
    {
        $count = 0;
        $failed = count($request->selection);
        foreach (Claim::whereIn('id', $request->selection)->get() as $record) {
            $record->{$request->force ? 'forceDelete' : 'delete'}() ? $count++ : '';
        }

        return back()->with('message', __('The task has completed, {count} deleted and {failed} failed.', ['count' => $count, 'failed' => $failed - $count]));
    }

    public function restore(Claim $claim)
    {
        $claim->restore();

        return back()->with('message', __('{model} has been successfully {action}.', [
            'model'  => __('Claim'),
            'action' => __('restored'),
        ]));
    }

    public function destroyPermanently(Claim $claim)
    {
        if ($claim->forceDelete()) {
            return to_route('claims.index')->with('message', __('{record} has been {action}.', ['record' => 'Claim', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }
}
