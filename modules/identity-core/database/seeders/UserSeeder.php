<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Role as SpatieRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use function setPermissionsTeamId;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $configuredPassword = (string) config('app.seed_admin_password');
        $adminUser = User::firstOrNew(['email' => 'admin@example.com']);
        $generatedPassword = null;

        if (! $adminUser->exists) {
            $generatedPassword = $configuredPassword === '' ? Str::random(64) : null;
            $adminUser->name = 'Admin User';
            $adminUser->email_verified_at = now();
            $adminUser->password = Hash::make($generatedPassword ?? $configuredPassword);
            $adminUser->save();
        } elseif ($configuredPassword !== '' && ! Hash::check($configuredPassword, $adminUser->password)) {
            $adminUser->password = Hash::make($configuredPassword);
            $adminUser->save();
        }

        $team = Team::firstOrFail();
        $adminUser->teams()->syncWithoutDetaching([$team->id]);
        $adminUser->forceFill(['current_team_id' => $team->id])->save();

        $role = SpatieRole::where('name', Role::SuperAdmin->value)->firstOrFail();
        setPermissionsTeamId(null);
        $adminUser->assignRole($role);

        if (app()->environment('local') && $generatedPassword !== null) {
            $this->command?->info("Admin password: {$generatedPassword}");
        }
    }
}
