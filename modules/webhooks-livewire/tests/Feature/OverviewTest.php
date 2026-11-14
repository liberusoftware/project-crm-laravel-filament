<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('webhooks-livewire-overview')
        ->assertSeeHtml('aria-labelledby="webhooks-livewire-heading"')
        ->assertSee('Webhooks')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
