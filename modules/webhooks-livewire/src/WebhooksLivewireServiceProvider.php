<?php

declare(strict_types=1);

namespace Liberu\Foundation\WebhooksLivewire;

use Illuminate\Support\ServiceProvider;
use Liberu\Foundation\WebhooksLivewire\Livewire\Overview;
use Livewire\Livewire;

final class WebhooksLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'webhooks-livewire');
        Livewire::component('webhooks-livewire-overview', Overview::class);
    }
}
