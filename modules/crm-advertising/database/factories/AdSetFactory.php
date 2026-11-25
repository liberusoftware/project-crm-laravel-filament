<?php

declare(strict_types=1);

namespace Database\Factories;

use Liberu\CRM\Advertising\Models\AdSet;
use Liberu\CRM\Advertising\Models\AdvertisingAccount;
use Liberu\CRM\Advertising\Models\Campaign;
use Liberu\Foundation\Organizations\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdSet>
 */
class AdSetFactory extends Factory
{
    protected $model = AdSet::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'advertising_account_id' => AdvertisingAccount::factory(),
            'campaign_id' => Campaign::factory(),
            'name' => $this->faker->words(3, true),
            'external_id' => $this->faker->numerify('adset_########'),
            'status' => $this->faker->randomElement(['active', 'paused', 'archived', 'deleted']),
            'budget' => $this->faker->randomFloat(2, 100, 10000),
            'budget_type' => $this->faker->randomElement(['daily', 'lifetime']),
            'targeting' => [],
            'metadata' => [],
        ];
    }
}
