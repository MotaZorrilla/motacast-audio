<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetAdminPassword extends Command
{
    protected $signature = 'admin:set-password {password=admin123}';
    protected $description = 'Set or reset admin password and ensure admin role';

    public function handle(): int
    {
        $password = $this->argument('password');
        $admin = User::firstOrCreate(
            ['email' => 'admin@motazorrilla.com'],
            [
                'name' => 'Héctor Mota',
                'role' => 'admin',
                'status' => 'active',
                'book_limit' => -1,
            ]
        );

        $admin->password = Hash::make($password);
        $admin->role = 'admin';
        $admin->status = 'active';
        $admin->book_limit = -1;
        $admin->save();

        $this->info("Admin 'admin@motazorrilla.com' updated with role 'admin' and password set successfully.");
        return self::SUCCESS;
    }
}
