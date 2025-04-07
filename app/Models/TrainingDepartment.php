<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrainingDepartment extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function training_plan(){
        return $this->hasOne('App\Models\TrainingPlan');
    }
    public function training_requirement(){
        return $this->hasOne('App\Models\TrainingRequirement');
    }
}
