<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PromoteAdmin extends Command
{
    protected $signature = 'banhawy:promote-admin
        {email : Email of the user to promote}
        {--create : Create the user if it does not exist}
        {--name= : Name (only used with --create)}
        {--phone= : Phone number (only used with --create)}
        {--password= : Password (only used with --create, random if omitted)}';

    protected $description = 'Promote a user to super-admin (or create one).';

    public function handle(): int
    {
        $email = $this->argument('email');
        $user  = User::where('email', $email)->first();

        if (! $user) {
            if (! $this->option('create')) {
                $this->error("User with email $email not found. Pass --create to create one.");
                return self::FAILURE;
            }

            $password = $this->option('password') ?: Str::random(14);
            $user = User::create([
                'name'     => $this->option('name') ?: explode('@', $email)[0],
                'email'    => $email,
                'password' => Hash::make($password),
                'role'     => 'admin',
            ]);

            $this->info("Admin user created.");
            $this->line("  email:    $email");
            $this->line("  password: $password");
            return self::SUCCESS;
        }

        if ($user->role === 'admin') {
            $this->info("User {$user->name} ($email) is already an admin.");
            return self::SUCCESS;
        }

        $previous = $user->role;
        $user->update(['role' => 'admin']);
        $this->info("Promoted {$user->name} ($email) from '$previous' to 'admin' ✓");
        return self::SUCCESS;
    }
}
