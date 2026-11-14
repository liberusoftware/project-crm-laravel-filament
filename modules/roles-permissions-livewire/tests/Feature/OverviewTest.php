<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('roles-permissions-livewire-overview')
        ->assertSeeHtml('aria-labelledby="roles-permissions-livewire-heading"')
        ->assertSee('Roles and Permissions')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
