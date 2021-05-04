<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = now();

        DB::table('users')->insert([[
            'name'              => 'user',
            'email'             => 'user@mail.com',
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'created_at'        => $now,
            'updated_at'        => $now,
        ], [
            'name'              => 'user2',
            'email'             => 'user2@mail.com',
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'created_at'        => $now,
            'updated_at'        => $now,
        ]]);
    }
}
