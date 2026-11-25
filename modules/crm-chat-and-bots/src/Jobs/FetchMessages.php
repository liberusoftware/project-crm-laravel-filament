<?php

namespace Liberu\CRM\ChatAndBots\Jobs;

use Liberu\CRM\ServiceAgent\Actions\Helpdesk\CreateTicketFromEmail;
use Liberu\CRM\ServiceAgent\Actions\Helpdesk\CreateTicketFromWhatsApp;
use Liberu\Foundation\Integrations\Services\MessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchMessages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(
        MessageService $messageService,
        CreateTicketFromEmail $createTicketFromEmail,
        CreateTicketFromWhatsApp $createTicketFromWhatsApp
    ): void {
        $messages = $messageService->getUnreadMessages();

        foreach ($messages['email'] as $message) {
            $fullMessage = $messageService->getMessage($message->getId(), 'email');
            $createTicketFromEmail->execute($fullMessage);
        }

        foreach ($messages['whatsapp'] as $message) {
            $fullMessage = $messageService->getMessage($message['id'], 'whatsapp');
            $createTicketFromWhatsApp->execute($fullMessage);
        }
    }
}
