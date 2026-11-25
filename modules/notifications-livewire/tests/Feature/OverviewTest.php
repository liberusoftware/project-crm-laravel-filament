<?php

use Livewire\Livewire;

it('renders the public component alias with its accessible module overview', function () {
    Livewire::test('notifications-livewire-overview')
        ->assertSeeHtml('aria-labelledby="notifications-livewire-heading"')
        ->assertSee('Notifications')
        ->assertSee('Core actions remain the authoritative mutation boundary.');
});
