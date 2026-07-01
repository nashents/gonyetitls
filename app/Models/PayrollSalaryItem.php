<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayrollSalaryItem extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'payroll_salary_id', 'salary_item_id', 'loan_id', 'deduction_id', 'recovery_id',
        'movement', 'allowance_id', 'currency_id', 'amount', 'exchange_rate', 'exchange_amount',
    ];

    public function payroll_salary(){
    return $this->belongsTo('App\Models\PayrollSalary');
    }
    public function salary_item(){
    return $this->belongsTo('App\Models\SalaryItem');
    }
    public function deduction(){
    return $this->belongsTo('App\Models\Deduction');
    }
    public function loan(){
    return $this->belongsTo('App\Models\Loan');
    }
    public function currency(){
    return $this->belongsTo('App\Models\Currency');
    }
    public function allowance(){
    return $this->belongsTo('App\Models\Allowance');
    }
}
