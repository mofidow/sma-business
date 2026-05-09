<?php

namespace App\Http\Controllers\Sma\Hr;

use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Sma\Hr\Employee;
use App\Models\Sma\Hr\Attendance;
use App\Models\Sma\Setting\Store;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sma\Hr\AttendanceRequest;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        return Inertia::render('Sma/Hr/Attendance/Index', [
            'stores'     => Store::get(['id as value', 'name as label']),
            'employees'  => Employee::with('user:id,name')->get(['id', 'user_id', 'store_id']),
            'pagination' => new Collection(
                Attendance::filter($filters)->latest('date')->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AttendanceRequest $request)
    {
        $data = $request->validated();

        if (($data['clock_in'] ?? null) && ($data['clock_out'] ?? null)) {
            $data['hours_worked'] = Carbon::parse($data['clock_in'])->diffInHours(Carbon::parse($data['clock_out']));
        }

        $attendance = Attendance::create($data);

        return back()
            ->with('data', $attendance)
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Attendance'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance)
    {
        return $attendance;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AttendanceRequest $request, Attendance $attendance)
    {
        $data = $request->validated();

        if (($data['clock_in'] ?? null) && ($data['clock_out'] ?? null)) {
            $data['hours_worked'] = Carbon::parse($data['clock_out'])->diffInMinutes(Carbon::parse($data['clock_in'])) / 60;
        }

        $attendance->update($data);

        return back()->with('message', __('{model} has been successfully {action}.', [
            'model'  => __('Attendance'),
            'action' => __('updated'),
        ]));
    }

    /**
     * Bulk store attendance for a specific date (monthly sheet).
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'store_id'              => 'required|exists:stores,id',
            'date'                  => 'required|date',
            'records'               => 'required|array',
            'records.*.employee_id' => 'required|exists:employees,id',
            'records.*.status'      => 'required|in:present,absent,late,half_day,holiday,on_leave',
            'records.*.clock_in'    => 'nullable|date_format:H:i',
            'records.*.clock_out'   => 'nullable|date_format:H:i',
            'records.*.note'        => 'nullable|string',
        ]);

        foreach ($request->records as $record) {
            $hoursWorked = null;

            if (! empty($record['clock_in']) && ! empty($record['clock_out'])) {
                $hoursWorked = Carbon::parse($record['clock_out'])->diffInMinutes(Carbon::parse($record['clock_in'])) / 60;
            }

            Attendance::updateOrCreate(
                ['employee_id' => $record['employee_id'], 'date' => $request->date],
                [
                    'store_id'     => $request->store_id,
                    'clock_in'     => $record['clock_in'] ?? null,
                    'clock_out'    => $record['clock_out'] ?? null,
                    'hours_worked' => $hoursWorked,
                    'status'       => $record['status'],
                    'note'         => $record['note'] ?? null,
                ]
            );
        }

        return back()->with('message', __('Attendance has been successfully recorded.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance)
    {
        if ($attendance->{$attendance->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Attendance'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Attendance'),
            'action' => __('deleted'),
        ]));
    }

    public function destroyMany(Request $request)
    {
        $count = 0;
        $failed = count($request->selection);
        foreach (Attendance::whereIn('id', $request->selection)->get() as $record) {
            $record->{$request->force ? 'forceDelete' : 'delete'}() ? $count++ : '';
        }

        return back()->with('message', __('The task has completed, {count} deleted and {failed} failed.', ['count' => $count, 'failed' => $failed - $count]));
    }

    public function restore(Attendance $attendance)
    {
        $attendance->restore();

        return back()->with('message', __('{model} has been successfully {action}.', [
            'model'  => __('Attendance'),
            'action' => __('restored'),
        ]));
    }

    public function destroyPermanently(Attendance $attendance)
    {
        if ($attendance->forceDelete()) {
            return to_route('attendances.index')->with('message', __('{record} has been {action}.', ['record' => 'Attendance', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }
}
