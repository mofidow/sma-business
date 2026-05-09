<?php

namespace App\Http\Controllers\Sma\Hr;

use Inertia\Inertia;
use App\Models\Sma\Hr\Leave;
use Illuminate\Http\Request;
use App\Models\Sma\Hr\Employee;
use App\Models\Sma\Hr\LeaveType;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sma\Hr\LeaveRequest;

class LeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        return Inertia::render('Sma/Hr/Leave/Index', [
            'employees'   => Employee::with('user:id,name')->get(['id', 'user_id']),
            'leave_types' => LeaveType::get(['id as value', 'name as label']),
            'pagination'  => new Collection(
                Leave::filter($filters)->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LeaveRequest $request)
    {
        $leave = Leave::create($request->validated());

        return back()
            ->with('data', $leave)
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Leave'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Leave $leave)
    {
        return $leave;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LeaveRequest $request, Leave $leave)
    {
        $leave->update($request->validated());

        return back()->with('message', __('{model} has been successfully {action}.', [
            'model'  => __('Leave'),
            'action' => __('updated'),
        ]));
    }

    /**
     * Approve or reject a leave request.
     */
    public function approve(Request $request, Leave $leave)
    {
        $request->validate(['status' => 'required|in:approved,rejected', 'notes' => 'nullable|string']);

        $leave->update([
            'status'      => $request->status,
            'notes'       => $request->notes,
            'approved_by' => auth()->id(),
        ]);

        return back()->with('message', __('{model} has been successfully {action}.', [
            'model'  => __('Leave'),
            'action' => __($request->status),
        ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Leave $leave)
    {
        if ($leave->{$leave->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Leave'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Leave'),
            'action' => __('deleted'),
        ]));
    }

    public function destroyMany(Request $request)
    {
        $count = 0;
        $failed = count($request->selection);
        foreach (Leave::whereIn('id', $request->selection)->get() as $record) {
            $record->{$request->force ? 'forceDelete' : 'delete'}() ? $count++ : '';
        }

        return back()->with('message', __('The task has completed, {count} deleted and {failed} failed.', ['count' => $count, 'failed' => $failed - $count]));
    }

    public function restore(Leave $leave)
    {
        $leave->restore();

        return back()->with('message', __('{model} has been successfully {action}.', [
            'model'  => __('Leave'),
            'action' => __('restored'),
        ]));
    }

    public function destroyPermanently(Leave $leave)
    {
        if ($leave->forceDelete()) {
            return to_route('leaves.index')->with('message', __('{record} has been {action}.', ['record' => 'Leave', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }
}
