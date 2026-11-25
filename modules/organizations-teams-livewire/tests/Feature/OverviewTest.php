<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('organizations-teams-livewire-overview')
        ->assertSeeHtml('aria-labelledby="organizations-teams-livewire-heading"')
        ->assertSee('Organizations and Teams')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
