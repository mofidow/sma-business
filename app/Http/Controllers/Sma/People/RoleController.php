<?php

namespace App\Http\Controllers\Sma\People;

use App\Models\Role;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sma\People\RoleRequest;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Sma/People/Role/Index', [
            'roles' => new Collection(Role::with('permissions:id,name')->latest()->orderBy('name')->paginate()->withQueryString()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleRequest $request)
    {
        $role = Role::create($request->validated());
        $role->savePermissions($request->input('permissions') ?? []);

        return redirect()->route('roles.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Role'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return $role;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RoleRequest $request, Role $role)
    {
        if (in_array($role->name, ['Super Admin'])) {
            return to_route('roles.index')->with('error', __('Role can not be modified.'));
        }

        $form = $request->validated();
        if (in_array($role->name, ['Customer', 'Supplier'])) {
            $form['name'] = $role->name;
        }

        $role->update($form);
        $role->savePermissions($request->input('permissions') ?? []);

        return redirect()->route('roles.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Role'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        if ($role->{$role->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Role'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Role'),
            'action' => __('deleted'),
        ]));
    }

    public function destroyMany(Request $request)
    {
        $count = 0;
        $failed = count($request->selection);
        foreach (Role::whereIn('id', $request->selection)->get() as $record) {
            $record->{$request->force ? 'forceDelete' : 'delete'}() ? $count++ : '';
        }

        return back()->with('message', __('The task has completed, {count} deleted and {failed} failed.', ['count' => $count, 'failed' => $failed - $count]));
    }

    public function destroyPermanently(Role $role)
    {
        if ($role->forceDelete()) {
            return to_route('roles.index')->with('message', __('{record} has been {action}.', ['record' => 'Role', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }
}
