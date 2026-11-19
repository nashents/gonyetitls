<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Deduction extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function recoveries(){
        return $this->hasMany('App\Models\Recovery');
    }
    public function payroll_salary_items(){
        return $this->hasMany('App\Models\PayrollSalaryItem');
    }
    public function salary_items(){
        return $this->hasMany('App\Models\SalaryItem');
    }
    /** CR side: employee + company share owed to the provider (medical aid, funeral cover ...). */
    public function payable_account(){
        return $this->belongsTo('App\Models\Account', 'payable_account_id');
    }
    /** DR side: the company's share of a shared premium (admin staff, or everyone when the split is off). */
    public function expense_account(){
        return $this->belongsTo('App\Models\Account', 'expense_account_id');
    }
    /** DR side for drivers when Payroll Config splits expenses by employee type (blank = expense_account). */
    public function expense_account_drivers(){
        return $this->belongsTo('App\Models\Account', 'expense_account_drivers_id');
    }

    protected $fillable = [
        'name',
        'type',
        'status',
        'is_locked',
    ];

    protected $casts = [
        'employer_share_taxable' => 'boolean',
    ];

    public const CONTRIBUTION_EMPLOYEE = 'employee';
    public const CONTRIBUTION_SHARED = 'shared';

    /** Percentage deductions are a % of basic or of gross (basic + earnings). */
    public const CALCULATE_ON = ['basic' => 'Basic', 'gross' => 'Gross'];

    public function isPercentage(): bool
    {
        return !$this->isShared() && $this->calculate_by === 'percentage';
    }

    /** "basic" or "gross" (anything else, e.g. old "net", counts as gross). */
    public function calculateOn(): string
    {
        return $this->calculate_on === 'basic' ? 'basic' : 'gross';
    }

    /**
     * The (employee) amount this deduction comes to on a salary with this
     * basic and gross. Shared premiums give the employee share. Null = no
     * default set.
     */
    public function amountFor(float $basic, float $gross): ?float
    {
        if ($this->isShared()) {
            return $this->defaultEmployeeShare();
        }
        if ($this->isPercentage()) {
            if (!is_numeric($this->percentage)) {
                return null;
            }
            $base = $this->calculateOn() === 'basic' ? $basic : $gross;
            return round($base * (float) $this->percentage / 100, 2);
        }

        return is_numeric($this->amount) ? (float) $this->amount : null;
    }

    /** Company pays part of a fixed premium (Medical Aid, Funeral Cover ...). */
    public function isShared(): bool
    {
        return $this->contribution_type === self::CONTRIBUTION_SHARED;
    }

    public function employerPercentage(): float
    {
        return $this->isShared() ? max(0.0, min(100.0, (float) $this->employer_percentage)) : 0.0;
    }

    public function employeePercentage(): float
    {
        return $this->isShared() ? 100.0 - $this->employerPercentage() : 100.0;
    }

    /** The employee's share of the default premium - what pre-fills a salary. */
    public function defaultEmployeeShare(): float
    {
        return round((float) $this->premium * $this->employeePercentage() / 100, 2);
    }

    /**
     * Company share for a salary line whose EMPLOYEE share is $employeeAmount.
     * Scales with the employee amount, so a salary on a different plan
     * (different premium) keeps the same split. If the employee pays 0% the
     * company pays the full default premium.
     */
    public function employerShareFor(float $employeeAmount): float
    {
        if (!$this->isShared() || $this->employerPercentage() <= 0) {
            return 0.0;
        }

        $employeePct = $this->employeePercentage();
        if ($employeePct <= 0) {
            return round((float) $this->premium, 2);
        }

        return round($employeeAmount * $this->employerPercentage() / $employeePct, 2);
    }

    /**
     * Deductions the payroll code looks up BY NAME (PAYE/AIDS Levy tax calc,
     * NSSA/NEC/Pension statutory lines + journal mapping, salary advance
     * recovery). Renaming, deleting or deactivating one breaks payroll, so
     * these are locked. Every other deduction is freely editable.
     */
    public const SYSTEM_NAMES = ['PAYE', 'AIDS Levy', 'NSSA', 'NEC', 'Pension', 'Salary Advance Recovery'];

    public function isSystem(): bool
    {
        return in_array($this->name, self::SYSTEM_NAMES, true);
    }
}
