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
                'id' => '220101',
                'username' => 'supervisor',
                'role' => 'supervisor',
                'email' => 'supervisor@gmail.com',
                'password' => Hash::make('123'),                
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '220201',
                'username' => 'staff',
                'role' => 'staff_gudang',
                'email' => 'staff@gmail.com',
                'password' => Hash::make('123'),            
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '220301',
                'username' => '1',
                'role' => 'supervisor_umum',
                'email' => 'supervisor_umum@gmail.com',
                'password' => Hash::make('123123'),                
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '220401',
                'username' => 'inspektor',
                'role' => 'inspektor',
                'email' => 'inspektor@gmail.com',
                'password' => Hash::make('123456'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
