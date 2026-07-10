<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class IntegrationLog extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];

    public function company_integration()
    {
        return $this->belongsTo(CompanyIntegration::class);
    }
}