<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IntegrationLog extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];

    public function company_integration()
    {
        return $this->belongsTo(CompanyIntegration::class);
    }
}