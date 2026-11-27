<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ChangeLog extends Model implements Auditable
{
    use \App\Models\Concerns\BelongsToCompanyOrShared; // multi-tenancy (docs/multi-tenancy-plan.md)

    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'key',
        'title',
        'description',
        'module',
        'type',
        'version',
        'released_at',
        'is_published',
        'created_by',
        'company_id',
    ];

    protected $casts = [
        'released_at' => 'datetime',
        'is_published' => 'boolean',
    ];

}
