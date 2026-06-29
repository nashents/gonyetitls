<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatutoryDeductionRate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'statutory_deduction_type_id', 'currency_id',
        'effective_from', 'effective_to',
        'employee_percentage', 'employer_percentage',
        'employee_fixed_amount', 'employer_fixed_amount',
        'earnings_ceiling',
        'minimum_employee_contribution', 'maximum_employee_contribution',
        'minimum_employer_contribution', 'maximum_employer_contribution',
        'notes', 'created_by',
    ];

    protected $casts = [
        'effective_from'                  => 'date',
        'effective_to'                    => 'date',
        'employee_percentage'             => 'decimal:4',
        'employer_percentage'             => 'decimal:4',
        'employee_fixed_amount'           => 'decimal:4',
        'employer_fixed_amount'           => 'decimal:4',
        'earnings_ceiling'                => 'decimal:4',
        'minimum_employee_contribution'   => 'decimal:4',
        'maximum_employee_contribution'   => 'decimal:4',
        'minimum_employer_contribution'   => 'decimal:4',
        'maximum_employer_contribution'   => 'decimal:4',
    ];

    public function type()
    {
        return $this->belongsTo(StatutoryDeductionType::class, 'statutory_deduction_type_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
