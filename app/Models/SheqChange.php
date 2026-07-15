<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SheqChange extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function department(){
        return $this->belongsTo('App\Models\Department');
    }
    public function requested_by(){
        return $this->belongsTo('App\Models\Employee','requested_by_id');
    }
    public function authorized_by(){
        return $this->belongsTo('App\Models\User','authorized_by_id');
    }
    public function risk_assessment(){
        return $this->belongsTo('App\Models\SheqRiskAssessment','sheq_risk_assessment_id');
    }
    public function actions(){
        return $this->morphMany('App\Models\SheqAction','actionable');
    }
}
