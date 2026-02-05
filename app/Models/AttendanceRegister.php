<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttendanceRegister extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function attendance(){
        return $this->belongsTo('App\Models\Attendance');
    }

    protected $fillable = [ 
        'employee_id',
        'status',
        'shift',
        'checkin',
        'checkout',
        'notes',
    ];
}
