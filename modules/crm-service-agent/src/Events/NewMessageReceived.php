<?php

declare(strict_types=1);

namespace Liberu\CRM\ServiceAgent\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(public array $message) {}
}
