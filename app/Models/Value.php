<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Value extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function attribute(){
        return $this->belongsTo('App\Models\Attribute');
    }
    public function products(){
        return $this->belongsToMany('App\Models\Product');
    }

    protected $fillable = [
        'attribute_id',
        'name',
        'user_id',
    ];
}
