<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('activity-comments-livewire-overview')
        ->assertSeeHtml('aria-labelledby="activity-comments-livewire-heading"')
        ->assertSee('Activity and Comments')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
