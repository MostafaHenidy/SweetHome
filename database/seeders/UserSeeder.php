<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Laravel\Jetstream\Features;

class UserSeeder extends Seeder
{
    /**
     * Storefront / local admin (Jetstream teams + password cast).
     *
     * After changing this file, run: php artisan db:seed --class=UserSeeder
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@sweethome.local'],
            [
                'name' => 'Admin User',
                // User model uses 'password' => 'hashed' cast — pass plain text (not bcrypt).
                'password' => 'admin123',
                'phone' => '+1234567890',
                'email_verified_at' => now(),
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        if (Features::hasTeamFeatures() && ! $user->ownedTeams()->exists()) {
            DB::transaction(function () use ($user): void {
                $user->ownedTeams()->save(Team::forceCreate([
                    'user_id' => $user->id,
                    'name' => explode(' ', $user->name, 2)[0]."'s Team",
                    'personal_team' => true,
                ]));
            });
            $user->refresh();
            $team = $user->ownedTeams()->first();
            if ($team && $user->current_team_id === null) {
                $user->forceFill(['current_team_id' => $team->id])->save();
            }
        }

        if ($this->command) {
            $this->command->info('Login: admin@sweethome.local / admin123');
            $this->command->info('(Also from UserManagementSeeder: admin@example.com / password)');
        }
    }
}
