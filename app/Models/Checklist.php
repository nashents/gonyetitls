<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Checklist extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function trailer(){
        return $this->belongsTo('App\Models\Trailer');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function gate_pass(){
        return $this->belongsTo('App\Models\GatePass');
    }
    public function checklist_results(){
        return $this->hasMany('App\Models\ChecklistResult');
    }
    public function checklist_category(){
        return $this->belongsTo('App\Models\ChecklistCategory');
    }


}
