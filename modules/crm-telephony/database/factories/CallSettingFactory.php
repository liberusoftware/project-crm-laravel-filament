<?php

declare(strict_types=1);

namespace Database\Factories;

use Liberu\CRM\Telephony\Models\CallSetting;
use Liberu\Foundation\Organizations\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class CallSettingFactory extends Factory
{
    protected $model = CallSetting::class;

    public function definition()
    {
        return [
            'team_id' => Team::factory(),
            'name' => $this->faker->unique()->words(2, true),
            'value' => $this->faker->word(),
            'description' => $this->faker->sentence(),
        ];
    }
}
