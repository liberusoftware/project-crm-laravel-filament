<?php

declare(strict_types=1);

namespace Liberu\CRM\EmailProductivity\Models;


use Liberu\CRM\ServiceAgent\Models\Ticket;
use Liberu\Foundation\Organizations\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    use IsTenantModel;

    protected $fillable = [
        'channel',
        'sender',
        'content',
        'timestamp',
        'priority',
        'status',
        'account_id',
        'thread_id',
        'ticket_id',
        'metadata',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'metadata' => 'array',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
