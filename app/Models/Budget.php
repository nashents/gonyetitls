<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function company(){
        return $this->belongsTo('App\Models\Company');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }

    protected $fillable = [
    'company_id',
    'user_id',
    'currency_id',
    'module',
    'name',
    'period',
    'value',
    'status',
];
}
