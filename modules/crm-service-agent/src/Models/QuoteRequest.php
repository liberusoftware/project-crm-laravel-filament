<?php

declare(strict_types=1);

namespace Liberu\CRM\ServiceAgent\Models;

use Liberu\Foundation\Organizations\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteRequest extends Model
{
    use HasFactory;
    use IsTenantModel;

    protected $fillable = [
        'name',
        'email',
        'message',
    ];
}
