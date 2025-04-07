<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseProduct extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    public function purchase(){
        return $this->belongsTo('App\Models\Purchase');
    }
    public function product(){
        return $this->belongsTo('App\Models\Product');
    }

    public function purchase_product_documents(){
        return $this->hasMany('App\Models\PurchaseProductDocument');
    }
}
