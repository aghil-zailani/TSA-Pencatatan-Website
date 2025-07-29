<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'username' => 'supervisor',
                'email' => 'supervisor@gmail.com',
                'password' => Hash::make('supervisor123'),
                'role' => 'supervisor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'staff',
                'email' => 'staff@gmail.com',
                'password' => Hash::make('staff123'),
                'role' => 'staff_gudang',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
