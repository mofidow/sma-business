<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $role = Role::factory()->create([
            'account_id' => 1,
            'guard_name' => 'web',
            'name'       => 'Admin',
        ]);
        $admin = User::factory()->create([
            'account_id' => 1,
            'username'   => 'admin',
            'email'      => 'admin@sma.tec.sh',
        ]);
        $admin->roles()->sync($role);
    }
}
