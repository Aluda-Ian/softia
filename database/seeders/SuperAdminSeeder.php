<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates (or updates) the super admin from SEED_ADMIN_* values in .env,
 * so credentials never live in the codebase.
 */
class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = config('deploy.seed_admin');

        if (blank($admin['email']) || blank($admin['password'])) {
            $this->command?->warn('SEED_ADMIN_EMAIL / SEED_ADMIN_PASSWORD not set; skipping super admin.');

            return;
        }

        $user = User::firstOrNew(['email' => $admin['email']]);

        $user->forceFill([
            'name' => $admin['name'] ?: 'Administrator',
            'password' => $admin['password'],
            'is_admin' => true,
            'is_super_admin' => true,
            'email_verified_at' => now(),
        ])->save();

        $this->command?->info("Super admin ready: {$user->email}");
    }
}
