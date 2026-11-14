<?php

use App\Filament\App\Pages\MarketingConnections;
use App\Models\OAuthConfiguration;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->withPersonalTeam()->create();
    $this->actingAs($this->user);
    $this->team = $this->user->currentTeam;
    $this->team->forceFill(['zernio_profile_id' => 'own-profile'])->save();
    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($this->team);
    config(['services.zernio.mode' => 'direct', 'services.zernio.api_key' => 'test-key', 'services.facebook.graph_version' => 'v23.0']);
    Http::preventStrayRequests();
    $this->connection = new OAuthConfiguration();
    $this->connection->forceFill(['team_id' => $this->team->id, 'user_id' => $this->user->id, 'service_name' => 'facebook', 'account_name' => 'Our Page', 'is_active' => true, 'access_token' => 'own-token'])->save();
});

it('lists only workspace direct connections and allows an owner to disable publishing', function () {
    $other = User::factory()->withPersonalTeam()->create();
    $foreign = new OAuthConfiguration();
    $foreign->forceFill(['team_id' => $other->currentTeam->id, 'user_id' => $other->id, 'service_name' => 'twitter', 'account_name' => 'Private account', 'is_active' => true, 'access_token' => 'private-token'])->save();
    Livewire::test(MarketingConnections::class)->assertSee('Our Page')->assertDontSee('Private account')
        ->call('disableConnection', $this->connection->id)->assertNotified('Connection disabled for publishing');
    expect($this->connection->fresh()->is_active)->toBeFalse()->and($foreign->fresh()->is_active)->toBeTrue();
});

it('starts a direct OAuth connection scoped to the current owner and workspace', function () {
    $component = Livewire::test(MarketingConnections::class)->set('platform', 'twitter')->call('connect');
    $connection = OAuthConfiguration::query()->where('team_id', $this->team->id)->where('service_name', 'twitter')->firstOrFail();
    expect($connection->user_id)->toBe($this->user->id)->and($connection->is_active)->toBeFalse();
    $component->assertRedirect(route('oauth.authenticate', ['service' => 'twitter', 'configId' => $connection->id]));
});

it('verifies an owned publishing destination before saving it', function () {
    Http::fake(['*/me?*' => Http::response(['id' => 'user-123']), '*/me/accounts?*' => Http::response(['data' => [['id' => '123', 'access_token' => 'page-token', 'name' => 'Our Page']]])]);
    Livewire::test(MarketingConnections::class)->set('destinations.'.$this->connection->id, '123')
        ->call('saveDestination', $this->connection->id)->assertNotified('Publishing destination saved');
    expect($this->connection->fresh()->additional_settings['publishing_id'])->toBe('123');
});

it('keeps the existing destination when ownership verification fails', function () {
    Http::fake(['*/me?*' => Http::response(['id' => 'user-123']), '*/me/accounts?*' => Http::response(['data' => []])]);
    Livewire::test(MarketingConnections::class)->set('destinations.'.$this->connection->id, 'foreign-page')
        ->call('saveDestination', $this->connection->id)->assertNotified('Destination could not be verified');
    expect(data_get($this->connection->fresh()->additional_settings, 'publishing_id'))->toBeNull();
});

it('rejects an invalid destination before making provider requests', function () {
    Livewire::test(MarketingConnections::class)->set('destinations.'.$this->connection->id, 'https://foreign.test')
        ->call('saveDestination', $this->connection->id)->assertHasErrors('target');
    Http::assertNothingSent();
});

it('loads provider accounts and redirects only to a secure connection URL', function () {
    config(['services.zernio.mode' => 'zernio']);
    Http::fake([
        '*/accounts*' => Http::response(['accounts' => [['_id' => 'account', 'platform' => 'twitter', 'displayName' => 'Remote account', 'isActive' => false]]]),
        '*/connect/twitter*' => Http::response(['authUrl' => 'https://connect.example.test/start']),
    ]);
    Livewire::test(MarketingConnections::class)->assertSee('Remote account')->assertSee('Reconnect required')
        ->set('platform', 'twitter')->call('connect')->assertRedirect('https://connect.example.test/start');
});

it('reports provider discovery and connection failures without redirecting', function () {
    config(['services.zernio.mode' => 'zernio']);
    Http::fake(['*/accounts*' => Http::response(['error' => 'Unavailable'], 503), '*/connect/facebook*' => Http::response(['authUrl' => 'http://insecure.example.test'])]);
    Livewire::test(MarketingConnections::class)->assertNotified('Connections could not be loaded')
        ->call('connect')->assertNotified('Connection could not be started')->assertNoRedirect();
});
