<?php

use Illuminate\Support\Str;

it('registers its public versioned status route and preserves the request identifier', function () {
    $requestId = '188c7e79-bd4e-496a-9ee3-e25e88a41dfa';

    $this->getJson(route('observability-api.status', [], false), ['X-Request-ID' => $requestId])
        ->assertOk()
        ->assertExactJson([
            'data' => ['type' => 'observability', 'status' => 'available', 'version' => 'v1'],
            'meta' => ['request_id' => $requestId],
        ]);

    expect(app('router')->getRoutes()->getByName('observability-api.status')->uri())
        ->toBe('api/v1/observability/status');
});

it('generates a request identifier when the caller supplies none', function () {
    $this->getJson('/api/v1/observability/status')
        ->assertOk()
        ->assertJsonPath('meta.request_id', fn (string $value): bool => Str::isUuid($value));
});
