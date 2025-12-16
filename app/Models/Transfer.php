<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transfer extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function asset(){
        return $this->belongsTo('App\Models\Asset');
    }
    public function tyre(){
        return $this->belongsTo('App\Models\Tyre');
    }
    public function inventory(){
        return $this->belongsTo('App\Models\Inventory');
    }
    public function transfer_items(){
        return $this->hasMany('App\Models\TransferItem');
    }
    
}
