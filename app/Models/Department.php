<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function asset_assignment(){
        return $this->hasMany('App\Models\AssetAssignment');
    }
    public function emails(){
        return $this->hasMany('App\Models\Email');
    }
    public function branches(){
        return $this->belongsToMany('App\Models\Branch');
    }
    public function department_head(){
        return $this->hasOne('App\Models\DepartmentHead');
    }
    public function employees(){
        return $this->belongsToMany('App\Models\Employee');
    }
    public function job_titles(){
        return $this->hasMany('App\Models\JobTitle');
    }
    protected $fillable = [
        'user_id',
        'name',
        'department_code',
        'description',
    ];

}
