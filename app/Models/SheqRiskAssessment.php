<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SheqRiskAssessment extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function department(){
        return $this->belongsTo('App\Models\Department');
    }
    public function risks(){
        return $this->hasMany('App\Models\SheqRisk');
    }
    public function actions(){
        return $this->morphMany('App\Models\SheqAction','actionable');
    }
}
