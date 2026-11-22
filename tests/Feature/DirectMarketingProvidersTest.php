<?php

use Liberu\Foundation\Integrations\Models\OAuthConfiguration;
use Liberu\CRM\MarketingResources\Models\SocialMediaPost;
use App\Models\User;
use Liberu\CRM\MarketingResources\Services\DirectPostPublisher;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

function standaloneMarketingConnection(User $user, string $platform, array $overrides = []): OAuthConfiguration
{
    $connection = new OAuthConfiguration();
    $connection->forceFill(array_replace([
        'team_id' => $user->currentTeam->id, 'user_id' => $user->id, 'service_name' => $platform,
        'is_active' => true, 'access_token' => 'owner-token',
        'additional_settings' => ['publishing_id' => $platform === 'linkedin' ? 'urn:li:person:owner' : '123'],
    ], $overrides))->save();

    return $connection;
}

beforeEach(function () {
    $this->user = User::factory()->withPersonalTeam()->create();
    $this->actingAs($this->user);
    $this->post = SocialMediaPost::factory()->create(['team_id' => $this->user->currentTeam->id, 'content' => 'Reviewed copy', 'image_path' => null, 'video_url' => null, 'link' => null]);
    config(['services.zernio.mode' => 'direct', 'services.facebook.graph_version' => 'v23.0', 'services.linkedin.api_version' => '202611']);
    Http::preventStrayRequests();
    Storage::fake('public');
});

it('requires a usable unambiguous standalone connection', function (string $problem) {
    $publisher = app(DirectPostPublisher::class);
    if ($problem !== 'missing') {
        standaloneMarketingConnection($this->user, 'facebook', match ($problem) {
            'token' => ['access_token' => null],
            'expired' => ['token_expires_at' => now()->subMinute()],
            'destination' => ['additional_settings' => []],
            default => [],
        });
    }
    if ($problem === 'duplicate') {
        standaloneMarketingConnection($this->user, 'facebook');
    }
    expect(fn () => $publisher->connection($this->user->currentTeam->id, 'facebook'))->toThrow(RuntimeException::class);
    Http::assertNothingSent();
})->with(['missing', 'token', 'expired', 'destination', 'duplicate']);

it('can retain an already owned Meta page destination without replacing the token', function () {
    $connection = standaloneMarketingConnection($this->user, 'facebook');
    Http::fake(['*/me?*' => Http::response(['id' => '123'])]);
    app(DirectPostPublisher::class)->configureDestination($connection, '123');
    expect($connection->fresh()->additional_settings['publishing_id'])->toBe('123')->and($connection->fresh()->access_token)->toBe('owner-token');
    Http::assertSentCount(1);
});

it('ignores malformed page rows and rejects destinations not owned by the connected account', function () {
    $connection = standaloneMarketingConnection($this->user, 'facebook');
    Http::fake(['*/me?*' => Http::response(['id' => 'user']), '*/me/accounts?*' => Http::response(['data' => ['invalid', ['id' => '456', 'access_token' => 'other-page']]])]);
    expect(fn () => app(DirectPostPublisher::class)->configureDestination($connection, '123'))->toThrow(RuntimeException::class, 'not managed');
    expect($connection->fresh()->access_token)->toBe('owner-token');
});

it('reports failed Meta account discovery without saving a new destination', function () {
    $connection = standaloneMarketingConnection($this->user, 'facebook');
    Http::fake(['*/me?*' => Http::response(['id' => 'user']), '*/me/accounts?*' => Http::response([], 403)]);
    expect(fn () => app(DirectPostPublisher::class)->configureDestination($connection, '123'))->toThrow(RuntimeException::class, 'Reconnect');
});

it('accepts LinkedIn author URNs and refuses malformed destinations', function () {
    $connection = standaloneMarketingConnection($this->user, 'linkedin');
    $publisher = app(DirectPostPublisher::class);
    expect(fn () => $publisher->configureDestination($connection, 'unverified-author'))->toThrow(RuntimeException::class);
    $publisher->configureDestination($connection, 'urn:li:organization:company');
    expect($connection->fresh()->additional_settings['publishing_id'])->toBe('urn:li:organization:company');
    Http::assertNothingSent();
});

it('rejects unsupported media or invalid API versions before publication', function (string $platform, string $problem, string $message) {
    standaloneMarketingConnection($this->user, $platform);
    if ($problem === 'image') {
        $this->post->update(['image_path' => 'posts/photo.jpg']);
    } elseif ($problem === 'video') {
        $this->post->update(['video_url' => 'https://media.example.test/video.mp4']);
    } elseif ($problem === 'version') {
        config(['services.facebook.graph_version' => '', 'services.linkedin.api_version' => '']);
    }
    expect(fn () => app(DirectPostPublisher::class)->publish($this->post->refresh(), $platform))->toThrow(RuntimeException::class, $message);
    Http::assertNothingSent();
})->with([
    ['twitter', 'image', 'media uploads'], ['twitter', 'video', 'video publishing'],
    ['linkedin', 'image', 'images must be uploaded'], ['linkedin', 'version', 'LinkedIn API version'],
    ['facebook', 'version', 'Meta Graph API version'], ['instagram', 'missing-image', 'requires an image'],
]);

it('does not publish an Instagram container that the provider rejected', function () {
    standaloneMarketingConnection($this->user, 'instagram');
    $this->post->update(['image_path' => 'posts/photo.jpg']);
    Http::fake(['*/123/media' => Http::response(['error' => 'Invalid image'], 400)]);
    expect(fn () => app(DirectPostPublisher::class)->publish($this->post->refresh(), 'instagram'))->toThrow(RuntimeException::class, 'image container');
    Http::assertSentCount(1);
});

it('publishes a Facebook image with its reviewed caption and link', function () {
    standaloneMarketingConnection($this->user, 'facebook');
    $this->post->update(['image_path' => 'posts/photo.jpg', 'link' => 'https://example.test/details']);
    Http::fake(['*/123/photos' => Http::response(['id' => 'photo-post'], 201)]);
    expect(app(DirectPostPublisher::class)->publish($this->post->refresh(), 'facebook'))->toBe('photo-post');
    Http::assertSent(fn ($request) => $request['caption'] === "Reviewed copy\nhttps://example.test/details" && str_contains($request['url'], 'posts/photo.jpg'));
});

it('refuses to report success without a publication identifier', function () {
    standaloneMarketingConnection($this->user, 'twitter');
    Http::fake(['api.x.com/*' => Http::response(['data' => []], 201)]);
    expect(fn () => app(DirectPostPublisher::class)->publish($this->post, 'twitter'))->toThrow(RuntimeException::class, 'publication identifier');
});
