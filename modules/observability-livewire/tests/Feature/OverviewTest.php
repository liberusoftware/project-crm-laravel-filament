<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('observability-livewire-overview')
        ->assertSeeHtml('aria-labelledby="observability-livewire-heading"')
        ->assertSee('Observability')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
