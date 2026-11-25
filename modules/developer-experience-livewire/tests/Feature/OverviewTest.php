<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('developer-experience-livewire-overview')
        ->assertSeeHtml('aria-labelledby="developer-experience-livewire-heading"')
        ->assertSee('Developer Experience')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
