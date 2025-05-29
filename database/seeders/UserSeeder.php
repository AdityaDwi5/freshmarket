<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $kasirRole = Role::where('name', 'kasir')->first();
        $customerRole = Role::where('name', 'customer')->first();

        User::create([
            'name' => 'admin1',
            'email' => 'admin1@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
        ]);
        
        User::create([
            'name' => 'kasir1',
            'email' => 'kasir1@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => $kasirRole->id,
        ]);
        
        User::create([
            'name' => 'kasir2',
            'email' => 'kasir2@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => $kasirRole->id,
        ]);
        
        User::create([
            'name' => 'customer1',
            'email' => 'customer1@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => $customerRole->id,
        ]);
        
        User::create([
            'name' => 'customer2',
            'email' => 'customer2@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => $customerRole->id,
        ]);
        
        User::create([
            'name' => 'customer3',
            'email' => 'customer3@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => $customerRole->id,
        ]);
    }
}
