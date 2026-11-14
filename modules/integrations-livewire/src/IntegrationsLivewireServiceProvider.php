<?php

declare(strict_types=1);

namespace Liberu\Foundation\IntegrationsLivewire;

use Illuminate\Support\ServiceProvider;
use Liberu\Foundation\IntegrationsLivewire\Livewire\Overview;
use Livewire\Livewire;

final class IntegrationsLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'integrations-livewire');
        Livewire::component('integrations-livewire-overview', Overview::class);
    }
}
