<?php

declare(strict_types=1);

namespace Liberu\Foundation\AnalyticsCoreLivewire\Widgets;

use Liberu\Analytics\Core\Models\DashboardWidget;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Tasks extends Component
{
    public DashboardWidget $widget;

    public function render(): Factory|View
    {
        return view('livewire.widgets.tasks');
    }
}
