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
                'name' => 'supervisor',
                'email' => 'supervisor@gmail.com',
                'password' => Hash::make('supervisor123'),
                'role' => 'supervisor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'staff',
                'email' => 'staff@gmail.com',
                'password' => Hash::make('staff123'),
                'role' => 'staff_gudang',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '1',
                'email' => 'supervisor_umum@gmail.com',
                'password' => Hash::make('123123'),
                'role' => 'supervisor_umum',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'inspektor',
                'email' => 'inspektor@gmail.com',
                'password' => Hash::make('123456'),
                'role' => 'inspektor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
