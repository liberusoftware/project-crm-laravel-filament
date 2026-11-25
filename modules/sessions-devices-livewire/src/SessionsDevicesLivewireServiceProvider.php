<?php

declare(strict_types=1);

namespace Liberu\Foundation\SessionsDevicesLivewire;

use Illuminate\Support\ServiceProvider;
use Liberu\Foundation\SessionsDevicesLivewire\Livewire\Overview;
use Livewire\Livewire;

final class SessionsDevicesLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'sessions-devices-livewire');
        Livewire::component('sessions-devices-livewire-overview', Overview::class);
    }
}
