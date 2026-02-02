<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'permissions' => [
                    'manage_users',
                    'manage_properties',
                    'manage_locations',
                    'manage_enquiries',
                    'view_analytics',
                    'manage_system',
                ],
            ],
            [
                'name' => 'agent',
                'permissions' => [
                    'manage_own_properties',
                    'view_own_analytics',
                    'respond_to_enquiries',
                    'use_ai_features',
                ],
            ],
            [
                'name' => 'user',
                'permissions' => [
                    'view_properties',
                    'create_enquiries',
                    'manage_wishlist',
                    'manage_profile',
                ],
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
