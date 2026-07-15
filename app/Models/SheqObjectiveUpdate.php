<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SheqObjectiveUpdate extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function objective(){
        return $this->belongsTo('App\Models\SheqObjective','sheq_objective_id');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
}
