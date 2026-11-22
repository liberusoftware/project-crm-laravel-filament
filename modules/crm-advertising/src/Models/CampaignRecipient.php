<?php

declare(strict_types=1);

namespace Liberu\CRM\Advertising\Models;


use Liberu\CRM\MarketingResources\Models\MarketingCampaign;
use Liberu\Foundation\Organizations\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignRecipient extends Model
{
    use HasFactory;
    use IsTenantModel;

    protected $fillable = [
        'marketing_campaign_id',
        'recipient_type',
        'recipient_id',
        'email',
        'phone',
        'status',
        'team_id',
    ];

    public function campaign()
    {
        return $this->belongsTo(MarketingCampaign::class, 'marketing_campaign_id');
    }

    public function recipient()
    {
        return $this->morphTo();
    }
}
