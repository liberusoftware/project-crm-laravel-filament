<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('search-livewire-overview')
        ->assertSeeHtml('aria-labelledby="search-livewire-heading"')
        ->assertSee('Search')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
