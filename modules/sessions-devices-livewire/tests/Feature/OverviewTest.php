<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('sessions-devices-livewire-overview')
        ->assertSeeHtml('aria-labelledby="sessions-devices-livewire-heading"')
        ->assertSee('Sessions and Devices')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
