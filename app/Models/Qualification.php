<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Qualification extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function user(){
        return $this->belongsTo('App\Models\User');
    } 

    public function job_title_qualifications(){
        return $this->hasMany('App\Models\JobTitleQualification');
    }
    public function recruitment_qualifications(){
        return $this->hasMany('App\Models\RecruitmentQualification');
    }
}
