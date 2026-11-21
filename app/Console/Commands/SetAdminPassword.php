<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetAdminPassword extends Command
{
    protected $signature = 'admin:set-password {password=Baralo00.} {--email=hector@motazorrilla.com}';

    protected $description = 'Set or reset admin password and ensure admin role';

    public function handle(): int
    {
        $password = (string) $this->argument('password');
        $email = (string) $this->option('email');

        $admin = User::where('email', $email)->first()
            ?? User::where('role', 'admin')->orderBy('id')->first();

        if (! $admin) {
            $admin = new User([
                'name' => 'Héctor Mota',
                'role' => 'admin',
                'status' => 'active',
                'book_limit' => -1,
            ]);
        }

        $admin->email = $email;
        $admin->password = Hash::make($password);
        $admin->role = 'admin';
        $admin->status = 'active';
        $admin->book_limit = -1;
        $admin->save();

        $this->info("Admin '{$email}' updated with role 'admin' and password set successfully.");

        return self::SUCCESS;
    }
}
