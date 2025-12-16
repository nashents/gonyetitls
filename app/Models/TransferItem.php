<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransferItem extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function transfer(){
        return $this->belongsTo('App\Models\Transfer');
    }
    public function product(){
        return $this->belongsTo('App\Models\Product');
    }
    public function inventory(){
        return $this->belongsTo('App\Models\Inventory');
    }
    public function tyre(){
        return $this->belongsTo('App\Models\Tyre');
    }

    protected $fillable = [
        'user_id',
      
    ];
}
