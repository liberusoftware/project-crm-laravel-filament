<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('import-export-livewire-overview')
        ->assertSeeHtml('aria-labelledby="import-export-livewire-heading"')
        ->assertSee('Import and Export')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
