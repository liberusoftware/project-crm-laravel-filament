<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('feature-flags-livewire-overview')
        ->assertSeeHtml('aria-labelledby="feature-flags-livewire-heading"')
        ->assertSee('Feature Flags')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
