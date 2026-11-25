<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('files-media-livewire-overview')
        ->assertSeeHtml('aria-labelledby="files-media-livewire-heading"')
        ->assertSee('Files and Media')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
