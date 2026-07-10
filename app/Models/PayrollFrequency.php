<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class PayrollFrequency extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'company_id', 'name', 'code', 'periods_per_year', 'days_in_period', 'active',
    ];

    protected $casts = [
        'active'          => 'boolean',
        'periods_per_year'=> 'integer',
        'days_in_period'  => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function payrollRuns()
    {
        return $this->hasMany(PayrollRun::class);
    }
}
