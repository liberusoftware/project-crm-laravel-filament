<?php

declare(strict_types=1);

namespace Liberu\CRM\Core\Models;


use Liberu\CRM\Core\Models\CrmContact as Contact;
use Liberu\CRM\SalesWorkspace\Models\Opportunity;
use Liberu\CRM\Core\Events\NewComment;
use Liberu\Foundation\Organizations\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmNote extends Model
{
    protected $table = 'notes';


    public function getForeignKey(): string
    {
        return 'note_id';
    }
    use HasFactory;
    use IsTenantModel;

    protected $fillable = [
        'content',
        'contact_id',
        'company_id',
        'opportunity_id',
    ];

    /** Note is the app's comment model; fire NewComment on create. */
    protected $dispatchesEvents = [
        'created' => NewComment::class,
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class);
    }
}
