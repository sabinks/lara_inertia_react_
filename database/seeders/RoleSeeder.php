<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ['Superadmin', 'Client'];

        foreach ($roles as $key => $role) {
            if (!Role::whereName($role)->first()) {
                Role::create([
                    'name' => $role,
                    'guard_name' => 'api'
                ]);
            }
        }
    }
}
