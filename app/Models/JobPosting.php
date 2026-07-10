<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class JobPosting extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    
    public function job_title(){
        return $this->belongsTo('App\Models\JobTitle');
    }
    public function department(){
        return $this->belongsTo('App\Models\Department');
    }
    public function rank(){
        return $this->belongsTo('App\Models\Rank');
    }
    public function grade(){
        return $this->belongsTo('App\Models\Grade');
    }
    public function applications(){
        return $this->hasMany('App\Models\Application');
    }

}
