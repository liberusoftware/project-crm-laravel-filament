<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('analytics-google-livewire-overview')
        ->assertSeeHtml('aria-labelledby="analytics-google-livewire-heading"')
        ->assertSee('Google Analytics')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
