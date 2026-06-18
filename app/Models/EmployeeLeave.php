<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class EmployeeLeave extends Model implements Auditable
{
    use HasFactory,SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }

    public function leave(){
        return $this->belongsTo('App\Models\Leave');
    }
    
    public function leave_type(){
        return $this->belongsTo('App\Models\LeaveType');
    }

    
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'acrual_rate',
        'available_leave_days',
        'maximum_leave_days',

        // Future-proof
        'opening_balance',
        'carried_forward_days',
        'days_accrued',
        'days_taken',
        'days_adjusted',
        'last_accrual_date',
    ];


}
