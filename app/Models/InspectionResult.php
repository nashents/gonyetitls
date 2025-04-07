<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InspectionResult extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function inspection(){
        return $this->belongsTo('App\Models\Inspection');
    }
    public function inspection_type(){
        return $this->belongsTo('App\Models\InspectionType');
    }
}
