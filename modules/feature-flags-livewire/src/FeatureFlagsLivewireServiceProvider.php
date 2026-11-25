<?php

declare(strict_types=1);

namespace Liberu\Foundation\FeatureFlagsLivewire;

use Illuminate\Support\ServiceProvider;
use Liberu\Foundation\FeatureFlagsLivewire\Livewire\Overview;
use Livewire\Livewire;

final class FeatureFlagsLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'feature-flags-livewire');
        Livewire::component('feature-flags-livewire-overview', Overview::class);
    }
}
