<?php

declare(strict_types=1);

namespace Liberu\Foundation\AnalyticsCoreLivewire\Widgets;

use Liberu\Analytics\Core\Models\DashboardWidget;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Stats extends Component
{
    public DashboardWidget $widget;

    public function render(): Factory|View
    {
        return view('livewire.widgets.stats');
    }
}
