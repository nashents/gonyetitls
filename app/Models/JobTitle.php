<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobTitle extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function employee_positions(){
        return $this->hasMany('App\Models\EmployeePosition');
    }
    public function department(){
        return $this->belongsTo('App\Models\Department');
    }
    public function rank(){
        return $this->belongsTo('App\Models\Rank');
    }
    public function job_title_qualifications(){
        return $this->hasMany('App\Models\JobTitleQualification');
    }
    public function job_postings(){
        return $this->hasMany('App\Models\JobPosting');
    }

    public function grades(){
        return $this->belongsToMany('App\Models\Grade');
    }

    protected $fillable =[
        'user_id',
        'department_id',
        'title',
    ];
}
