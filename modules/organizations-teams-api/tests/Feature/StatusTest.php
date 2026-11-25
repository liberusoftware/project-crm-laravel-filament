<?php

use Illuminate\Support\Str;

it('registers its public versioned status route and preserves the request identifier', function () {
    $requestId = '188c7e79-bd4e-496a-9ee3-e25e88a41dfa';

    $this->getJson(route('organizations-teams-api.status', [], false), ['X-Request-ID' => $requestId])
        ->assertOk()
        ->assertExactJson([
            'data' => ['type' => 'organizations-teams', 'status' => 'available', 'version' => 'v1'],
            'meta' => ['request_id' => $requestId],
        ]);

    expect(app('router')->getRoutes()->getByName('organizations-teams-api.status')->uri())
        ->toBe('api/v1/organizations-teams/status');
});

it('generates a request identifier when the caller supplies none', function () {
    $this->getJson('/api/v1/organizations-teams/status')
        ->assertOk()
        ->assertJsonPath('meta.request_id', fn (string $value): bool => Str::isUuid($value));
});
