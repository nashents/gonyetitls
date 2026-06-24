<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyIntegration extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = [
        'company_id',
        'integration_provider_id',
        'status',
        'credentials',
        'config',
        'last_tested_at',
        'last_success_at',
        'last_sync_at',
        'last_error',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'config' => 'array',
        'last_tested_at' => 'datetime',
        'last_success_at' => 'datetime',
        'last_sync_at' => 'datetime',
    ];

      public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function integration_provider()
    {
        return $this->belongsTo(IntegrationProvider::class);
    }

    public function integration_logs()
    {
        return $this->hasMany(IntegrationLog::class);
    }
}
