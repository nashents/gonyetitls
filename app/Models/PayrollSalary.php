<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayrollSalary extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function salary(){
    return $this->belongsTo('App\Models\Salary');
    }
    public function payroll_salary_items(){
    return $this->hasMany('App\Models\PayrollSalaryItem');
    }
    public function payroll(){
    return $this->belongsTo('App\Models\Payroll');
    }
    public function employee(){
    return $this->belongsTo('App\Models\Employee');
    }
}
