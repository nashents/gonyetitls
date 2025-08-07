<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Visitor extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function gate_passes(){
        return $this->hasMany('App\Models\GatePass');
    }
    public function group(){
        return $this->belongsTo('App\Models\Group');
    }

    protected $fillable = [
        'name',
        'user_id',
        'surname',
        'phonenumber',
        'group_id',
        'idnumber',
    ];
}
