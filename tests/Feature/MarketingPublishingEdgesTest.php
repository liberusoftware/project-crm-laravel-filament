<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Liberu\CRM\MarketingResources\Models\SocialMediaPost;
use Liberu\CRM\MarketingResources\Services\PostPublisher;
use Liberu\CRM\MarketingResources\Services\PostTargets;
use Liberu\CRM\MarketingResources\Services\PostWorkflow;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    $this->user = User::factory()->withPersonalTeam()->create();
    $this->actingAs($this->user);
    $this->team = $this->user->currentTeam;
    $this->team->forceFill(['zernio_profile_id' => 'own-profile'])->save();
    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($this->team);
    config(['services.zernio.mode' => 'zernio', 'services.zernio.api_key' => 'test-key']);
    Http::preventStrayRequests();
});

it('offers owned supported destinations and keeps draft planning channels separate', function () {
    Http::fake(['*/accounts*' => Http::response(['data' => ['accounts' => [
        ['_id' => 'page', 'platform' => 'facebook', 'displayName' => 'Our Page'],
        ['_id' => 'person', 'platform' => 'linkedin', 'username' => 'Our Person'],
        ['_id' => 'unnamed', 'platform' => 'twitter'],
        ['_id' => 'unsupported', 'platform' => 'unknown'], ['platform' => 'facebook'], 'invalid',
    ]]])]);
    $options = app(PostTargets::class)->options($this->team);
    expect($options['Connected accounts'])->toBe([
        'facebook::page' => 'Facebook — Our Page', 'linkedin::person' => 'Linkedin — Our Person', 'twitter::unnamed' => 'Twitter — unnamed',
    ])->and($options['Channels for draft planning'])->toHaveKey('facebook');
    expect(app(PostTargets::class)->normalize($this->team, ['facebook::page', 'linkedin::person'], 'zernio'))
        ->toBe([['platform' => 'facebook', 'account_id' => 'page'], ['platform' => 'linkedin', 'account_id' => 'person']]);
});

it('retains draft planning when connected-account discovery fails', function () {
    Http::fake(['*/accounts*' => Http::response(['error' => 'Unavailable'], 503)]);
    $options = app(PostTargets::class)->options($this->team);
    expect($options['Connected accounts'])->toBe([])->and($options['Channels for draft planning'])->toHaveKey('twitter');
});

it('treats malformed provider account collections as empty', function () {
    Http::fake(['*/accounts*' => Http::response(['accounts' => 'invalid'])]);
    expect(app(PostTargets::class)->options($this->team)['Connected accounts'])->toBe([]);
});

it('requires explicit connected accounts before scheduling a planned Zernio channel', function () {
    $post = SocialMediaPost::factory()->create(['team_id' => $this->team->id, 'platforms' => ['twitter'], 'status' => 'draft', 'approved_at' => now()]);
    expect(fn () => app(PostWorkflow::class)->schedule($this->user, $this->team, $post, now()->addHour()->toIso8601String()))
        ->toThrow(ValidationException::class);
    expect($post->fresh()->status)->toBe('draft');
    Http::assertNothingSent();
});

it('records an operators resolution of uncertain delivery and refuses a stale second resolution', function () {
    $post = SocialMediaPost::factory()->create(['team_id' => $this->team->id, 'status' => 'publishing_unknown']);
    $workflow = app(PostWorkflow::class);
    $workflow->reconcile($this->user, $this->team, $post, ['resolution' => 'published', 'evidence' => 'Verified remote post identifier 123']);
    expect($post->fresh()->status)->toBe('published')->and($post->fresh()->last_error)->toContain('Verified remote post identifier 123');
    expect(fn () => $workflow->reconcile($this->user, $this->team, $post, ['resolution' => 'failed', 'evidence' => 'A stale reconciliation attempt']))->toThrow(HttpException::class);
    expect($post->fresh()->status)->toBe('published');
});

it('does not publish unapproved or future scheduled content', function () {
    $post = SocialMediaPost::factory()->create(['team_id' => $this->team->id, 'status' => 'scheduled', 'approved_at' => null, 'scheduled_at' => now()->subMinute()]);
    $publisher = app(PostPublisher::class);
    $publisher->publish($post);
    expect($post->fresh()->status)->toBe('scheduled')->and($post->fresh()->last_error)->toContain('reviewer');
    $post->update(['approved_at' => now(), 'scheduled_at' => now()->addHour()]);
    $publisher->publish($post->refresh());
    Http::assertNothingSent();
});

