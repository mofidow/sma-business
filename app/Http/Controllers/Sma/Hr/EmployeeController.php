<?php

namespace App\Http\Controllers\Sma\Hr;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Sma\Hr\Employee;
use App\Models\Sma\Setting\Store;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sma\Hr\EmployeeRequest;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        return Inertia::render('Sma/Hr/Employee/Index', [
            'stores'     => Store::get(['id as value', 'name as label']),
            'users'      => User::employee()->get(['id as value', 'name as label']),
            'pagination' => new Collection(
                Employee::filter($filters)->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EmployeeRequest $request)
    {
        $employee = Employee::create($request->validated());

        return back()
            ->with('data', $employee)
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Employee'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        return $employee;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EmployeeRequest $request, Employee $employee)
    {
        $employee->update($request->validated());

        return back()->with('message', __('{model} has been successfully {action}.', [
            'model'  => __('Employee'),
            'action' => __('updated'),
        ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        if ($employee->{$employee->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Employee'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Employee'),
            'action' => __('deleted'),
        ]));
    }

    public function destroyMany(Request $request)
    {
        $count = 0;
        $failed = count($request->selection);
        foreach (Employee::whereIn('id', $request->selection)->get() as $record) {
            $record->{$request->force ? 'forceDelete' : 'delete'}() ? $count++ : '';
        }

        return back()->with('message', __('The task has completed, {count} deleted and {failed} failed.', ['count' => $count, 'failed' => $failed - $count]));
    }

    public function restore(Employee $employee)
    {
        $employee->restore();

        return back()->with('message', __('{model} has been successfully {action}.', [
            'model'  => __('Employee'),
            'action' => __('restored'),
        ]));
    }

    public function destroyPermanently(Employee $employee)
    {
        if ($employee->forceDelete()) {
            return to_route('employees.index')->with('message', __('{record} has been {action}.', ['record' => 'Employee', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }
}
