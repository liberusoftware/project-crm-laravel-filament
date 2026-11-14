<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('application-livewire-overview')
        ->assertSeeHtml('aria-labelledby="application-livewire-heading"')
        ->assertSee('Application Core')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
