<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrainingRequirement extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function training_item(){
        return $this->belongsTo('App\Models\TrainingItem');
    }
    public function training_department(){
        return $this->belongsTo('App\Models\TrainingDepartment');
    }
}
