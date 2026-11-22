<?php

namespace Liberu\CRM\Core\Services;

use Liberu\CRM\LeadCapture\Models\Lead;

class LeadScoringService
{
    public function scoreLeads(Lead $lead): void
    {
        $lead->calculateScore();
    }

    public function recalculateAllScores(): void
    {
        Lead::chunk(100, function ($leads): void {
            foreach ($leads as $lead) {
                $this->scoreLeads($lead);
            }
        });
    }
}
