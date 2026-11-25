<?php

declare(strict_types=1);

namespace Database\Factories;

use Liberu\CRM\Core\Models\SamlConnection;
use Liberu\Foundation\Organizations\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class SamlConnectionFactory extends Factory
{
    protected $model = SamlConnection::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'idp_entity_id' => 'https://idp.example.com/entity',
            'idp_sso_url' => 'https://idp.example.com/sso',
            'idp_x509_cert' => 'MIICertData',
            'enabled' => false,
        ];
    }
}
