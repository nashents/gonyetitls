<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleItem extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function invoice(){
        return $this->belongsTo('App\Models\Invoice');
    }
    public function inventory(){
        return $this->belongsTo('App\Models\Inventory');
    }
    public function tax(){
        return $this->belongsTo('App\Models\Tax');
    }
    public function account(){
        return $this->belongsTo('App\Models\Account');
    }
    public function product(){
        return $this->belongsTo('App\Models\Product');
    }
}
