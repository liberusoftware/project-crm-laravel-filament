<?php

declare(strict_types=1);

namespace Database\Factories;

use Liberu\CRM\SalesPipelines\Models\Pipeline;
use Liberu\CRM\SalesPipelines\Models\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

class StageFactory extends Factory
{
    protected $model = Stage::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'order' => $this->faker->numberBetween(1, 10),
            'pipeline_id' => Pipeline::factory(),
        ];
    }
}
