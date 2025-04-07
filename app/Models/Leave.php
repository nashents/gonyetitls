<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Leave extends Model implements Auditable
{
    use HasFactory,SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function department(){
        return $this->belongsTo('App\Models\Department');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function leave_type(){
        return $this->belongsTo('App\Models\LeaveType');
    }

    protected $fillable = [
        'user_id',
        'to',
        'from',
        'reason',
        'days',
        'leave_type_id',
        'available_leave_days',
    ];
}
