<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Application extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function job_posting(){
        return $this->belongsTo('App\Models\JobPosting');
    }
    
    public function recruitment_candidate(){
        return $this->hasOne('App\Models\RecruitmentCandidate');
    }

}
