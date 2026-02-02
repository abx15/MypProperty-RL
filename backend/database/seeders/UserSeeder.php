<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get role IDs
        $adminRole = Role::where('name', 'admin')->first();
        $agentRole = Role::where('name', 'agent')->first();
        $userRole = Role::where('name', 'user')->first();

        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@myproperty.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'phone' => '+1234567890',
            'is_active' => true,
        ]);

        // Create agent users
        $agents = [
            [
                'name' => 'John Smith',
                'email' => 'john.agent@myproperty.com',
                'phone' => '+1234567891',
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.agent@myproperty.com',
                'phone' => '+1234567892',
            ],
            [
                'name' => 'Michael Brown',
                'email' => 'michael.agent@myproperty.com',
                'phone' => '+1234567893',
            ],
        ];

        foreach ($agents as $agent) {
            User::create([
                'name' => $agent['name'],
                'email' => $agent['email'],
                'password' => Hash::make('password'),
                'role_id' => $agentRole->id,
                'phone' => $agent['phone'],
                'is_active' => true,
            ]);
        }

        // Create regular users
        $users = [
            [
                'name' => 'Alice Wilson',
                'email' => 'alice.user@myproperty.com',
                'phone' => '+1234567894',
            ],
            [
                'name' => 'Bob Davis',
                'email' => 'bob.user@myproperty.com',
                'phone' => '+1234567895',
            ],
            [
                'name' => 'Carol Martinez',
                'email' => 'carol.user@myproperty.com',
                'phone' => '+1234567896',
            ],
            [
                'name' => 'David Lee',
                'email' => 'david.user@myproperty.com',
                'phone' => '+1234567897',
            ],
            [
                'name' => 'Emma Garcia',
                'email' => 'emma.user@myproperty.com',
                'phone' => '+1234567898',
            ],
        ];

        foreach ($users as $user) {
            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => Hash::make('password'),
                'role_id' => $userRole->id,
                'phone' => $user['phone'],
                'is_active' => true,
            ]);
        }
    }
}
