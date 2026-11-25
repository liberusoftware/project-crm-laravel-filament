<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('profiles-livewire-overview')
        ->assertSeeHtml('aria-labelledby="profiles-livewire-heading"')
        ->assertSee('Profiles')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
