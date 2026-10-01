<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Book;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@motazorrilla.com'],
            [
                'name' => 'Héctor Mota',
                'password' => Hash::make('admin'),
                'role' => 'admin',
                'status' => 'active',
                'book_limit' => -1,
            ]
        );

        // Keep guest trial books (user_id = null) unassigned for guest session access
    }
}
