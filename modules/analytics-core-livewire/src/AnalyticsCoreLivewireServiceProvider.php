<?php

declare(strict_types=1);

namespace Liberu\Foundation\AnalyticsCoreLivewire;

use Illuminate\Support\ServiceProvider;
use Liberu\Foundation\AnalyticsCoreLivewire\Livewire\Overview;
use Livewire\Livewire;

final class AnalyticsCoreLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'analytics-core-livewire');
        Livewire::component('analytics-core-livewire-overview', Overview::class);
    }
}
