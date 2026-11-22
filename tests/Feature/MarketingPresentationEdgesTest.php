<?php

use Liberu\CRM\MarketingResourcesFilament\Pages\ContentCalendar;
use Liberu\CRM\MarketingResources\Models\MarketingPlatformSettings;
use App\Models\SocialMediaPost;
use App\Models\User;
use Liberu\CRM\MarketingAgent\Services\ContentAssistant;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Facades\Filament;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->withPersonalTeam()->create();
    $this->actingAs($this->user);
    $this->team = $this->user->currentTeam;
    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($this->team);
    Http::preventStrayRequests();
});

it('navigates the calendar across year boundaries without changing scheduled content', function () {
    $post = SocialMediaPost::factory()->create(['team_id' => $this->team->id, 'content' => 'Unchanged draft']);
    Livewire::test(ContentCalendar::class)->set('month', '2026-12')
        ->call('moveMonth', 1)->assertSet('month', '2027-01')
        ->call('moveMonth', -1)->assertSet('month', '2026-12');
    expect($post->fresh()->content)->toBe('Unchanged draft');
});

it('returns normal panel navigation after workspace onboarding is complete', function () {
    $this->team->forceFill(['setup_completed_at' => now()])->save();
    $response = app(LoginResponse::class)->toResponse(Request::create('/app/login', 'POST'));
    expect($response->getTargetUrl())->not->toContain('setup-wizard')->toContain('/app');
});

it('preserves drafts when the content service fails or times out', function (string $failure) {
    MarketingPlatformSettings::query()->create(['id' => 1, 'provider' => 'direct', 'options' => ['ai_enabled' => true], 'credentials' => ['ai_endpoint' => 'https://content.example.test/generate', 'ai_api_key' => 'test-key']]);
    $post = SocialMediaPost::factory()->create(['team_id' => $this->team->id, 'content' => 'Original reviewed copy']);
    Http::fake(['content.example.test/*' => $failure === 'timeout'
        ? fn () => throw new ConnectionException('Provider timeout')
        : Http::response(['error' => 'Unavailable'], 503)]);
    expect(fn () => app(ContentAssistant::class)->generate($this->user, $this->team, ['purpose' => 'post', 'brief' => 'Describe our launch', 'platform' => 'linkedin']))
        ->toThrow(RuntimeException::class, $failure === 'timeout' ? 'timed out' : 'unavailable');
    expect($post->fresh()->content)->toBe('Original reviewed copy');
    $this->assertDatabaseCount('social_media_posts', 1);
})->with(['unavailable', 'timeout']);
