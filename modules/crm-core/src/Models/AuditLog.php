<?php

declare(strict_types=1);

namespace Liberu\CRM\Core\Models;


use App\Models\User;
use Liberu\Foundation\Organizations\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use IsTenantModel;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'auditable_type',
        'auditable_id',
        'team_id',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}
