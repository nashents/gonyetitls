<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PayrollCompanyConfig extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'company_id', 'country', 'currency_id',
        'tax_authority_name', 'social_security_authority_name',
        'proration_method', 'work_week_days', 'exclude_public_holidays_from_proration',
        'allow_negative_net_pay', 'require_approval_before_lock',
        'require_approval_for_reversal', 'auto_deduct_loans', 'auto_deduct_salary_advances',
        'gl_wages_expense_account', 'gl_nssa_liability_account', 'gl_paye_liability_account',
        'gl_pension_liability_account', 'gl_nec_liability_account',
        'gl_nssa_employer_expense_account', 'gl_nec_employer_expense_account', 'gl_pension_employer_expense_account',
        'gl_nssa_employee_liability_account', 'gl_aids_levy_liability_account',
        'gl_payroll_suspense_account', 'gl_wages_payable_account',
        'effective_from', 'effective_to', 'active',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'work_week_days'                         => 'array',
        'exclude_public_holidays_from_proration' => 'boolean',
        'allow_negative_net_pay'                 => 'boolean',
        'require_approval_before_lock'           => 'boolean',
        'require_approval_for_reversal'          => 'boolean',
        'auto_deduct_loans'                      => 'boolean',
        'auto_deduct_salary_advances'            => 'boolean',
        'active'                                 => 'boolean',
        'effective_from'                         => 'date',
        'effective_to'                           => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
