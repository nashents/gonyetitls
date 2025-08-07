<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Group extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function visitors(){
        return $this->hasMany('App\Models\Visitor');
    }
    
    public function gate_passes(){
        return $this->hasMany('App\Models\GatePass');
    }

     protected $fillable = [
        'name',
        'user_id',
    ];
}
