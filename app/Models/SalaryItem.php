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
        'deduction_id',
        'recovery_id',
        'movement',
        'amount',
        'percentage',
    ];
   
}
