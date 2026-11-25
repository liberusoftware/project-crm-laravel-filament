<?php

namespace Liberu\CRM\EmailProductivity\Jobs;

use Liberu\CRM\ServiceAgent\Actions\Helpdesk\CreateTicketFromEmail;
use Liberu\Foundation\Integrations\Models\OAuthConfiguration;
use Liberu\CRM\EmailProductivity\Services\ImapService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchImapTickets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected $configId = null) {}

    public function handle(ImapService $imapService, CreateTicketFromEmail $createTicket): void
    {
        try {
            $configs = $this->configId
                ? [OAuthConfiguration::findOrFail($this->configId)]
                : OAuthConfiguration::where('service_name', 'imap')
                    ->where('is_active', true)
                    ->get();

            foreach ($configs as $config) {
                try {
                    $messages = $imapService->getUnreadMessages($config);

                    foreach ($messages as $message) {
                        $createTicket->execute($message, 'imap');
                    }
                } catch (\Exception $e) {
                    Log::error("Error fetching IMAP tickets for config {$config->id}: ".$e->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in FetchImapTickets job: '.$e->getMessage());
        }
    }
}
