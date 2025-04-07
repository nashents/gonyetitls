<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoadingPoint extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'user_id',
        'name',
        'contact_name',
        'contact_surname',
        'email',
        'phonenumber',
        'long',
        'lat',
        'status',
    ];
    public function rates(){
      return $this->hasMany('App\Models\Rate');
  }

    public function trips(){
      return $this->hasMany('App\Models\Trip');
    }
    public function user(){
      return $this->belongsTo('App\Models\User');
    }
    public function quotation_products(){
      return $this->hasMany('App\Models\QuotationProduct');
    }
}
