<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class StatutoryDeductionType extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'company_id', 'country', 'name', 'code', 'calculation_type',
        'applies_to', 'is_tax_deductible', 'is_pre_tax',
        'gl_employee_debit_account', 'gl_employee_credit_account',
        'gl_employer_debit_account', 'gl_employer_credit_account',
        'sort_order', 'active', 'notes', 'created_by',
    ];

    protected $casts = [
        'is_tax_deductible' => 'boolean',
        'is_pre_tax'        => 'boolean',
        'active'            => 'boolean',
        'sort_order'        => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function rates()
    {
        return $this->hasMany(StatutoryDeductionRate::class);
    }

    public function taxBrackets()
    {
        return $this->hasMany(TaxBracketV2::class);
    }

    /**
     * Get the rate effective on a given date.
     */
    public function rateOn(string $date): ?StatutoryDeductionRate
    {
        return $this->rates()
            ->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $date);
            })
            ->orderByDesc('effective_from')
            ->first();
    }
}
