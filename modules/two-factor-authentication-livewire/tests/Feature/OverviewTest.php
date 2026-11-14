<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('two-factor-authentication-livewire-overview')
        ->assertSeeHtml('aria-labelledby="two-factor-authentication-livewire-heading"')
        ->assertSee('Two-Factor Authentication')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
