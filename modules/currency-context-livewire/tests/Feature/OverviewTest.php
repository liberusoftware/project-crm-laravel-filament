<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('currency-context-livewire-overview')
        ->assertSeeHtml('aria-labelledby="currency-context-livewire-heading"')
        ->assertSee('Currency Context')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
