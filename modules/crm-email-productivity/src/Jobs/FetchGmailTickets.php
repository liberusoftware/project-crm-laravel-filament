<?php

namespace Liberu\CRM\EmailProductivity\Jobs;

use Liberu\CRM\ServiceAgent\Actions\Helpdesk\CreateTicketFromEmail;
use Liberu\CRM\EmailProductivity\Services\GmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchGmailTickets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(GmailService $gmailService, CreateTicketFromEmail $createTicket): void
    {
        $messages = $gmailService->getUnreadMessages();

        foreach ($messages as $message) {
            $fullMessage = $gmailService->getMessage($message->getId());
            $createTicket->execute($fullMessage);
        }
    }
}
