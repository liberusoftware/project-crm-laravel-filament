<?php

declare(strict_types=1);

namespace Liberu\CRM\DataOperations\Models;

use Liberu\Foundation\Organizations\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Model;

final class OperationRule extends Model
{
    use IsTenantModel;

    protected $table = 'crm_data_operation_rules';

    protected $fillable = ['team_id', 'name', 'kind', 'definition', 'active'];

    protected function casts(): array
    {
        return ['definition' => 'array', 'active' => 'boolean'];
    }
}
