<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Loan extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function account(){
        return $this->belongsTo('App\Models\Account');
    }
    public function vendor(){
        return $this->belongsTo('App\Models\Vendor');
    }
    public function loan_type(){
        return $this->belongsTo('App\Models\LoanType');
    }
    public function payroll_salary(){
        return $this->hasMany('App\Models\PayrollSalary');
    }
    public function payments(){
        return $this->hasMany('App\Models\Payment');
    }
    public function salary_items(){
        return $this->hasMany('App\Models\SalaryItem');
    }
   
}
