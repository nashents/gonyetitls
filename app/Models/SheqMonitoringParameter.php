<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SheqMonitoringParameter extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function readings(){
        return $this->hasMany('App\Models\SheqMonitoringReading');
    }

    public function isBreach($value){
        if (is_null($this->limit_value) || $this->limit_value === '' || is_null($value)) {
            return false;
        }
        if ($this->limit_type == 'min') {
            return (float)$value < (float)$this->limit_value;
        }
        return (float)$value > (float)$this->limit_value;
    }
}
