<?php

declare(strict_types=1);

namespace Liberu\CRM\MarketingResources\Models;


use Liberu\CRM\Advertising\Models\CampaignRecipient;
use Liberu\Foundation\Organizations\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingCampaign extends Model
{
    use HasFactory;
    use IsTenantModel;

    protected $fillable = [
        'name',
        'type',
        'status',
        'subject',
        'content',
        'scheduled_at',
        'team_id',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function recipients()
    {
        return $this->hasMany(CampaignRecipient::class);
    }
}
