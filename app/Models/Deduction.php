<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Deduction extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function recoveries(){
        return $this->hasMany('App\Models\Recovery');
    }
    public function salary_items(){
        return $this->hasMany('App\Models\SalaryItem');
    }

    protected $fillable = [
        'name',
        'status',
    ];
   
}
