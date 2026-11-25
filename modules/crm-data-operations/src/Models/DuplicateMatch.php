<?php

declare(strict_types=1);

namespace Liberu\CRM\DataOperations\Models;

use Liberu\Foundation\Organizations\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Model;

final class DuplicateMatch extends Model
{
    use IsTenantModel;

    protected $table = 'crm_data_operation_duplicates';

    protected $fillable = ['team_id', 'operation_id', 'left_record_id', 'right_record_id', 'confidence', 'status'];

    protected function casts(): array
    {
        return ['confidence' => 'float'];
    }
}
