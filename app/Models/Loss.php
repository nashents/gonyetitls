<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Loss extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function loss_group(){
        return $this->belongsTo('App\Models\LossGroup');
    }
    public function loss_category(){
        return $this->belongsTo('App\Models\LossCategory');
    }
    public function immediate_causes(){
        return $this->hasMany('App\Models\ImmediateCause');
    }
    public function basic_causes(){
        return $this->hasMany('App\Models\BasicCause');
    }
   
}
