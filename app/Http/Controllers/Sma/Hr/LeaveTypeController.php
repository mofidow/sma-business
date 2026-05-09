<?php

namespace App\Http\Controllers\Sma\Hr;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Sma\Hr\LeaveType;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sma\Hr\LeaveTypeRequest;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        return Inertia::render('Sma/Hr/LeaveType/Index', [
            'pagination' => new Collection(
                LeaveType::filter($filters)->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LeaveTypeRequest $request)
    {
        $leaveType = LeaveType::create($request->validated());

        return back()
            ->with('data', $leaveType)
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Leave Type'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveType $leaveType)
    {
        return $leaveType;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LeaveTypeRequest $request, LeaveType $leaveType)
    {
        $leaveType->update($request->validated());

        return back()->with('message', __('{model} has been successfully {action}.', [
            'model'  => __('Leave Type'),
            'action' => __('updated'),
        ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveType $leaveType)
    {
        if ($leaveType->{$leaveType->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Leave Type'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Leave Type'),
            'action' => __('deleted'),
        ]));
    }

    public function destroyMany(Request $request)
    {
        $count = 0;
        $failed = count($request->selection);
        foreach (LeaveType::whereIn('id', $request->selection)->get() as $record) {
            $record->{$request->force ? 'forceDelete' : 'delete'}() ? $count++ : '';
        }

        return back()->with('message', __('The task has completed, {count} deleted and {failed} failed.', ['count' => $count, 'failed' => $failed - $count]));
    }

    public function restore(LeaveType $leaveType)
    {
        $leaveType->restore();

        return back()->with('message', __('{model} has been successfully {action}.', [
            'model'  => __('Leave Type'),
            'action' => __('restored'),
        ]));
    }

    public function destroyPermanently(LeaveType $leaveType)
    {
        if ($leaveType->forceDelete()) {
            return to_route('leave-types.index')->with('message', __('{record} has been {action}.', ['record' => 'Leave Type', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }
}
