<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('analytics-meta-livewire-overview')
        ->assertSeeHtml('aria-labelledby="analytics-meta-livewire-heading"')
        ->assertSee('Meta Server-Side Tracking')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
