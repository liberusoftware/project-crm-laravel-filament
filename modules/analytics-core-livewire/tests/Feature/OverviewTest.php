<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('analytics-core-livewire-overview')
        ->assertSeeHtml('aria-labelledby="analytics-core-livewire-heading"')
        ->assertSee('Analytics Core')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
