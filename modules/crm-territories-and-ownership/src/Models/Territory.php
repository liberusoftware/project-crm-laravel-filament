<?php

declare(strict_types=1);

namespace Liberu\CRM\TerritoriesAndOwnership\Models;


use App\Models\User;
use Liberu\Foundation\Organizations\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A team's sales territory (G3 ABAC foundation). Members assigned here will,
 * in a later slice, be scoped to the records in their territories.
 */
class Territory extends Model
{
    use HasFactory;
    use IsTenantModel;

    protected $fillable = [
        'team_id',
        'name',
    ];

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'territory_user');
    }
}
