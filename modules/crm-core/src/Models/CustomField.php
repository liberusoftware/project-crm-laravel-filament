<?php

declare(strict_types=1);

namespace Liberu\CRM\Core\Models;


use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Foundation\Organizations\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    use HasFactory;
    use IsTenantModel;

    protected $fillable = [
        'name',
        'type',
        'model_type',
        'team_id',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
