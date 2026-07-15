<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SheqObligation extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function department(){
        return $this->belongsTo('App\Models\Department');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function evaluations(){
        return $this->hasMany('App\Models\SheqObligationEvaluation')->orderBy('evaluation_date','desc');
    }
    public function actions(){
        return $this->morphMany('App\Models\SheqAction','actionable');
    }
    public function isExpired(){
        return $this->expiry_date && \Carbon\Carbon::parse($this->expiry_date)->isPast();
    }
    public function latestEvaluation(){
        return $this->evaluations->first();
    }
}
