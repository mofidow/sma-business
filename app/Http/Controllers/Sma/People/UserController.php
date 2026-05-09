<?php

namespace App\Http\Controllers\Sma\People;

use App\Models\Role;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Tec\Actions\SaveUser;
use App\Models\Sma\Setting\Store;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Models\Sma\Setting\CustomField;
use App\Http\Requests\Sma\People\UserRequest;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters');

        return Inertia::render('Sma/People/User/Index', [
            'roles'         => Role::get(),
            'custom_fields' => CustomField::ofModel('user')->get(),
            'stores'        => Store::active()->get(['id as value', 'name as label']),

            'pagination' => new Collection(User::with([
                'customer:id,company,name', 'supplier:id,company,name',
                'settings', 'roles:id,name', 'store:id,name', 'stores:id,name',
            ])->filter($filters)->latest()->orderBy('name')->paginate()->withQueryString()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        $user = (new SaveUser)->execute($request->validated());

        return redirect()->route('users.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('User'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return $user->loadMissing(['customer:id,company,name', 'supplier:id,company,name']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user)
    {
        $user = (new SaveUser)->execute($request->validated(), $user);

        return redirect()->route('users.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('User'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            abort(403, __('You cannot delete your own account while logged in.'));
        }

        if ($user->sales()->exists() || $user->purchases()->exists() || $user->repairOrders()->exists()) {
            abort(403, __('User cannot be deleted because they have associated records. Please reassign or delete those records first.'));
        }

        if ($user->hasRole('Super Admin')) {
            if (User::whereHas('roles', function ($query) {
                $query->where('name', 'Super Admin');
            })->count() <= 1) {
                abort(403, __('At least one Super Admin user is required. Please assign the Super Admin role to another user before deleting this account.'));
            }
        }

        if ($user->{$user->deleted_at ? 'forceDelete' : 'delete'}()) {
            $user->deleteProfilePhoto();
            $user->tokens->each->delete();

            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('User'),
                'action' => __('deleted'),
            ]));
        }

        return to_route('users.index')->with('error', __('{model} cannot be {action}.', [
            'model'  => __('User'),
            'action' => __('deleted'),
        ]));
    }

    public function destroyMany(Request $request)
    {
        $count = 0;
        $failed = count($request->selection);
        foreach (User::whereIn('id', $request->selection)->get() as $record) {
            $record->{$request->force ? 'forceDelete' : 'delete'}() ? $count++ : '';
        }

        return back()->with('message', __('The task has completed, {count} deleted and {failed} failed.', ['count' => $count, 'failed' => $failed - $count]));
    }

    public function destroyPermanently(User $user)
    {
        if ($user->forceDelete()) {
            return to_route('users.index')->with('message', __('{record} has been {action}.', ['record' => 'User', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function impersonate(User $user)
    {
        if ($user->hasRole('Super Admin')) {
            return back()->with('error', __('You cannot impersonate a Super Admin user.'));
        }

        $user->startImpersonating();

        return to_route('dashboard')->with('message', __('You are now impersonating as {x}.', ['x' => $user->name]));
    }
}
