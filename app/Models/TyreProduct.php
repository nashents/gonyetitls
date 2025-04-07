<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TyreProduct extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function category(){
        return $this->belongsTo('App\Models\Category');
    }
    public function category_value(){
        return $this->belongsTo('App\Models\CategoryValue');
    }
    public function attribute_value(){
        return $this->belongsTo('App\Models\AttributeValue');
    }
    public function attribute(){
        return $this->belongsTo('App\Models\Attribute');
    }
    public function brand(){
        return $this->belongsTo('App\Models\Brand');
    }
}
