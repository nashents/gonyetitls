<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceType extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function services(){
        return $this->hasMany('App\Models\Service');
    }
    public function inspections(){
        return $this->hasMany('App\Models\Inspection');
    }
    public function inspection_groups(){
        return $this->hasMany('App\Models\InspectionGroup');
    }
    public function inspection_types(){
        return $this->hasMany('App\Models\InspectionType');
    }
    public function inspection_services(){
        return $this->hasMany('App\Models\InspectionService');
    }
    public function tickets(){
        return $this->hasMany('App\Models\Ticket');
    }

    protected $fillable = [
        'user_id',
        'name',
    ];
}
