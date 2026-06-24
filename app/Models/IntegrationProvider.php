<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IntegrationProvider extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'required_credentials' => 'array',
        'default_config'       => 'array',
        'is_active'            => 'boolean',
    ];

    protected $fillable = [
        'key',
        'name',
        'type',
        'driver',
        'required_credentials',
        'default_config',
        'is_active',
    ];

    public function company_integrations()
    {
        return $this->hasMany(CompanyIntegration::class);
    }
}