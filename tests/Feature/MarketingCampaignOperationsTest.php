<?php

use App\Models\AdvertisingAccount;
use App\Models\Campaign;
use App\Models\User;
use Liberu\CRM\Advertising\Services\AdvertisingManager;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->user = User::factory()->withPersonalTeam()->create();
    $this->actingAs($this->user);
    $this->team = $this->user->currentTeam;
    $this->team->forceFill(['zernio_profile_id' => 'own-profile'])->save();
    $this->account = AdvertisingAccount::factory()->create([
        'team_id' => $this->team->id, 'platform' => 'facebook', 'account_id' => 'act_123',
        'access_token' => 'owned-ad-token', 'metadata' => ['zernio_account_id' => 'own-connection'],
    ]);
    $this->campaign = Campaign::factory()->create([
        'team_id' => $this->team->id, 'advertising_account_id' => $this->account->id,
        'external_id' => 'remote-campaign', 'status' => 'paused', 'budget' => 25, 'budget_type' => 'daily',
    ]);
    config(['services.zernio.mode' => 'direct', 'services.zernio.api_key' => 'test-key', 'services.facebook.graph_version' => 'v23.0']);
    Http::preventStrayRequests();
});

it('applies owned Meta campaign operations and preserves the correct local status', function (string $operation, string $status) {
    Http::fake([
        '*/remote-campaign?*' => Http::response(['account_id' => '123']),
        '*/remote-campaign/insights?*' => Http::response(['data' => [['spend' => '12.00']]]),
        '*/remote-campaign' => Http::response(['success' => true]),
    ]);
    app(AdvertisingManager::class)->execute($this->user, $this->team, $this->campaign, $operation);
    expect($this->campaign->fresh()->status)->toBe($status);
    Http::assertSentCount(2);
    if (in_array($operation, ['activate', 'pause', 'archive'], true)) {
        Http::assertSent(fn ($request) => $request->method() === 'POST' && $request['status'] === match ($operation) {
            'activate' => 'ACTIVE', 'pause' => 'PAUSED', 'archive' => 'ARCHIVED',
        });
    }
})->with([['update', 'paused'], ['activate', 'active'], ['pause', 'paused'], ['archive', 'archived'], ['analytics', 'paused']]);

it('releases a rejected creation claim so an operator can retry a definite client error', function () {
    $this->campaign->update(['external_id' => null]);
    Http::fake(['*/act_123/campaigns' => Http::sequence()->push(['error' => 'Invalid request'], 400)->push(['id' => 'new-campaign'], 201)]);
    $manager = app(AdvertisingManager::class);
    expect(fn () => $manager->execute($this->user, $this->team, $this->campaign, 'create'))->toThrow(RuntimeException::class, 'HTTP 400');
    expect(data_get($this->campaign->fresh()->metadata, 'creation_pending'))->toBeFalse();
    $manager->execute($this->user, $this->team, $this->campaign, 'create');
    expect($this->campaign->fresh()->external_id)->toBe('new-campaign');
});

it('keeps an uncertain creation claim when the provider returns no campaign identifier', function () {
    $this->campaign->update(['external_id' => null]);
    Http::fake(['*/act_123/campaigns' => Http::response([], 201)]);
    expect(fn () => app(AdvertisingManager::class)->execute($this->user, $this->team, $this->campaign, 'create'))
        ->toThrow(RuntimeException::class, 'campaign identifier');
    expect(data_get($this->campaign->fresh()->metadata, 'creation_pending'))->toBeTrue();
});

it('creates paused Zernio campaigns using the networks supported budget contract', function (string $platform, string $goal, string $expectedGoal) {
    config(['services.zernio.mode' => 'zernio']);
    $this->account->update(['platform' => $platform]);
    $this->campaign->update(['external_id' => null, 'objective' => $goal]);
    Http::fake([
        '*/accounts*' => Http::response(['accounts' => [['_id' => 'own-connection']]]),
        '*/ads/campaigns' => Http::response(['campaignId' => 'created-campaign'], 201),
    ]);
    app(AdvertisingManager::class)->execute($this->user, $this->team, $this->campaign, 'create');
    expect($this->campaign->fresh()->external_id)->toBe('created-campaign')->and($this->campaign->fresh()->status)->toBe('paused');
    Http::assertSent(fn ($request) => $request->method() === 'POST' && $request['goal'] === $expectedGoal && $request['status'] === 'PAUSED'
        && $request->hasHeader('Idempotency-Key', 'crm-campaign-'.$this->team->id.'-'.$this->campaign->id)
        && ($platform === 'LinkedIn Business' ? ! isset($request['budgetAmount']) : (float) $request['budgetAmount'] === 25.0));
})->with([['Facebook Advertising', 'awareness', 'awareness'], ['LinkedIn Business', 'conversion', 'conversions'], ['Google AdWords', 'consideration', 'traffic']]);

it('changes Zernio campaign status and archives only after remote ownership verification', function (string $operation, string $status) {
    config(['services.zernio.mode' => 'zernio']);
    Http::fake([
        '*/accounts*' => Http::response(['accounts' => [['_id' => 'own-connection']]]),
        '*/ads/campaigns?*' => Http::response(['campaigns' => [['platformCampaignId' => 'remote-campaign']]]),
        '*/ads/campaigns/*' => Http::response(['success' => true]),
    ]);
    app(AdvertisingManager::class)->execute($this->user, $this->team, $this->campaign, $operation);
    expect($this->campaign->fresh()->status)->toBe($status);
    Http::assertSent(fn ($request) => in_array($request->method(), ['PUT', 'DELETE'], true) && $request['accountId'] === 'own-connection');
})->with([['activate', 'active'], ['pause', 'paused'], ['archive', 'archived']]);
