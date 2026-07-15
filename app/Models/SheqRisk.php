<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SheqRisk extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function assessment(){
        return $this->belongsTo('App\Models\SheqRiskAssessment','sheq_risk_assessment_id');
    }
    public function department(){
        return $this->belongsTo('App\Models\Department');
    }
    public function controls(){
        return $this->hasMany('App\Models\SheqRiskControl');
    }
    public function actions(){
        return $this->morphMany('App\Models\SheqAction','actionable');
    }

    public static function band($rating){
        if (is_null($rating)) {
            return Null;
        }
        if ($rating >= 16) {
            return 'Critical';
        }
        if ($rating >= 10) {
            return 'High';
        }
        if ($rating >= 5) {
            return 'Medium';
        }
        return 'Low';
    }

    public function ratingBand(){
        return self::band($this->rating);
    }

    public function residualRatingBand(){
        return self::band($this->residual_rating);
    }
}
