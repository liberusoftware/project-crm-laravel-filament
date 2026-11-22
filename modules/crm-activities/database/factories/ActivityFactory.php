<?php

declare(strict_types=1);

namespace Database\Factories;

use Liberu\CRM\Activities\Models\CrmActivity as Activity;
use Liberu\CRM\Core\Models\CrmContact as Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['created', 'updated', 'deleted', 'commented']),
            'date' => $this->faker->dateTimeThisYear(),
            'description' => $this->faker->sentence(),
            'outcome' => $this->faker->optional()->sentence(),
            'activitable_id' => 1,
            'activitable_type' => Contact::class,
        ];
    }
}
