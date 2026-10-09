<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@motazorrilla.com'],
            [
                'name' => 'Héctor Mota',
                'password' => Hash::make('Password123!'),
                'role' => 'admin',
                'status' => 'active',
                'book_limit' => -1,
                'permissions' => [
                    'platform.index' => true,
                    'platform.systems.roles' => true,
                    'platform.systems.users' => true,
                    'platform.systems.attachment' => true,
                ],
            ]
        );

        if ($admin) {
            $admin->update([
                'role' => 'admin',
                'permissions' => array_merge($admin->permissions ?? [], [
                    'platform.index' => true,
                    'platform.systems.roles' => true,
                    'platform.systems.users' => true,
                ]),
            ]);
        }
    }
}
