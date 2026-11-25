<?php

declare(strict_types=1);

namespace Liberu\Foundation\SearchLivewire;

use Illuminate\Support\ServiceProvider;
use Liberu\Foundation\SearchLivewire\Livewire\Overview;
use Livewire\Livewire;

final class SearchLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'search-livewire');
        Livewire::component('search-livewire-overview', Overview::class);
    }
}
