<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SheqEmergency extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function department(){
        return $this->belongsTo('App\Models\Department');
    }
    public function risk(){
        return $this->belongsTo('App\Models\SheqRisk','sheq_risk_id');
    }
    public function drills(){
        return $this->hasMany('App\Models\SheqDrill');
    }
}
