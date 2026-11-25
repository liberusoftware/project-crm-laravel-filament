<?php

namespace Liberu\CRM\SalesWorkspace\Models;


use Liberu\CRM\Activities\Models\CrmActivity as Activity;
use Liberu\CRM\Core\Models\CrmContact as Contact;
use Liberu\CRM\Documents\Models\Document;
use Liberu\CRM\SalesPipelines\Models\Pipeline;
use Liberu\CRM\SalesPipelines\Models\Stage;
use App\Models\User;
use Liberu\CRM\Core\Contracts\OwnsRecords;
use Liberu\CRM\Core\Events\DealClosed;
use Liberu\Foundation\Organizations\Traits\IsTenantModel;
use Liberu\CRM\Core\Traits\MasksFields;
use Liberu\CRM\Core\Traits\RestrictsToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Deal extends Model implements OwnsRecords
{
    use HasFactory;
    use IsTenantModel;
    use MasksFields;
    use RestrictsToOwner;

    /** Sensitive fields masked in serialized output for masked-role viewers. */
    protected $maskedFields = ['value'];

    protected $fillable = [
        'team_id',
        'name',
        'value',
        'stage',
        'close_date',
        'probability',
        'contact_id',
        'user_id',
        'pipeline_id',
        'stage_id',
    ];

    protected $casts = [
        'close_date' => 'date',
        'value' => 'decimal:2',
        'probability' => 'integer',
    ];

    /** Fire DealClosed when the stage transitions into a closed-won/closed state. */
    protected static function booted(): void
    {
        static::updated(function (self $deal): void {
            if ($deal->wasChanged('stage') && in_array($deal->stage, ['won', 'closed'], true)) {
                DealClosed::dispatch($deal);
            }
        });
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'activitable');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function updateStage(Stage $newStage): void
    {
        $this->stage()->associate($newStage);
        $this->save();
    }
}
