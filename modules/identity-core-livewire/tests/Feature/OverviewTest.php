<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('identity-core-livewire-overview')
        ->assertSeeHtml('aria-labelledby="identity-core-livewire-heading"')
        ->assertSee('Identity')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
