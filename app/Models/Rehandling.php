<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rehandling extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

   
    public function shift(){
        return $this->belongsTo('App\Models\Shift');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function work(){
        return $this->belongsTo('App\Models\Work');
    }
    public function location(){
        return $this->belongsTo('App\Models\Location');
    }
}
