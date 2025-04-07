<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InspectionType extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function inspection_group(){
        return $this->belongsTo('App\Models\InspectionGroup');
    }
    public function service_type(){
        return $this->belongsTo('App\Models\ServiceType');
    }
    public function inspections(){
        return $this->hasMany('App\Models\Inspection');
    }
    public function inspection_services(){
        return $this->hasMany('App\Models\InspectionService');
    }
    public function inspection_results(){
        return $this->hasMany('App\Models\InspectionResult');
    }
}
