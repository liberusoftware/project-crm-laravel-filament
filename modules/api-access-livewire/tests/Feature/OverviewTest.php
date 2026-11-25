<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('api-access-livewire-overview')
        ->assertSeeHtml('aria-labelledby="api-access-livewire-heading"')
        ->assertSee('API Access')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
