<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveType extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function leaves(){
        return $this->hasMany('App\Models\Leave');
    }
    protected $fillable = [
        'user_id',
        'name',
        'code',
        'entitlement',
        'is_paid',
        'is_accruable',
        'requires_medical_report',
        'requires_attachment',
        'carry_forward_allowed',
        'monthly_accrual_rate',
        'max_carry_forward_days',
        'max_consecutive_days',
        'requires_hod_approval',
        'requires_management_approval',
        'active',
        'description',
    ];
}
