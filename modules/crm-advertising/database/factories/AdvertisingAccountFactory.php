<?php

declare(strict_types=1);

namespace Database\Factories;

use Liberu\CRM\Advertising\Models\AdvertisingAccount;
use Liberu\Foundation\Organizations\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdvertisingAccountFactory extends Factory
{
    protected $model = AdvertisingAccount::class;

    public function definition()
    {
        return [
            'team_id' => Team::factory(),
            'name' => $this->faker->company,
            'platform' => $this->faker->randomElement(['Google AdWords', 'LinkedIn Business', 'Facebook Advertising']),
            'account_id' => $this->faker->numerify('act_##########'),
            'status' => true,
            'access_token' => $this->faker->sha256,
            'refresh_token' => $this->faker->sha256,
        ];
    }
}
