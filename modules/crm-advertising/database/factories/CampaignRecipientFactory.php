<?php

declare(strict_types=1);

namespace Database\Factories;

use Liberu\CRM\Advertising\Models\CampaignRecipient;
use Liberu\CRM\Core\Models\CrmContact as Contact;
use Liberu\CRM\MarketingResources\Models\MarketingCampaign;
use Liberu\Foundation\Organizations\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampaignRecipientFactory extends Factory
{
    protected $model = CampaignRecipient::class;

    public function definition()
    {
        return [
            'marketing_campaign_id' => MarketingCampaign::factory(),
            'recipient_type' => Contact::class,
            'recipient_id' => Contact::factory(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'status' => $this->faker->randomElement(['pending', 'sent', 'failed']),
            'team_id' => Team::factory(),
        ];
    }
}
