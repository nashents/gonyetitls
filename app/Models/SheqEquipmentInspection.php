<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SheqEquipmentInspection extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function equipment(){
        return $this->belongsTo('App\Models\SheqEquipment','sheq_equipment_id');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function inspector(){
        return $this->belongsTo('App\Models\Employee','inspector_id');
    }
}
