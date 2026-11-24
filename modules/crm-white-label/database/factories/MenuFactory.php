<?php

declare(strict_types=1);

namespace Database\Factories;

use Liberu\CRM\WhiteLabel\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'url' => $this->faker->url,
            'order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
