<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('integrations-livewire-overview')
        ->assertSeeHtml('aria-labelledby="integrations-livewire-heading"')
        ->assertSee('Integrations')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
