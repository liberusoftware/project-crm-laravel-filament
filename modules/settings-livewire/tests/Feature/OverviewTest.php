<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('settings-livewire-overview')
        ->assertSeeHtml('aria-labelledby="settings-livewire-heading"')
        ->assertSee('Settings')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
