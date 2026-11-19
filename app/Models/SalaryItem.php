<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalaryItem extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    /**
     * A shared-premium deduction line (Medical Aid ...) carries the company's
     * share in employer_amount; `amount` stays the employee share. Filled here
     * so Salaries Create/Edit and the import all get it the same way.
     */
    protected static function booted()
    {
        static::creating(function (SalaryItem $item) {
            if ($item->deduction_id && ($item->employer_amount === null || $item->employer_amount === '')) {
                $deduction = Deduction::find($item->deduction_id);
                if ($deduction && $deduction->isShared()) {
                    $item->employer_amount = $deduction->employerShareFor((float) $item->amount);
                }
            }
        });
    }

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function recovery(){
        return $this->belongsTo('App\Models\Recovery');
    }
    public function inventory(){
        return $this->belongsTo('App\Models\Inventory');
    }
    public function product(){
        return $this->belongsTo('App\Models\Product');
    }
    public function allowance(){
        return $this->belongsTo('App\Models\Allowance');
    }
    // Payroll earning (Accommodation, Transport ...). Payroll no longer uses allowances.
    public function earning(){
        return $this->belongsTo('App\Models\Earning');
    }

    /** The earning on this line (falls back to a legacy allowance line). Use $item->earning_line->name. */
    public function getEarningLineAttribute()
    {
        return $this->earning ?? $this->allowance;
    }
    public function deduction(){
        return $this->belongsTo('App\Models\Deduction');
    }
    public function loan(){
        return $this->belongsTo('App\Models\Loan');
    }
    public function salary(){
        return $this->belongsTo('App\Models\Salary');
    }
    public function payroll_salary_items(){
        return $this->hasMany('App\Models\PayrollSalaryItem');
    }
   

    protected $fillable = [
        'user_id',
        'salary_id',
        'currency_id',
        'loan_id',
        'allowance_id',
        'earning_id',
        'deduction_id',
        'recovery_id',
        'movement',
        'amount',
        'percentage',
        // Set by Salaries Create/Edit and SalaryPayeService on every item;
        // without these a foreign-currency item is valued at 0 in totals.
        'exchange_rate',
        'exchange_amount',
        'employer_amount',
    ];
   
}
