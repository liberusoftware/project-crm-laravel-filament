<?php

declare(strict_types=1);

namespace Liberu\Foundation\SchedulerQueues\Jobs;

trait TenantAware
{
    use \Liberu\Foundation\Tenancy\Jobs\Concerns\TenantAware;
}
