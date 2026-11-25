<?php

declare(strict_types=1);

namespace Liberu\CRM\ChannelGateway\Listeners;

use Liberu\CRM\ChannelGateway\Services\Zernio\ZernioTenantService;
use Laravel\Jetstream\Events\TeamCreated;

final class ProvisionZernioProfile
{
    public function __construct(private readonly ZernioTenantService $zernio) {}

    public function handle(TeamCreated $event): void
    {
        $this->zernio->provisionIfConfigured($event->team);
    }
}
