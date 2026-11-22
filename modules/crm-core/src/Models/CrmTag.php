<?php

namespace Liberu\CRM\Core\Models;


use Liberu\CRM\Core\Models\CrmContact as Contact;
use Liberu\Foundation\Organizations\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class CrmTag extends Model
{
    protected $table = 'tags';


    public function getForeignKey(): string
    {
        return 'tag_id';
    }
    use HasFactory;
    use IsTenantModel;

    protected $fillable = [
        'name',
        'team_id',
    ];

    public function contacts(): MorphToMany
    {
        return $this->morphedByMany(Contact::class, 'taggable');
    }
}
