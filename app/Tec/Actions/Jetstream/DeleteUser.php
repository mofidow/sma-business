<?php

namespace App\Tec\Actions\Jetstream;

use App\Models\User;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * Delete the given user.
     */
    public function delete(User $user): void
    {
        if (auth()->id() === $user->id) {
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

        $user->deleteProfilePhoto();
        $user->tokens->each->delete();
        $user->delete();
    }
}
