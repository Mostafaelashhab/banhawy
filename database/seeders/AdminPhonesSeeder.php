<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Guarantees the two owner phone numbers we use as the project's admins
 * are always promoted to admin role — so push notifications always reach
 * both of them. Idempotent: safe to run on every db:seed.
 */
class AdminPhonesSeeder extends Seeder
{
    public const ADMIN_PHONES = ['01022345504', '01550047838'];

    public function run(): void
    {
        foreach (self::ADMIN_PHONES as $phone) {
            $user = User::where('phone', $phone)->first();

            if (! $user) {
                $user = User::create([
                    'name'     => 'Admin '.$phone,
                    'email'    => 'admin-'.$phone.'@banhawy.local',
                    'phone'    => $phone,
                    'password' => Hash::make(Str::random(20)),
                    'role'     => 'admin',
                ]);
                $this->command?->info("Created admin user for phone $phone");
            } elseif ($user->role !== 'admin') {
                $user->update(['role' => 'admin']);
                $this->command?->info("Promoted $phone (id={$user->id}) to admin");
            }
        }
    }
}
