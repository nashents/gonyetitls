<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SheqEquipment extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    protected $table = 'sheq_equipment';

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function equipment_class(){
        return $this->belongsTo('App\Models\SheqEquipmentClass','sheq_equipment_class_id');
    }
    public function department(){
        return $this->belongsTo('App\Models\Department');
    }
    public function inspections(){
        return $this->hasMany('App\Models\SheqEquipmentInspection')->orderBy('inspection_date','desc');
    }
    public function isInspectionOverdue(){
        return $this->next_inspection_date && \Carbon\Carbon::parse($this->next_inspection_date)->isPast();
    }
    public function isCertificateExpired(){
        return $this->certificate_expiry && \Carbon\Carbon::parse($this->certificate_expiry)->isPast();
    }
}
