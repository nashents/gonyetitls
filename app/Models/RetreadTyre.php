<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RetreadTyre extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function retread(){
        return $this->belongsTo('App\Models\Retread');
    }
    public function tyre(){
        return $this->belongsTo('App\Models\Tyre');
    }
}
