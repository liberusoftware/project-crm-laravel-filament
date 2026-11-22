<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use Liberu\CRM\AdvertisingFilament\App\Resources\AdResource;
use Liberu\CRM\AdvertisingFilament\App\Resources\AdSetResource;
use Liberu\CRM\AdvertisingFilament\App\Resources\AdvertisingAccountResource;
use Liberu\CRM\AdvertisingFilament\App\Resources\CampaignResource;
use Liberu\CRM\MarketingResourcesFilament\App\Resources\LandingPageResource;
use Liberu\CRM\MarketingResourcesFilament\App\Resources\MailchimpCampaignResource;
use Liberu\CRM\MarketingResourcesFilament\App\Resources\MarketingCampaignResource;
use Liberu\CRM\MarketingResourcesFilament\App\Resources\SocialMediaPostResource;
use Liberu\Foundation\Organizations\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MarketingResourceMountTest extends TestCase
{
    use RefreshDatabase;

    private function actingManagerWithTeam(): Team
    {
        Role::findOrCreate('manager', 'web');
        $user = User::factory()->withPersonalTeam()->create(['email_verified_at' => now()]);
        $team = $user->ownedTeams->first();
        $user->current_team_id = $team->id;
        $user->save();
        $user->assignRole('manager');
        $this->actingAs($user);

        return $team;
    }

    private function assertResourceMounts(string $resourceClass): void
    {
        $team = $this->actingManagerWithTeam();
        $url = '/app/'.$team->id.'/'.$resourceClass::getSlug();
        $this->get($url)->assertStatus(200);
    }

    public function test_campaign_resource_index_mounts(): void
    {
        $this->assertResourceMounts(CampaignResource::class);
    }

    public function test_marketing_campaign_resource_index_mounts(): void
    {
        $this->assertResourceMounts(MarketingCampaignResource::class);
    }

    public function test_mailchimp_campaign_resource_index_mounts(): void
    {
        $this->assertResourceMounts(MailchimpCampaignResource::class);
    }

    public function test_ad_resource_index_mounts(): void
    {
        $this->assertResourceMounts(AdResource::class);
    }

    public function test_ad_set_resource_index_mounts(): void
    {
        $this->assertResourceMounts(AdSetResource::class);
    }

    public function test_advertising_account_resource_index_mounts(): void
    {
        $this->assertResourceMounts(AdvertisingAccountResource::class);
    }

    public function test_social_media_post_resource_index_mounts(): void
    {
        $this->assertResourceMounts(SocialMediaPostResource::class);
    }

    public function test_landing_page_resource_index_mounts(): void
    {
        $this->assertResourceMounts(LandingPageResource::class);
    }
}
