<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin User', 'username' => 'admin', 'email' => 'admin@test.com', 'role' => 'admin'],
            ['name' => 'Owner User', 'username' => 'owner', 'email' => 'owner@test.com', 'role' => 'owner'],
            ['name' => 'Tenant User', 'username' => 'tenant', 'email' => 'tenant@test.com', 'role' => 'tenant'],
        ];

        foreach ($users as $userData) {
            $roleName = $userData['role'];

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name'     => $userData['name'],
                    'username' => $userData['username'],
                    'password' => Hash::make('Password123'),
                ]
            );

            $roleId = Role::where('name', $roleName)->value('id');
            $user->roles()->sync([$roleId]);
        }
    }
}
