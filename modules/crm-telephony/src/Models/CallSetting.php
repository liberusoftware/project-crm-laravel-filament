<?php

declare(strict_types=1);

namespace Liberu\CRM\Telephony\Models;

use Liberu\Foundation\Organizations\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallSetting extends Model
{
    use HasFactory;
    use IsTenantModel;

    protected $fillable = [
        'name',
        'value',
        'description',
    ];
}
