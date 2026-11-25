<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('audit-livewire-overview')
        ->assertSeeHtml('aria-labelledby="audit-livewire-heading"')
        ->assertSee('Audit')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