it('keeps a provider submission with no returned identifier in an uncertain state', function () {
    Http::fake(['*/accounts*' => Http::response(['accounts' => [['_id' => 'own-account', 'platform' => 'twitter']]]), '*/posts' => Http::response(['post' => ['status' => 'published']], 201)]);
    $post = SocialMediaPost::factory()->create(['team_id' => $this->team->id, 'platforms' => ['twitter'], 'status' => 'scheduled', 'approved_at' => now(), 'scheduled_at' => now()->subMinute()]);
    app(PostPublisher::class)->publish($post);
    expect($post->fresh()->status)->toBe('publishing_unknown')->and($post->fresh()->last_error)->toContain('identifier');
});

it('refreshes terminal provider status only for the owning workspace', function (string $status) {
    Http::fake(['*/posts/remote-post' => Http::response(['post' => ['profileId' => 'own-profile', 'status' => $status]])]);
    $post = SocialMediaPost::factory()->create(['team_id' => $this->team->id, 'status' => 'publishing_unknown', 'platform_post_ids' => ['zernio' => 'remote-post']]);
    app(PostPublisher::class)->refreshStatus($post);
    expect($post->fresh()->status)->toBe($status);
    if ($status === 'failed') {
        expect($post->fresh()->last_error)->toContain('provider reported');
    } else {
        expect($post->fresh()->last_error)->toBeNull();
    }
})->with(['published', 'failed', 'cancelled']);

it('rejects a status response for another workspace without changing local delivery state', function () {
    Http::fake(['*/posts/remote-post' => Http::response(['post' => ['profileId' => 'foreign-profile', 'status' => 'published']])]);
    $post = SocialMediaPost::factory()->create(['team_id' => $this->team->id, 'status' => 'publishing', 'platform_post_ids' => ['zernio' => 'remote-post']]);
    expect(fn () => app(PostPublisher::class)->refreshStatus($post))->toThrow(RuntimeException::class);
    expect($post->fresh()->status)->toBe('publishing');
});

it('edits approves schedules and cancels a workspace post through the API', function () {
    config(['services.zernio.mode' => 'direct']);
    $created = $this->postJson('/api/v1/marketing/posts', ['content' => 'Initial copy', 'platforms' => ['twitter'], 'scheduled_at' => now()->addDay()->toIso8601String()])->assertCreated();
    $url = '/api/v1/marketing/posts/'.$created->json('id');
    $this->getJson('/api/v1/marketing/posts')->assertOk()->assertJsonCount(1, 'data');
    $this->putJson($url, ['content' => 'Reviewed copy', 'platforms' => ['twitter']])->assertOk()->assertJsonPath('content', 'Reviewed copy');
    $this->postJson($url.'/approve')->assertOk()->assertJsonPath('approved_by', $this->user->id);
    $this->postJson($url.'/schedule', ['scheduled_at' => now()->addHour()->toIso8601String()])->assertOk()->assertJsonPath('status', 'scheduled');
    $this->postJson($url.'/cancel')->assertOk()->assertJsonPath('status', 'cancelled');
    $this->postJson($url.'/cancel')->assertConflict();
    Http::assertNothingSent();
});

it('refuses incompatible persisted targets instead of sending them through the direct provider', function () {
    $post = SocialMediaPost::factory()->create([
        'team_id' => $this->team->id, 'platforms' => [['platform' => 'twitter', 'account_id' => 'account']],
        'status' => 'scheduled', 'publishing_provider' => 'direct', 'approved_at' => now(), 'scheduled_at' => now()->subMinute(),
    ]);
    app(PostPublisher::class)->publish($post);
    expect($post->fresh()->status)->toBe('publishing_unknown')->and($post->fresh()->last_error)->toContain('requires platform names');
    Http::assertNothingSent();
});

it('does not poll provider status for a draft without a remote identifier', function () {
    $post = SocialMediaPost::factory()->create(['team_id' => $this->team->id, 'status' => 'draft', 'platform_post_ids' => null]);
    app(PostPublisher::class)->refreshStatus($post);
    expect($post->fresh()->status)->toBe('draft');
    Http::assertNothingSent();
});
