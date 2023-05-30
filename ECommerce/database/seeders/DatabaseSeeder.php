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
            'superadmin_name' => 'mhammad1',
            'email' => 'mh1@gmail.com',
            'password' => Hash::make('0234567890'),
        ]);
        DB::table('admins')->insert([
          [  'company_name' => '1mhammad',
            'email' => 'mh2@gmail.com',
            'password' => Hash::make('1234567890'),
            'logo'=>'jdjfhfhfebfjfj.png',
            'Commercial_Record'=>'jjjfdkgkflfe.pdf',
            'phone_number'=>'9123456789']
            ,[
                'company_name' => 'leen2',
                'email' => 'leen2@gmail.com',
                'password' => Hash::make('1234567890'),
                'logo'=>'jdjfhfhfebfjfj.png',
                'Commercial_Record'=>'jjjfdkgkflfe.pdf',
                'phone_number'=>'8193456789'
            ]
        ]);
        // CREATE CONST COLORS
        DB::table('colors')->insert([
            ['color' => 'Red', 'hex' => '#FF0000'],
            ['color' => 'Green', 'hex' => '#00FF00'],
            ['color' => 'Blue', 'hex' => '#0000FF'],
            ['color' => 'white', 'hex' => '#FFFFFF'],
            ['color' => 'black', 'hex' => '#000000'],
        ]);
        // CREATE CONST SIZES
        DB::table('sizes')->insert([
            ['size' => 'S'],
            ['size' => 'M'],
            ['size' => 'L'],
            ['size' => 'XL'],
            ['size' => 'XXL'],
            ['size' => 'XXXL'],
        ]);
    }
}
