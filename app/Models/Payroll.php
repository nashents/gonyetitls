<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payroll extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'currency_id', 'payroll_number', 'exchange_rate', 'month', 'year',
        'authorized_by_id', 'authorization', 'comments', 'authorization_date',
        // Bridge columns linking to the PayrollRun lifecycle wrapper
        'payroll_run_id', 'period_start', 'period_end', 'payroll_date', 'payroll_frequency_id',
        'payroll_status', 'total_gross_amount', 'total_net_amount', 'total_paye',
        'total_nssa_employee', 'total_nssa_employer', 'total_pension_employee', 'total_pension_employer',
        'total_nec_employee', 'total_nec_employer',
        'calculated_by', 'locked_by', 'posted_by', 'locked_at', 'posted_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'payroll_date' => 'date',
        'locked_at'    => 'datetime',
        'posted_at'    => 'datetime',
    ];

    public function payroll_salaries(){
        return $this->hasMany('App\Models\PayrollSalary');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function payrollRun(){
        return $this->belongsTo('App\Models\PayrollRun');
    }
}
