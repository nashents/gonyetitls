<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InspectionGroup extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function service_type(){
        return $this->belongsTo('App\Models\ServiceType');
    }
    public function inspection_services(){
        return $this->hasMany('App\Models\InspectionService');
    }
    
    public function inspection_types(){
        return $this->hasMany('App\Models\InspectionType');
    }
}
