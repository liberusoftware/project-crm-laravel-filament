<?php

declare(strict_types=1);

namespace Database\Factories;

use Liberu\CRM\Core\Models\CrmTag as Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
        ];
    }
}
