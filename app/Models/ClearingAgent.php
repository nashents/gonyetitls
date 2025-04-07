<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClearingAgent extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function trips(){
        return $this->belongsToMany('App\Models\Trip');
    }
    public function contacts(){
        return $this->hasMany('App\Models\Contact');
    }
    public function documents(){
        return $this->hasMany('App\Models\Document');
    }
    public function borders(){
        return $this->belongsToMany('App\Models\Border');
    }

    protected $fillable = [
        'user_id',
        'name',
        'transporter_number',
        'contact_name',
        'contact_surname',
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
