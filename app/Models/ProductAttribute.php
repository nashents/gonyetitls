<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductAttribute extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function product(){
        return $this->belongsTo('App\Models\Product');
    }
    public function attribute(){
        return $this->belongsTo('App\Models\Attribute');
    }
    public function attribute_value(){
        return $this->belongsTo('App\Models\AttributeValue');
    }
}
