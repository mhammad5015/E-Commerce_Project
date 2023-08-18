<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // CREATE A SUPER ADMIN ACOUNT
        DB::table('super_admins')->insert([
            [
                'superadmin_name' => 'mhammad',
                'email' => 'mhammad@gmail.com',
                'password' => Hash::make('1234567890'),
            ],
            [
                'superadmin_name' => 'leen',
                'email' => 'leen@gmail.com',
                'password' => Hash::make('1234567890'),
            ]
        ]);
        // CREATE A ADMINs ACOUNT
        DB::table('admins')->insert([
            [
                'company_name' => 'addidas',
                'email' => 'addidas@gmail.com',
                'password' => Hash::make('1234567890'),
                'logo' => 'jdjfhfhfebfjfj.png',
                'description' => 'this is description',
                'phone_number' => '9123456789',
                'percentage' => 0.2,
            ], [
                'company_name' => 'nike',
                'email' => 'nike@gmail.com',
                'password' => Hash::make('1234567890'),
                'logo' => 'jdjfhfhfebfjfj.png',
                'description' => 'this is description',
                'phone_number' => '8193456789',
                'percentage' => 0.4,
            ]
        ]);
        // CREATE CONST COLORS
        DB::table('colors')->insert([
            ['color' => 'No Color', 'hex' => '#FFFFFF'],
            ['color' => 'Red', 'hex' => '#FF0000'],
            ['color' => 'Green', 'hex' => '#00FF00'],
            ['color' => 'Blue', 'hex' => '#0000FF'],
            ['color' => 'white', 'hex' => '#FFFFFF'],
            ['color' => 'black', 'hex' => '#000000'],
        ]);
        // CREATE CONST SIZES
        DB::table('sizes')->insert([
            ['size' => 'free size', 'type_id' => 1],
            ['size' => 'XS', 'type_id' => 1],
            ['size' => 'S', 'type_id' => 1],
            ['size' => 'M', 'type_id' => 1],
            ['size' => 'L', 'type_id' => 1],
            ['size' => 'XL', 'type_id' => 1],
            ['size' => 'XXL', 'type_id' => 1],
            ['size' => 'XXXL', 'type_id' => 1],
            ['size' => 'free size', 'type_id' => 2],
        ]);
        DB::table('types')->insert([
            ['type' => 'international'],
            ['type' => 'numeric'],
        ]);
    }
}
