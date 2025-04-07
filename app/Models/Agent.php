<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agent extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function trips(){
        return $this->hasMany('App\Models\Trip');
    }
    public function commissions(){
        return $this->hasMany('App\Models\Commission');
    }
    public function documents(){
        return $this->hasMany('App\Models\Document');
    }
    public function company(){
        return $this->belongsTo('App\Models\Company');
    }
    
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    protected $fillable = [
        'user_id',
        'name',
        'surname',
        'dob',
        'gender',
        'email',
        'phonenumber',
        'worknumber',
        'country',
        'city',
        'suburb',
        'status',
        'street_address',
    ];

}
