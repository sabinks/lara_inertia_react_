<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!User::whereEmail('superadmin@xl.com.au')->first()) {
            $user = User::create([
                'name' => 'Super Admin',
                'email' => "superadmin@xl.com.au",
                'password' => Hash::make('P@ss1234'),
                'is_active' => true,
                'email_verified_at' => Carbon::now(),
                'verification_token' => '',
                'remember_token' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $user->assignRole('Superadmin');
        }
    }
}
