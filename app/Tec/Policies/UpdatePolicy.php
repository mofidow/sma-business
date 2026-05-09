<?php

namespace App\Tec\Policies;

use App\Models\User;

class UpdatePolicy
{
    public function update(User $user, $model): bool
    {
        return $user->id === $model->user_id || $user->can('update-all');
    }
}
