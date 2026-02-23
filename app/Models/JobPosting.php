<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobPosting extends Model
{
    use HasFactory, SoftDeletes;
    
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

}
