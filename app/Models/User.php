<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasDefaultTenant;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use JoelButcher\Socialstream\HasConnectedAccounts;
use JoelButcher\Socialstream\SetsProfilePhotoFromUrl;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Liberu\Analytics\Core\Models\DashboardWidget;
use Liberu\CRM\TerritoriesAndOwnership\Models\Territory;
use Liberu\Foundation\Identity\Socialstream\Contracts\ConnectedAccountOwner;
use Liberu\Foundation\Observability\Contracts\ObservabilityActor;
use Liberu\Foundation\Organizations\Contracts\OrganizationActor;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Foundation\RolesPermissions\Contracts\PrivilegedActor;
use Liberu\Foundation\RolesPermissions\Enums\Role;
use Liberu\Foundation\Search\Concerns\Searchable;
use Liberu\Foundation\UsersRoles\Models\User as Authenticatable;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property string|null $email
 * @property string|null $profile_photo_path
 * @property string|null $theme_preference
 * @property string|null $locale
 */
class User extends Authenticatable implements ConnectedAccountOwner, FilamentUser, HasDefaultTenant, HasTenants, ObservabilityActor, OrganizationActor, PrivilegedActor
{
    use HasApiTokens;
    use HasConnectedAccounts;
    use HasFactory;
    use HasProfilePhoto {
        HasProfilePhoto::profilePhotoUrl as getPhotoUrl;
    }
    use HasRoles {
        HasRoles::teams as permissionTeams;
    }
    use HasTeams {
        HasTeams::teams insteadof HasRoles;
    }
    use LogsActivity;
    use Notifiable;
    use Searchable;
    use SetsProfilePhotoFromUrl;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_calendar_token',
        'theme_preference',
        'locale',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'google_calendar_token',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    #[\Override]
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'google_calendar_token' => 'encrypted',
            'password' => 'hashed',
        ];
    }

    public function dashboardWidgets(): HasMany
    {
        return $this->hasMany(DashboardWidget::class);
    }

    public function inAppNotifications(): MorphMany
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')
            ->orderByDesc('created_at');
    }

    public function profilePhotoUrl(): Attribute
    {
        return filter_var($this->profile_photo_path, FILTER_VALIDATE_URL)
            ? Attribute::get(fn () => $this->profile_photo_path)
            : $this->getPhotoUrl();
    }

    public function getTenants(Panel $panel): array|EloquentCollection
    {
        return new EloquentCollection($this->allTeams()->all());
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if ($tenant instanceof Team && $tenant->isArchived()) {
            return false;
        }

        return $this->ownsTeam($tenant) || $this->teams->contains($tenant);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'super_admin' => $this->hasRole(Role::SuperAdmin),
            'admin' => $this->hasAdminAccess(),
            'portal' => $this->hasRole(Role::Customer),
            default => ! $this->hasRole(Role::Customer),
        };
    }

    public function canAccessFilament(): bool
    {
        return $this->hasVerifiedEmail()
            && $this->hasAnyRole(Role::values());
    }

    public function isAdmin(): bool
    {
        $adminEmails = config('app.admin_emails', []);

        return in_array(strtolower((string) $this->email), array_map('strtolower', $adminEmails), true)
            || $this->hasRoleInAnyTeam(Role::SuperAdmin->value);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRoleInAnyTeam(Role::SuperAdmin->value);
    }

    public function hasAdminAccess(): bool
    {
        return $this->isAdmin()
            || $this->hasRoleInAnyTeam(Role::Admin->value);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function getDefaultTenant(Panel $panel): ?Model
    {
        $team = $this->latestTeam;

        return $team instanceof Team && $team->isArchived() ? null : $team;
    }

    public function latestTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    public function hasRole($role, ?string $guard = null): bool
    {
        if (is_array($role)) {
            foreach ($role as $r) {
                if ($this->hasRole($r, $guard)) {
                    return true;
                }
            }

            return false;
        }

        $roleName = $role instanceof Role ? $role->value : strtolower((string) $role);

        if ($this->getRoleNames()->contains(fn (string $name): bool => strtolower($name) === $roleName)) {
            return true;
        }

        return $this->hasGlobalRole($roleName);
    }

    protected function hasGlobalRole(string $roleName): bool
    {
        if ($this->getKey() === null) {
            return false;
        }

        $tables = config('permission.table_names');
        $morphKey = config('permission.column_names.model_morph_key');
        $teamKey = config('permission.column_names.team_foreign_key');
        $roleKey = app(PermissionRegistrar::class)->pivotRole;

        return DB::table($tables['model_has_roles'])
            ->join($tables['roles'], $tables['roles'].'.id', '=', $tables['model_has_roles'].'.'.$roleKey)
            ->where($tables['model_has_roles'].'.model_type', $this->getMorphClass())
            ->where($tables['model_has_roles'].'.'.$morphKey, $this->getKey())
            ->whereNull($tables['model_has_roles'].'.'.$teamKey)
            ->whereRaw('LOWER('.$tables['roles'].'.name) = ?', [$roleName])
            ->exists();
    }

    public function territories(): BelongsToMany
    {
        return $this->belongsToMany(Territory::class, 'territory_user');
    }
}
