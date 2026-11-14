<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('module-manager-livewire-overview')
        ->assertSeeHtml('aria-labelledby="module-manager-livewire-heading"')
        ->assertSee('Module Manager')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
