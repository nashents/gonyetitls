<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FreightServiceType extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function freight_jobs(){
        return $this->hasMany('App\Models\FreightJob');
    }

    protected $fillable = [
        'name',
        'user_id',
        'description',
        'is_locked',
    ];
}
