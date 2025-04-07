<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tax extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function invoice_items(){
        return $this->hasMany('App\Models\InvoiceItem');
    }
    public function sale_items(){
        return $this->hasMany('App\Models\SaleItem');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
}
