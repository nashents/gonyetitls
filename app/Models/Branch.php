<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model implements Auditable
{
    use HasFactory,SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function dispatches(){
        return $this->hasMany('App\Models\Dispatch');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function employee_positions(){
        return $this->hasMany('App\Models\EmployeePosition');
    }
    public function emails(){
        return $this->hasMany('App\Models\Email');
    }
    public function movements(){
        return $this->hasMany('App\Models\Movement');
    }
    public function employees(){
        return $this->hasMany('App\Models\Employee');
    }
    public function gate_passes(){
        return $this->hasMany('App\Models\GatePass');
    }
    public function asset_assignment(){
        return $this->hasMany('App\Models\AssetAssignment');
    }

    public function gates(){
        return $this->hasMany('App\Models\Gate');
    }
    public function departments(){
        return $this->belongsToMany('App\Models\Department');
    }

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'address',
        'phonenumber',
    ];

}
