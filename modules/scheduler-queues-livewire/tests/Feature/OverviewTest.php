<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('scheduler-queues-livewire-overview')
        ->assertSeeHtml('aria-labelledby="scheduler-queues-livewire-heading"')
        ->assertSee('Scheduler and Queues')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
